<?php

namespace Ayurviro\Tests\Integration;

use PHPUnit\Framework\TestCase;

class MiscInsertsTest extends TestCase
{
    private static ?\mysqli $conn = null;
    private static int $testUserId = 0;

    public static function setUpBeforeClass(): void
    {
        try {
            require_once __DIR__ . '/../../config/database.php';
            self::$conn = getDB();

            $ts = time();
            $email = "misc_{$ts}@example.com";
            self::$conn->query("INSERT INTO users (full_name, email, password, role) VALUES ('MiscUser', '$email', 'hash', 'customer')");
            self::$testUserId = self::$conn->insert_id;
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

    // ========== CONTACTS ==========

    public function test_contact_form_insert(): void
    {
        $stmt = self::$conn->prepare("INSERT INTO contacts (name, email, subject, message) VALUES (?, ?, ?, ?)");
        $name = 'Test User';
        $email = 'test_' . time() . '@example.com';
        $subject = 'Test Subject';
        $msg = 'This is a test message';
        $stmt->bind_param('ssss', $name, $email, $subject, $msg);
        $this->assertTrue($stmt->execute());
        $cid = $stmt->insert_id;

        $row = self::$conn->query("SELECT * FROM contacts WHERE id = $cid")->fetch_assoc();
        $this->assertSame($name, $row['name']);
        $this->assertSame($email, $row['email']);
        $this->assertSame($subject, $row['subject']);
        $this->assertSame($msg, $row['message']);

        self::$conn->query("DELETE FROM contacts WHERE id = $cid");
    }

    public function test_contact_optional_subject(): void
    {
        $stmt = self::$conn->prepare("INSERT INTO contacts (name, email, message) VALUES (?, ?, ?)");
        $name = 'No Subject';
        $email = 'nosubject_' . time() . '@example.com';
        $msg = 'Message without subject';
        $stmt->bind_param('sss', $name, $email, $msg);
        $this->assertTrue($stmt->execute());
        $cid = $stmt->insert_id;

        $row = self::$conn->query("SELECT subject FROM contacts WHERE id = $cid")->fetch_assoc();
        $this->assertNull($row['subject']);

        self::$conn->query("DELETE FROM contacts WHERE id = $cid");
    }

    // ========== SUBSCRIBERS ==========

    public function test_subscriber_insert(): void
    {
        // Create table if needed (matching subscribe.php behavior)
        self::$conn->query("CREATE TABLE IF NOT EXISTS subscribers (id INT AUTO_INCREMENT PRIMARY KEY, email VARCHAR(255) UNIQUE NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");

        $email = 'subscriber_' . time() . '@example.com';
        $stmt = self::$conn->prepare("INSERT INTO subscribers (email) VALUES (?)");
        $stmt->bind_param('s', $email);
        $this->assertTrue($stmt->execute());
        $sid = $stmt->insert_id;

        $row = self::$conn->query("SELECT email FROM subscribers WHERE id = $sid")->fetch_assoc();
        $this->assertSame($email, $row['email']);

        self::$conn->query("DELETE FROM subscribers WHERE id = $sid");
    }

    public function test_subscriber_duplicate_email_rejected(): void
    {
        $email = 'dup_sub_' . time() . '@example.com';
        self::$conn->query("INSERT INTO subscribers (email) VALUES ('$email')");

        $this->expectException(\mysqli_sql_exception::class);
        self::$conn->query("INSERT INTO subscribers (email) VALUES ('$email')");

        self::$conn->query("DELETE FROM subscribers WHERE email = '$email'");
    }

    // ========== HEALTH REMINDERS ==========

    public function test_health_reminder_create(): void
    {
        $uid = self::$testUserId;

        $stmt = self::$conn->prepare("INSERT INTO health_reminders (user_id, reminder_type, reminder_time, active) VALUES (?, ?, ?, ?)");
        $type = 'water';
        $time = '09:00:00';
        $active = 1;
        $stmt->bind_param('issi', $uid, $type, $time, $active);
        $this->assertTrue($stmt->execute());
        $hid = $stmt->insert_id;

        $row = self::$conn->query("SELECT * FROM health_reminders WHERE id = $hid")->fetch_assoc();
        $this->assertSame('water', $row['reminder_type']);
        $this->assertSame('09:00:00', $row['reminder_time']);
        $this->assertEquals(1, $row['active']);

        self::$conn->query("DELETE FROM health_reminders WHERE id = $hid");
    }

    public function test_health_reminder_delete_all_for_user(): void
    {
        $uid = self::$testUserId;

        self::$conn->query("INSERT INTO health_reminders (user_id, reminder_type, reminder_time, active) VALUES ($uid, 'medicine', '08:00:00', 1)");
        self::$conn->query("INSERT INTO health_reminders (user_id, reminder_type, reminder_time, active) VALUES ($uid, 'water', '10:00:00', 1)");

        $stmt = self::$conn->prepare("DELETE FROM health_reminders WHERE user_id = ?");
        $stmt->bind_param('i', $uid);
        $stmt->execute();

        $count = (int)self::$conn->query("SELECT COUNT(*) as c FROM health_reminders WHERE user_id = $uid")->fetch_assoc()['c'];
        $this->assertEquals(0, $count);
    }

    public function test_health_reminder_types(): void
    {
        $uid = self::$testUserId;
        $types = ['medicine', 'water', 'yoga', 'diet'];

        foreach ($types as $type) {
            self::$conn->query("INSERT INTO health_reminders (user_id, reminder_type, reminder_time, active) VALUES ($uid, '$type', '12:00:00', 1)");
            $hid = self::$conn->insert_id;
            $row = self::$conn->query("SELECT reminder_type FROM health_reminders WHERE id = $hid")->fetch_assoc();
            $this->assertSame($type, $row['reminder_type']);
            self::$conn->query("DELETE FROM health_reminders WHERE id = $hid");
        }
    }

    // ========== LAB TEST BOOKINGS ==========

    public function test_lab_booking_insert(): void
    {
        $uid = self::$testUserId;
        $date = date('Y-m-d', strtotime('+1 week'));
        $time = '09:30:00';

        $stmt = self::$conn->prepare("INSERT INTO lab_test_bookings (user_id, test_id, booking_date, booking_time, collection_address, collection_type, amount, notes, status, payment_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', 'pending')");
        $testId = 1;
        $addr = '123 Test St';
        $colType = 'home';
        $amount = 500.00;
        $notes = 'Fasting required';
        $stmt->bind_param('iissssds', $uid, $testId, $date, $time, $addr, $colType, $amount, $notes);
        $this->assertTrue($stmt->execute());
        $bid = $stmt->insert_id;

        $row = self::$conn->query("SELECT * FROM lab_test_bookings WHERE id = $bid")->fetch_assoc();
        $this->assertSame('pending', $row['status']);
        $this->assertSame('home', $row['collection_type']);
        $this->assertSame('500.00', $row['amount']);

        self::$conn->query("DELETE FROM lab_test_bookings WHERE id = $bid");
    }

    // ========== SUBSCRIPTIONS ==========

    public function test_subscription_insert(): void
    {
        $uid = self::$testUserId;
        $nextDelivery = date('Y-m-d', strtotime('+1 month'));

        $stmt = self::$conn->prepare("INSERT INTO subscriptions (user_id, plan_name, frequency, status, next_delivery_date, total_amount) VALUES (?, ?, ?, 'active', ?, ?)");
        $plan = 'Monthly Wellness';
        $freq = 'monthly';
        $amount = 299.00;
        $stmt->bind_param('isssd', $uid, $plan, $freq, $nextDelivery, $amount);
        $this->assertTrue($stmt->execute());
        $sid = $stmt->insert_id;

        $row = self::$conn->query("SELECT * FROM subscriptions WHERE id = $sid")->fetch_assoc();
        $this->assertSame('Monthly Wellness', $row['plan_name']);
        $this->assertSame('monthly', $row['frequency']);
        $this->assertSame('active', $row['status']);
        $this->assertSame($nextDelivery, $row['next_delivery_date']);

        self::$conn->query("DELETE FROM subscriptions WHERE id = $sid");
    }

    // ========== ORDER PUNCH ==========

    public function test_order_punch_insert(): void
    {
        $uid = self::$testUserId;
        $orderNum = 'PUNCH-' . time();

        $stmt = self::$conn->prepare("INSERT INTO order_punch (user_id, order_number, order_type, source, total_amount, discount, gst_amount, net_amount, status, notes, delivery_date, created_at) VALUES (?, ?, 'retail', ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $source = 'manual';
        $total = 1000.00;
        $discount = 50.00;
        $gst = 90.00;
        $net = 1040.00;
        $status = 'draft';
        $notes = 'Test order';
        $delivery = date('Y-m-d');
        $stmt->bind_param('issdddssss', $uid, $orderNum, $source, $total, $discount, $gst, $net, $status, $notes, $delivery);
        $this->assertTrue($stmt->execute());
        $pid = $stmt->insert_id;

        $row = self::$conn->query("SELECT * FROM order_punch WHERE id = $pid")->fetch_assoc();
        $this->assertSame($orderNum, $row['order_number']);
        $this->assertSame('draft', $row['status']);
        $this->assertSame('1000.00', $row['total_amount']);

        self::$conn->query("DELETE FROM order_punch WHERE id = $pid");
    }

    public function test_order_punch_insert_items(): void
    {
        $uid = self::$testUserId;
        $orderNum = 'PUNCH-ITEM-' . time();

        self::$conn->query("INSERT INTO order_punch (user_id, order_number, order_type, status, total_amount, net_amount) VALUES ($uid, '$orderNum', 'retail', 'draft', 0, 0)");
        $pid = self::$conn->insert_id;

        // Insert items
        $productId = self::productId();
        $stmt = self::$conn->prepare("INSERT INTO order_punch_items (order_punch_id, product_id, quantity, price, total) VALUES (?, ?, ?, ?, ?)");
        $qty = 2;
        $price = 150.00;
        $total = 300.00;
        $stmt->bind_param('iiidd', $pid, $productId, $qty, $price, $total);
        $this->assertTrue($stmt->execute());
        $piid = $stmt->insert_id;

        $row = self::$conn->query("SELECT * FROM order_punch_items WHERE id = $piid")->fetch_assoc();
        $this->assertEquals(2, $row['quantity']);
        $this->assertSame('150.00', $row['price']);

        self::$conn->query("DELETE FROM order_punch_items WHERE id = $piid");
        self::$conn->query("DELETE FROM order_punch WHERE id = $pid");
    }

    private static function productId(): int
    {
        static $pid = null;
        if ($pid === null) {
            $pid = (int)self::$conn->query("SELECT id FROM products LIMIT 1")->fetch_assoc()['id'];
        }
        return $pid;
    }
}
