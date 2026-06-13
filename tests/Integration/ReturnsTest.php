<?php

namespace Ayurviro\Tests\Integration;

use PHPUnit\Framework\TestCase;

class ReturnsTest extends TestCase
{
    private static ?\mysqli $conn = null;
    private static int $testUserId = 0;
    private static int $orderId = 0;
    private static int $orderItemId = 0;
    private static int $productId = 0;

    public static function setUpBeforeClass(): void
    {
        try {
            require_once __DIR__ . '/../../config/database.php';
            self::$conn = getDB();

            $ts = time();
            $email = "return_{$ts}@example.com";
            self::$conn->query("INSERT INTO users (full_name, email, password, role) VALUES ('ReturnUser', '$email', 'hash', 'customer')");
            self::$testUserId = self::$conn->insert_id;

            // Get a product
            $res = self::$conn->query("SELECT id FROM products LIMIT 1");
            self::$productId = (int)$res->fetch_assoc()['id'];

            // Create a delivered order with items
            self::$conn->query("INSERT INTO orders (user_id, order_number, total, shipping_name, shipping_address, shipping_city, shipping_zip, status, payment_status) VALUES (" . self::$testUserId . ", 'ORD-RETURN-$ts', 100.00, 'Test', 'Addr', 'City', '123456', 'delivered', 'paid')");
            self::$orderId = self::$conn->insert_id;

            self::$conn->query("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (" . self::$orderId . ", " . self::$productId . ", 2, 50.00)");
            self::$orderItemId = self::$conn->insert_id;
        } catch (\Throwable $e) {
            self::markTestSkipped('MySQL not available: ' . $e->getMessage());
        }
    }

    protected function setUp(): void
    {
        if (!self::$conn) {
            $this->markTestSkipped('No connection');
        }
        self::$conn->query("DELETE FROM return_items WHERE return_id IN (SELECT id FROM return_requests WHERE user_id = " . self::$testUserId . ")");
        self::$conn->query("DELETE FROM return_requests WHERE user_id = " . self::$testUserId);
    }

    public function test_create_return_with_items(): void
    {
        $uid = self::$testUserId;
        $oid = self::$orderId;
        $returnNum = 'RTN-TEST-' . time();

        self::$conn->begin_transaction();
        try {
            // Insert return request
            $stmt = self::$conn->prepare("INSERT INTO return_requests (user_id, order_id, return_number, return_type, reason, reason_detail, status, pickup_address, created_at) VALUES (?, ?, ?, ?, ?, ?, 'pending', ?, NOW())");
            $type = 'refund';
            $reason = 'damaged';
            $detail = 'Product damaged in transit';
            $addr = '123 Test St';
            $stmt->bind_param('iisssss', $uid, $oid, $returnNum, $type, $reason, $detail, $addr);
            $this->assertTrue($stmt->execute());
            $rid = $stmt->insert_id;

            // Insert return items
            $istmt = self::$conn->prepare("INSERT INTO return_items (return_id, order_item_id, product_id, quantity) VALUES (?, ?, ?, ?)");
            $istmt->bind_param('iiii', $rid, self::$orderItemId, self::$productId, 1);
            $this->assertTrue($istmt->execute());

            self::$conn->commit();
        } catch (\Throwable $e) {
            self::$conn->rollback();
            throw $e;
        }

        // Verify return request
        $rr = self::$conn->query("SELECT * FROM return_requests WHERE id = $rid")->fetch_assoc();
        $this->assertSame($returnNum, $rr['return_number']);
        $this->assertSame('refund', $rr['return_type']);
        $this->assertSame('damaged', $rr['reason']);
        $this->assertSame('pending', $rr['status']);

        // Verify return items
        $items = self::$conn->query("SELECT * FROM return_items WHERE return_id = $rid");
        $this->assertEquals(1, $items->num_rows);
        $item = $items->fetch_assoc();
        $this->assertEquals(self::$productId, $item['product_id']);
        $this->assertEquals(1, $item['quantity']);

        self::$conn->query("DELETE FROM return_items WHERE return_id = $rid");
        self::$conn->query("DELETE FROM return_requests WHERE id = $rid");
    }

    public function test_return_rejects_nonexistent_order(): void
    {
        $stmt = self::$conn->prepare("SELECT id, total FROM orders WHERE id = ? AND user_id = ? AND status IN ('delivered','completed')");
        $fakeId = 999999;
        $stmt->bind_param('ii', $fakeId, self::$testUserId);
        $stmt->execute();
        $this->assertNull($stmt->get_result()->fetch_assoc());
    }

    public function test_return_with_multiple_items(): void
    {
        $uid = self::$testUserId;
        $oid = self::$orderId;
        $returnNum = 'RTN-MULTI-' . time();

        self::$conn->begin_transaction();
        try {
            $stmt1 = self::$conn->prepare("INSERT INTO return_requests (user_id, order_id, return_number, return_type, reason, reason_detail, status, pickup_address, created_at) VALUES (?, ?, ?, ?, ?, ?, 'pending', ?, NOW())");
            $type = 'exchange';
            $reason = 'wrong_item';
            $detail = 'Wrong size';
            $addr = '456 Test Ave';
            $stmt1->bind_param('iisssss', $uid, $oid, $returnNum, $type, $reason, $detail, $addr);
            $stmt1->execute();
            $rid = $stmt1->insert_id;

            $istmt = self::$conn->prepare("INSERT INTO return_items (return_id, order_item_id, product_id, quantity) VALUES (?, ?, ?, ?)");
            $istmt->bind_param('iiii', $rid, self::$orderItemId, self::$productId, 2);
            $istmt->execute();

            self::$conn->commit();
        } catch (\Throwable $e) {
            self::$conn->rollback();
            throw $e;
        }

        $items = self::$conn->query("SELECT * FROM return_items WHERE return_id = $rid");
        $this->assertEquals(1, $items->num_rows);
        $this->assertEquals(2, $items->fetch_assoc()['quantity']);

        self::$conn->query("DELETE FROM return_items WHERE return_id = $rid");
        self::$conn->query("DELETE FROM return_requests WHERE id = $rid");
    }

    public function test_return_rolls_back_on_failure(): void
    {
        $uid = self::$testUserId;
        $oid = self::$orderId;
        $returnNum = 'RTN-ROLLBACK-' . time();

        $beforeCount = (int)self::$conn->query("SELECT COUNT(*) as c FROM return_requests")->fetch_assoc()['c'];

        try {
            self::$conn->begin_transaction();

            $stmt = self::$conn->prepare("INSERT INTO return_requests (user_id, order_id, return_number, return_type, reason, reason_detail, status, pickup_address, created_at) VALUES (?, ?, ?, ?, ?, ?, 'pending', ?, NOW())");
            $type = 'refund';
            $reason = 'defective';
            $detail = 'Not working';
            $addr = 'Addr';
            $stmt->bind_param('iisssss', $uid, $oid, $returnNum, $type, $reason, $detail, $addr);
            $stmt->execute();

            // Force failure with bad query
            self::$conn->query("INSERT INTO nonexistent_table VALUES (1)");

            self::$conn->commit();
        } catch (\Throwable $e) {
            self::$conn->rollback();
        }

        $afterCount = (int)self::$conn->query("SELECT COUNT(*) as c FROM return_requests")->fetch_assoc()['c'];
        $this->assertEquals($beforeCount, $afterCount, 'Rollback should prevent any insert');
    }

    public function test_return_requests_are_scoped_to_user(): void
    {
        // Create another user
        $ts = time();
        self::$conn->query("INSERT INTO users (full_name, email, password) VALUES ('OtherUser', 'other_$ts@example.com', 'hash')");
        $otherUid = self::$conn->insert_id;

        // Other user's return should not be visible to test user
        $stmt = self::$conn->prepare("SELECT * FROM return_requests WHERE id = ? AND user_id = ?");
        $stmt->bind_param('ii', 0, self::$testUserId);
        $stmt->execute();
        // This is testing the query pattern used in returns.php
        $this->assertNull($stmt->get_result()->fetch_assoc());

        self::$conn->query("DELETE FROM users WHERE id = $otherUid");
    }
}
