<?php

namespace Ayurviro\Tests\Integration;

use PHPUnit\Framework\TestCase;

class PatientMetricTest extends TestCase
{
    private static ?\mysqli $conn = null;
    private static int $testUserId = 0;

    public static function setUpBeforeClass(): void
    {
        try {
            require_once __DIR__ . '/../../config/database.php';
            self::$conn = getDB();

            $ts = time();
            $email = "metric_{$ts}@example.com";
            self::$conn->query("INSERT INTO users (full_name, email, password, role) VALUES ('MetricUser', '$email', 'hash', 'customer')");
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
        self::$conn->query("DELETE FROM patient_metrics WHERE user_id = " . self::$testUserId);
    }

    public function test_insert_patient_metric(): void
    {
        $uid = self::$testUserId;
        $today = date('Y-m-d');

        $stmt = self::$conn->prepare("INSERT INTO patient_metrics (user_id, record_date, weight, sleep_hours, pain_score, bp_systolic, bp_diastolic, blood_sugar, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $weight = 72.5;
        $sleep = 7.5;
        $pain = 2;
        $bpSys = 120;
        $bpDia = 80;
        $sugar = 95;
        $notes = 'Feeling good';
        $stmt->bind_param('isddiiiis', $uid, $today, $weight, $sleep, $pain, $bpSys, $bpDia, $sugar, $notes);
        $this->assertTrue($stmt->execute());
        $mid = $stmt->insert_id;

        $row = self::$conn->query("SELECT * FROM patient_metrics WHERE id = $mid")->fetch_assoc();
        $this->assertSame('72.50', $row['weight']);
        $this->assertSame('7.5', (string)$row['sleep_hours']);
        $this->assertEquals(2, $row['pain_score']);
        $this->assertEquals(120, $row['bp_systolic']);
        $this->assertEquals(95, $row['blood_sugar']);
    }

    public function test_upsert_updates_existing_record(): void
    {
        $uid = self::$testUserId;
        $today = date('Y-m-d');

        // Insert initial
        self::$conn->query("INSERT INTO patient_metrics (user_id, record_date, weight, sleep_hours, pain_score, notes) VALUES ($uid, '$today', 70.0, 6.0, 3, 'initial')");
        $mid = self::$conn->insert_id;

        // Upsert - update same day
        $stmt = self::$conn->prepare("INSERT INTO patient_metrics (user_id, record_date, weight, sleep_hours, pain_score, bp_systolic, bp_diastolic, blood_sugar, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE weight = VALUES(weight), sleep_hours = VALUES(sleep_hours), pain_score = VALUES(pain_score), bp_systolic = VALUES(bp_systolic), bp_diastolic = VALUES(bp_diastolic), blood_sugar = VALUES(blood_sugar), notes = VALUES(notes)");
        $weight = 71.2;
        $sleep = 8.0;
        $pain = 1;
        $bpSys = 118;
        $bpDia = 76;
        $sugar = 90;
        $notes = 'Updated';
        $stmt->bind_param('isddiiiis', $uid, $today, $weight, $sleep, $pain, $bpSys, $bpDia, $sugar, $notes);
        $this->assertTrue($stmt->execute());

        // Verify updated - should be same id
        $row = self::$conn->query("SELECT * FROM patient_metrics WHERE id = $mid")->fetch_assoc();
        $this->assertSame('71.20', $row['weight']);
        $this->assertSame('8.0', (string)$row['sleep_hours']);
        $this->assertEquals(1, $row['pain_score']);
        $this->assertEquals('Updated', $row['notes']);

        // Should still be only 1 record for today
        $count = (int)self::$conn->query("SELECT COUNT(*) as c FROM patient_metrics WHERE user_id = $uid AND record_date = '$today'")->fetch_assoc()['c'];
        $this->assertEquals(1, $count);
    }

    public function test_upsert_creates_new_when_no_existing(): void
    {
        $uid = self::$testUserId;
        $yesterday = date('Y-m-d', strtotime('-1 day'));

        $stmt = self::$conn->prepare("INSERT INTO patient_metrics (user_id, record_date, weight, sleep_hours, pain_score, notes) VALUES (?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE weight = VALUES(weight)");
        $weight = 68.0;
        $sleep = 7.0;
        $pain = 0;
        $notes = 'First entry';
        $stmt->bind_param('isddis', $uid, $yesterday, $weight, $sleep, $pain, $notes);
        $this->assertTrue($stmt->execute());
        $mid = $stmt->insert_id;

        $this->assertGreaterThan(0, $mid);
        $row = self::$conn->query("SELECT * FROM patient_metrics WHERE id = $mid")->fetch_assoc();
        $this->assertSame('68.00', $row['weight']);
    }

    public function test_multiple_days_of_metrics(): void
    {
        $uid = self::$testUserId;

        for ($i = 0; $i < 5; $i++) {
            $d = date('Y-m-d', strtotime("-$i days"));
            self::$conn->query("INSERT INTO patient_metrics (user_id, record_date, weight, sleep_hours, pain_score) VALUES ($uid, '$d', " . (70 + $i) . ", " . (7 + $i * 0.5) . ", $i)");
        }

        $rows = self::$conn->query("SELECT COUNT(*) as c FROM patient_metrics WHERE user_id = $uid");
        $this->assertEquals(5, (int)$rows->fetch_assoc()['c']);

        // Ordered by date desc
        $ordered = self::$conn->query("SELECT record_date FROM patient_metrics WHERE user_id = $uid ORDER BY record_date DESC LIMIT 5");
        $dates = [];
        while ($r = $ordered->fetch_assoc()) {
            $dates[] = $r['record_date'];
        }
        $this->assertCount(5, $dates);
    }

    public function test_metrics_with_partial_data(): void
    {
        $uid = self::$testUserId;
        $today = date('Y-m-d');

        // Insert with only weight and blood_sugar
        $stmt = self::$conn->prepare("INSERT INTO patient_metrics (user_id, record_date, weight, blood_sugar) VALUES (?, ?, ?, ?)");
        $weight = 75.0;
        $sugar = 110;
        $stmt->bind_param('isdi', $uid, $today, $weight, $sugar);
        $this->assertTrue($stmt->execute());
        $mid = $stmt->insert_id;

        $row = self::$conn->query("SELECT * FROM patient_metrics WHERE id = $mid")->fetch_assoc();
        $this->assertSame('75.00', $row['weight']);
        $this->assertSame(110, (int)$row['blood_sugar']);
        $this->assertNull($row['sleep_hours']);
        $this->assertNull($row['pain_score']);
    }

    public function test_pain_score_range(): void
    {
        $uid = self::$testUserId;
        $today = date('Y-m-d');

        foreach ([1, 5, 10] as $score) {
            self::$conn->query("INSERT INTO patient_metrics (user_id, record_date, pain_score) VALUES ($uid, '$today', $score)");
            $mid = self::$conn->insert_id;
            $row = self::$conn->query("SELECT pain_score FROM patient_metrics WHERE id = $mid")->fetch_assoc();
            $this->assertEquals($score, $row['pain_score']);
            self::$conn->query("DELETE FROM patient_metrics WHERE id = $mid");
        }
    }
}
