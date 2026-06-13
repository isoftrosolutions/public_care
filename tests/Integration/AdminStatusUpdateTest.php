<?php

namespace Ayurviro\Tests\Integration;

use PHPUnit\Framework\TestCase;

class AdminStatusUpdateTest extends TestCase
{
    private static ?\mysqli $conn = null;
    private static int $testUserId = 0;
    private static int $doctorId = 0;

    public static function setUpBeforeClass(): void
    {
        try {
            require_once __DIR__ . '/../../config/database.php';
            self::$conn = getDB();

            $ts = time();
            $email = "status_test_{$ts}@example.com";
            self::$conn->query("INSERT INTO users (full_name, email, password, role) VALUES ('StatusUser', '$email', 'hash', 'customer')");
            self::$testUserId = self::$conn->insert_id;

            self::$conn->query("INSERT INTO doctors (name, slug, fee) VALUES ('StatusDoc', 'status-doc-$ts', 500.00)");
            self::$doctorId = self::$conn->insert_id;
        } catch (\Throwable $e) {
            self::markTestSkipped('MySQL not available: ' . $e->getMessage());
        }
    }

    protected function setUp(): void
    {
        if (!self::$conn) {
            $this->markTestSkipped('No connection');
        }
    }

    // ========== APPOINTMENTS STATUS ==========

    public function test_appointment_create_read_update_status(): void
    {
        $uid = self::$testUserId;
        $did = self::$doctorId;

        // Create
        $stmt = self::$conn->prepare("INSERT INTO appointments (user_id, doctor_id, appointment_date, appointment_time, amount, status) VALUES (?, ?, CURDATE(), CURTIME(), ?, 'pending')");
        $fee = 500.00;
        $stmt->bind_param('iid', $uid, $did, $fee);
        $this->assertTrue($stmt->execute());
        $aid = $stmt->insert_id;

        // Verify pending status
        $row = self::$conn->query("SELECT status FROM appointments WHERE id = $aid")->fetch_assoc();
        $this->assertSame('pending', $row['status']);

        // Update to confirmed
        $stmt = self::$conn->prepare("UPDATE appointments SET status = ? WHERE id = ?");
        $status = 'confirmed';
        $stmt->bind_param('si', $status, $aid);
        $this->assertTrue($stmt->execute());

        $row = self::$conn->query("SELECT status FROM appointments WHERE id = $aid")->fetch_assoc();
        $this->assertSame('confirmed', $row['status']);

        // Update to completed
        $stmt = self::$conn->prepare("UPDATE appointments SET status = ? WHERE id = ?");
        $status = 'completed';
        $stmt->bind_param('si', $status, $aid);
        $this->assertTrue($stmt->execute());

        $row = self::$conn->query("SELECT status FROM appointments WHERE id = $aid")->fetch_assoc();
        $this->assertSame('completed', $row['status']);

        // Update to cancelled
        $stmt = self::$conn->prepare("UPDATE appointments SET status = ? WHERE id = ?");
        $status = 'cancelled';
        $stmt->bind_param('si', $status, $aid);
        $this->assertTrue($stmt->execute());

        $row = self::$conn->query("SELECT status FROM appointments WHERE id = $aid")->fetch_assoc();
        $this->assertSame('cancelled', $row['status']);

        self::$conn->query("DELETE FROM appointments WHERE id = $aid");
    }

    public function test_appointment_payment_status_update(): void
    {
        $uid = self::$testUserId;
        $did = self::$doctorId;

        self::$conn->query("INSERT INTO appointments (user_id, doctor_id, appointment_date, appointment_time, status, payment_status, amount) VALUES ($uid, $did, CURDATE(), CURTIME(), 'pending', 'pending', 500.00)");
        $aid = self::$conn->insert_id;

        // Mark as paid
        $stmt = self::$conn->prepare("UPDATE appointments SET payment_status = ? WHERE id = ?");
        $ps = 'paid';
        $stmt->bind_param('si', $ps, $aid);
        $this->assertTrue($stmt->execute());

        $row = self::$conn->query("SELECT payment_status FROM appointments WHERE id = $aid")->fetch_assoc();
        $this->assertSame('paid', $row['payment_status']);

        self::$conn->query("DELETE FROM appointments WHERE id = $aid");
    }

    public function test_appointment_rejects_invalid_status(): void
    {
        $this->expectException(\mysqli_sql_exception::class);
        self::$conn->query("INSERT INTO appointments (user_id, doctor_id, appointment_date, appointment_time, status) VALUES (1, 1, CURDATE(), CURTIME(), 'invalid_status')");
    }

    // ========== ORDERS STATUS ==========

    public function test_order_create_with_status(): void
    {
        $uid = self::$testUserId;
        $num = 'ORD-TEST-' . time();

        $stmt = self::$conn->prepare("INSERT INTO orders (user_id, order_number, total, status, payment_status) VALUES (?, ?, ?, 'pending', 'pending')");
        $total = 150.00;
        $stmt->bind_param('isd', $uid, $num, $total);
        $this->assertTrue($stmt->execute());
        $oid = $stmt->insert_id;

        $row = self::$conn->query("SELECT status, payment_status FROM orders WHERE id = $oid")->fetch_assoc();
        $this->assertSame('pending', $row['status']);
        $this->assertSame('pending', $row['payment_status']);

        self::$conn->query("DELETE FROM orders WHERE id = $oid");
    }

    public function test_order_status_transitions(): void
    {
        $uid = self::$testUserId;
        $shortTs = substr(time(), -8);
        $num = 'S' . $shortTs;
        self::$conn->query("INSERT INTO orders (user_id, order_number, total, status, payment_status) VALUES ($uid, '$num', 100.00, 'pending', 'pending')");
        $oid = self::$conn->insert_id;

        $transitions = ['processing', 'shipped', 'delivered'];
        foreach ($transitions as $status) {
            $stmt = self::$conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
            $stmt->bind_param('si', $status, $oid);
            $this->assertTrue($stmt->execute());
            $this->assertSame($status, self::$conn->query("SELECT status FROM orders WHERE id = $oid")->fetch_assoc()['status']);
        }

        self::$conn->query("DELETE FROM orders WHERE id = $oid");
    }

    public function test_order_payment_status_update(): void
    {
        $uid = self::$testUserId;
        $num = 'ORD-PAY-' . time();
        self::$conn->query("INSERT INTO orders (user_id, order_number, total, status, payment_status) VALUES ($uid, '$num', 100.00, 'pending', 'pending')");
        $oid = self::$conn->insert_id;

        $stmt = self::$conn->prepare("UPDATE orders SET payment_status = ? WHERE id = ?");
        $ps = 'paid';
        $stmt->bind_param('si', $ps, $oid);
        $this->assertTrue($stmt->execute());

        $row = self::$conn->query("SELECT payment_status FROM orders WHERE id = $oid")->fetch_assoc();
        $this->assertSame('paid', $row['payment_status']);

        self::$conn->query("DELETE FROM orders WHERE id = $oid");
    }

    // ========== CONSULTATIONS STATUS ==========

    public function test_consultation_create_and_status_update(): void
    {
        $uid = self::$testUserId;
        $did = self::$doctorId;

        // Create
        $stmt = self::$conn->prepare("INSERT INTO consultations (user_id, doctor_id, type, status, meeting_link) VALUES (?, ?, ?, 'scheduled', ?)");
        $type = 'video';
        $link = 'https://meet.example.com/test';
        $stmt->bind_param('iiss', $uid, $did, $type, $link);
        $this->assertTrue($stmt->execute());
        $cid = $stmt->insert_id;

        $row = self::$conn->query("SELECT type, status, meeting_link FROM consultations WHERE id = $cid")->fetch_assoc();
        $this->assertSame('scheduled', $row['status']);
        $this->assertSame('video', $row['type']);

        // Mark in_progress
        self::$conn->query("UPDATE consultations SET status = 'in_progress', started_at = NOW() WHERE id = $cid");
        $row = self::$conn->query("SELECT status FROM consultations WHERE id = $cid")->fetch_assoc();
        $this->assertSame('in_progress', $row['status']);

        // Mark completed
        self::$conn->query("UPDATE consultations SET status = 'completed', ended_at = NOW() WHERE id = $cid");
        $row = self::$conn->query("SELECT status FROM consultations WHERE id = $cid")->fetch_assoc();
        $this->assertSame('completed', $row['status']);

        self::$conn->query("DELETE FROM consultations WHERE id = $cid");
    }

    public function test_consultation_types_are_valid(): void
    {
        $uid = self::$testUserId;
        $did = self::$doctorId;

        $types = ['video', 'audio', 'chat'];
        foreach ($types as $type) {
            self::$conn->query("INSERT INTO consultations (user_id, doctor_id, type, status) VALUES ($uid, $did, '$type', 'scheduled')");
            $cid = self::$conn->insert_id;
            $row = self::$conn->query("SELECT type FROM consultations WHERE id = $cid")->fetch_assoc();
            $this->assertSame($type, $row['type']);
            self::$conn->query("DELETE FROM consultations WHERE id = $cid");
        }
    }

    public function test_consultation_meeting_link_stored(): void
    {
        $uid = self::$testUserId;
        $did = self::$doctorId;
        $link = 'https://meet.example.com/' . time();

        $stmt = self::$conn->prepare("INSERT INTO consultations (user_id, doctor_id, type, status, meeting_link) VALUES (?, ?, 'video', 'scheduled', ?)");
        $stmt->bind_param('iis', $uid, $did, $link);
        $stmt->execute();
        $cid = self::$conn->insert_id;

        $row = self::$conn->query("SELECT meeting_link FROM consultations WHERE id = $cid")->fetch_assoc();
        $this->assertSame($link, $row['meeting_link']);

        self::$conn->query("DELETE FROM consultations WHERE id = $cid");
    }

    public function test_consultation_timestamps_recorded(): void
    {
        $uid = self::$testUserId;
        $did = self::$doctorId;

        self::$conn->query("INSERT INTO consultations (user_id, doctor_id, type, status) VALUES ($uid, $did, 'video', 'scheduled')");
        $cid = self::$conn->insert_id;

        // Start
        self::$conn->query("UPDATE consultations SET status = 'in_progress', started_at = NOW() WHERE id = $cid");
        $row = self::$conn->query("SELECT started_at FROM consultations WHERE id = $cid")->fetch_assoc();
        $this->assertNotNull($row['started_at']);

        // End
        self::$conn->query("UPDATE consultations SET status = 'completed', ended_at = NOW() WHERE id = $cid");
        $row = self::$conn->query("SELECT ended_at FROM consultations WHERE id = $cid")->fetch_assoc();
        $this->assertNotNull($row['ended_at']);

        self::$conn->query("DELETE FROM consultations WHERE id = $cid");
    }
}
