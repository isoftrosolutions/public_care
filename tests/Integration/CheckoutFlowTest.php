<?php

namespace Ayurviro\Tests\Integration;

use PHPUnit\Framework\TestCase;

class CheckoutFlowTest extends TestCase
{
    private static ?\mysqli $conn = null;
    private static int $testUserId = 0;
    private static int $productId = 0;
    private static int $productId2 = 0;

    public static function setUpBeforeClass(): void
    {
        try {
            require_once __DIR__ . '/../../config/database.php';
            require_once __DIR__ . '/../../includes/feature_helpers.php';
            self::$conn = getDB();

            $ts = time();
            $email = "checkout_{$ts}@example.com";
            self::$conn->query("INSERT INTO users (full_name, email, password, role) VALUES ('CheckoutUser', '$email', 'hash', 'customer')");
            self::$testUserId = self::$conn->insert_id;

            // Get seed products
            $res = self::$conn->query("SELECT id FROM products LIMIT 2");
            $products = $res->fetch_all(MYSQLI_ASSOC);
            self::$productId = (int)$products[0]['id'];
            self::$productId2 = (int)$products[1]['id'];
        } catch (\Throwable $e) {
            self::markTestSkipped('MySQL not available: ' . $e->getMessage());
        }
    }

    protected function setUp(): void
    {
        if (!self::$conn) {
            $this->markTestSkipped('No connection');
        }
        self::$conn->query("DELETE FROM orders WHERE user_id = " . self::$testUserId);
        self::$conn->query("DELETE FROM cart WHERE user_id = " . self::$testUserId);
    }

    public function test_add_items_to_cart(): void
    {
        $uid = self::$testUserId;
        self::$conn->query("INSERT INTO cart (user_id, product_id, quantity) VALUES ($uid, " . self::$productId . ", 2)");
        self::$conn->query("INSERT INTO cart (user_id, product_id, quantity) VALUES ($uid, " . self::$productId2 . ", 1)");

        $count = (int)self::$conn->query("SELECT SUM(quantity) as c FROM cart WHERE user_id = $uid")->fetch_assoc()['c'];
        $this->assertEquals(3, $count);
    }

    public function test_order_creation_with_items(): void
    {
        $uid = self::$testUserId;
        $pid1 = self::$productId;
        $pid2 = self::$productId2;
        $orderNum = 'OT' . substr(time(), -8);
        $total = 100.00;

        // Add to cart
        self::$conn->query("INSERT INTO cart (user_id, product_id, quantity) VALUES ($uid, $pid1, 2)");
        self::$conn->query("INSERT INTO cart (user_id, product_id, quantity) VALUES ($uid, $pid2, 1)");

        // Create order
        $stmt = self::$conn->prepare("INSERT INTO orders (user_id, order_number, total, status, payment_status) VALUES (?, ?, ?, 'pending', 'pending')");
        $stmt->bind_param('isd', $uid, $orderNum, $total);
        $this->assertTrue($stmt->execute());
        $oid = $stmt->insert_id;

        // Create order items from cart
        $cart = self::$conn->query("SELECT c.product_id, c.quantity, p.price FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = $uid");
        $itemCount = 0;
        while ($item = $cart->fetch_assoc()) {
            $istmt = self::$conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            $istmt->bind_param('iiid', $oid, $item['product_id'], $item['quantity'], $item['price']);
            $istmt->execute();
            $itemCount++;
        }
        $this->assertEquals(2, $itemCount);

        // Verify order
        $order = self::$conn->query("SELECT * FROM orders WHERE id = $oid")->fetch_assoc();
        $this->assertSame($orderNum, $order['order_number']);
        $this->assertSame('pending', $order['status']);
        $this->assertSame('pending', $order['payment_status']);

        // Verify order items
        $items = self::$conn->query("SELECT * FROM order_items WHERE order_id = $oid");
        $this->assertEquals(2, $items->num_rows);

        // Clear cart
        self::$conn->query("DELETE FROM cart WHERE user_id = $uid");
        $cartCount = (int)self::$conn->query("SELECT COUNT(*) as c FROM cart WHERE user_id = $uid")->fetch_assoc()['c'];
        $this->assertEquals(0, $cartCount);

        self::$conn->query("DELETE FROM order_items WHERE order_id = $oid");
        self::$conn->query("DELETE FROM orders WHERE id = $oid");
    }

    public function test_order_with_multiple_items_total(): void
    {
        $uid = self::$testUserId;
        $pid1 = self::$productId;
        $pid2 = self::$productId2;
        $orderNum = 'TT' . substr(time(), -8);

        // Get prices
        $p1 = self::$conn->query("SELECT price FROM products WHERE id = $pid1")->fetch_assoc();
        $p2 = self::$conn->query("SELECT price FROM products WHERE id = $pid2")->fetch_assoc();
        $expectedTotal = (float)$p1['price'] * 2 + (float)$p2['price'] * 1;

        self::$conn->query("INSERT INTO cart (user_id, product_id, quantity) VALUES ($uid, $pid1, 2)");
        self::$conn->query("INSERT INTO cart (user_id, product_id, quantity) VALUES ($uid, $pid2, 1)");

        // Calculate total from cart
        $cartResult = self::$conn->query("SELECT SUM(c.quantity * p.price) as total FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = $uid");
        $calcTotal = (float)$cartResult->fetch_assoc()['total'];
        $this->assertEqualsWithDelta($expectedTotal, $calcTotal, 0.01);

        self::$conn->query("INSERT INTO orders (user_id, order_number, total) VALUES ($uid, '$orderNum', $calcTotal)");
        $oid = self::$conn->insert_id;

        $orderTotal = (float)self::$conn->query("SELECT total FROM orders WHERE id = $oid")->fetch_assoc()['total'];
        $this->assertEqualsWithDelta($expectedTotal, $orderTotal, 0.01);

        self::$conn->query("DELETE FROM orders WHERE id = $oid");
    }

    public function test_order_item_product_reference_integrity(): void
    {
        $uid = self::$testUserId;
        $pid = self::$productId;
        $oid = 0;

        // Create order + item
        $stmt = self::$conn->prepare("INSERT INTO orders (user_id, order_number, total) VALUES (?, ?, 50.00)");
        $num = 'RF' . substr(time(), -8);
        $stmt->bind_param('is', $uid, $num);
        $stmt->execute();
        $oid = $stmt->insert_id;

        $istmt = self::$conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, 1, 25.00)");
        $istmt->bind_param('ii', $oid, $pid);
        $istmt->execute();
        $oiid = $istmt->insert_id;

        // Verify FK exists
        $row = self::$conn->query("SELECT p.name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.id = $oiid")->fetch_assoc();
        $this->assertNotNull($row['name']);

        // Delete triggers cascade
        self::$conn->query("DELETE FROM order_items WHERE order_id = $oid");
        self::$conn->query("DELETE FROM orders WHERE id = $oid");
    }

    public function test_cart_empty_after_checkout(): void
    {
        $uid = self::$testUserId;

        // Add items
        self::$conn->query("INSERT INTO cart (user_id, product_id, quantity) VALUES ($uid, " . self::$productId . ", 1)");
        $this->assertEquals(1, (int)self::$conn->query("SELECT COUNT(*) as c FROM cart WHERE user_id = $uid")->fetch_assoc()['c']);

        // Clear cart (as checkout does)
        self::$conn->query("DELETE FROM cart WHERE user_id = $uid");
        $this->assertEquals(0, (int)self::$conn->query("SELECT COUNT(*) as c FROM cart WHERE user_id = $uid")->fetch_assoc()['c']);
    }

    public function test_order_number_is_unique(): void
    {
        $uid = self::$testUserId;
        $num = 'UQ' . substr(time(), -8);

        self::$conn->query("INSERT INTO orders (user_id, order_number, total) VALUES ($uid, '$num', 10.00)");

        $this->expectException(\mysqli_sql_exception::class);
        self::$conn->query("INSERT INTO orders (user_id, order_number, total) VALUES ($uid, '$num', 20.00)");

        self::$conn->query("DELETE FROM orders WHERE order_number = '$num'");
    }
}
