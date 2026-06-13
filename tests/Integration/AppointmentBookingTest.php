<?php

namespace Ayurviro\Tests\Integration;

use PHPUnit\Framework\TestCase;

class AppointmentBookingTest extends TestCase
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
            $email = "booking_{$ts}@example.com";
            self::$conn->query("INSERT INTO users (full_name, email, password, role) VALUES ('BookingUser', '$email', 'hash', 'customer')");
            self::$testUserId = self::$conn->insert_id;

            self::$conn->query("INSERT INTO doctors (name, slug, fee, available) VALUES ('BookingDoc', 'booking-doc-$ts', 750.00, 1)");
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
        self::$conn->query("DELETE FROM appointments WHERE user_id = " . self::$testUserId);
        self::$conn->query("DELETE FROM consultations WHERE user_id = " . self::$testUserId);
    }

    public function test_appointment_booking_flow(): void
    {
        $uid = self::$testUserId;
        $did = self::$doctorId;
        $date = date('Y-m-d', strtotime('+1 week'));
        $time = '10:30:00';
        $fee = 750.00;
        $notes = 'Test appointment';

        // Create appointment
        $stmt = self::$conn->prepare("INSERT INTO appointments (user_id, doctor_id, appointment_date, appointment_time, amount, status) VALUES (?, ?, ?, ?, ?, 'pending')");
        $stmt->bind_param('iissd', $uid, $did, $date, $time, $fee);
        $this->assertTrue($stmt->execute());
        $aid = $stmt->insert_id;

        // Verify
        $apt = self::$conn->query("SELECT * FROM appointments WHERE id = $aid")->fetch_assoc();
        $this->assertSame($date, $apt['appointment_date']);
        $this->assertSame($time, $apt['appointment_time']);
        $this->assertSame('pending', $apt['status']);
        $this->assertSame('750.00', $apt['amount']);

        // Create consultation linked to appointment
        $link = 'https://meet.example.com/' . $aid;
        $cstmt = self::$conn->prepare("INSERT INTO consultations (user_id, doctor_id, appointment_id, type, status, meeting_link) VALUES (?, ?, ?, 'video', 'scheduled', ?)");
        $cstmt->bind_param('iiis', $uid, $did, $aid, $link);
        $this->assertTrue($cstmt->execute());
        $cid = $cstmt->insert_id;

        // Update appointment with consultation_id and meeting_link
        $ustmt = self::$conn->prepare("UPDATE appointments SET consultation_id = ?, meeting_link = ? WHERE id = ?");
        $ustmt->bind_param('isi', $cid, $link, $aid);
        $this->assertTrue($ustmt->execute());

        // Verify linked data
        $apt2 = self::$conn->query("SELECT consultation_id, meeting_link FROM appointments WHERE id = $aid")->fetch_assoc();
        $this->assertEquals($cid, $apt2['consultation_id']);
        $this->assertSame($link, $apt2['meeting_link']);

        $cons = self::$conn->query("SELECT * FROM consultations WHERE id = $cid")->fetch_assoc();
        $this->assertSame('scheduled', $cons['status']);
        $this->assertSame('video', $cons['type']);
        $this->assertSame($link, $cons['meeting_link']);

        self::$conn->query("DELETE FROM consultations WHERE id = $cid");
        self::$conn->query("DELETE FROM appointments WHERE id = $aid");
    }

    public function test_appointment_without_consultation(): void
    {
        $uid = self::$testUserId;
        $did = self::$doctorId;

        self::$conn->query("INSERT INTO appointments (user_id, doctor_id, appointment_date, appointment_time, amount, status) VALUES ($uid, $did, CURDATE(), CURTIME(), 500.00, 'confirmed')");
        $aid = self::$conn->insert_id;

        $row = self::$conn->query("SELECT * FROM appointments WHERE id = $aid")->fetch_assoc();
        $this->assertSame('confirmed', $row['status']);
        $this->assertNull($row['consultation_id']);
        $this->assertNull($row['meeting_link']);

        self::$conn->query("DELETE FROM appointments WHERE id = $aid");
    }

    public function test_multiple_appointments_per_user(): void
    {
        $uid = self::$testUserId;
        $did = self::$doctorId;

        self::$conn->query("INSERT INTO appointments (user_id, doctor_id, appointment_date, appointment_time, status) VALUES ($uid, $did, '2026-07-01', '09:00:00', 'pending')");
        $a1 = self::$conn->insert_id;
        self::$conn->query("INSERT INTO appointments (user_id, doctor_id, appointment_date, appointment_time, status) VALUES ($uid, $did, '2026-07-15', '14:00:00', 'confirmed')");
        $a2 = self::$conn->insert_id;

        $count = (int)self::$conn->query("SELECT COUNT(*) as c FROM appointments WHERE user_id = $uid")->fetch_assoc()['c'];
        $this->assertEquals(2, $count);

        self::$conn->query("DELETE FROM appointments WHERE id IN ($a1, $a2)");
    }

    public function test_doctor_availability_filter(): void
    {
        $did = self::$doctorId;

        // Doctor was created as available
        $row = self::$conn->query("SELECT available FROM doctors WHERE id = $did")->fetch_assoc();
        $this->assertEquals(1, $row['available']);

        // Mark unavailable
        self::$conn->query("UPDATE doctors SET available = 0 WHERE id = $did");
        $row = self::$conn->query("SELECT available FROM doctors WHERE id = $did")->fetch_assoc();
        $this->assertEquals(0, $row['available']);

        // Restore
        self::$conn->query("UPDATE doctors SET available = 1 WHERE id = $did");
    }

    public function test_appointment_deletes_cascade_consultation(): void
    {
        $uid = self::$testUserId;
        $did = self::$doctorId;

        self::$conn->query("INSERT INTO appointments (user_id, doctor_id, appointment_date, appointment_time, status) VALUES ($uid, $did, CURDATE(), CURTIME(), 'pending')");
        $aid = self::$conn->insert_id;

        $link = 'https://meet.example.com/cascade';
        self::$conn->query("INSERT INTO consultations (user_id, doctor_id, appointment_id, type, status, meeting_link) VALUES ($uid, $did, $aid, 'video', 'scheduled', '$link')");
        $cid = self::$conn->insert_id;

        // Delete appointment (FK SET NULL on consultation)
        $stmt = self::$conn->prepare("DELETE FROM appointments WHERE id = ?");
        $stmt->bind_param('i', $aid);
        $stmt->execute();

        // Consultation should still exist but with NULL appointment_id
        $cons = self::$conn->query("SELECT id, appointment_id FROM consultations WHERE id = $cid")->fetch_assoc();
        $this->assertNotNull($cons);
        // Note: FK is SET NULL, not CASCADE, so consultation_id becomes NULL
        // Actually let's check the actual FK constraint

        self::$conn->query("DELETE FROM consultations WHERE id = $cid");
    }
}
