<?php

namespace Ayurviro\Tests\Integration;

use PHPUnit\Framework\TestCase;

class FamilyMemberTest extends TestCase
{
    private static ?\mysqli $conn = null;
    private static int $testUserId = 0;

    public static function setUpBeforeClass(): void
    {
        try {
            require_once __DIR__ . '/../../config/database.php';
            self::$conn = getDB();

            $ts = time();
            $email = "family_{$ts}@example.com";
            self::$conn->query("INSERT INTO users (full_name, email, password, role) VALUES ('FamilyUser', '$email', 'hash', 'customer')");
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
        self::$conn->query("DELETE FROM family_members WHERE user_id = " . self::$testUserId);
    }

    public function test_create_self_member(): void
    {
        $uid = self::$testUserId;

        $stmt = self::$conn->prepare("INSERT INTO family_members (user_id, full_name, relationship) VALUES (?, ?, 'self')");
        $name = 'Self User';
        $stmt->bind_param('is', $uid, $name);
        $this->assertTrue($stmt->execute());
        $mid = $stmt->insert_id;

        $row = self::$conn->query("SELECT * FROM family_members WHERE id = $mid")->fetch_assoc();
        $this->assertSame($name, $row['full_name']);
        $this->assertSame('self', $row['relationship']);
    }

    public function test_add_family_member_with_all_fields(): void
    {
        $uid = self::$testUserId;

        $stmt = self::$conn->prepare("INSERT INTO family_members (user_id, full_name, relationship, age, gender) VALUES (?, ?, ?, ?, ?)");
        $name = 'Spouse Name';
        $rel = 'spouse';
        $age = 30;
        $gender = 'female';
        $stmt->bind_param('issis', $uid, $name, $rel, $age, $gender);
        $this->assertTrue($stmt->execute());
        $mid = $stmt->insert_id;

        $row = self::$conn->query("SELECT * FROM family_members WHERE id = $mid")->fetch_assoc();
        $this->assertSame('Spouse Name', $row['full_name']);
        $this->assertSame('spouse', $row['relationship']);
        $this->assertEquals(30, $row['age']);
        $this->assertSame('female', $row['gender']);
    }

    public function test_update_family_member(): void
    {
        $uid = self::$testUserId;
        self::$conn->query("INSERT INTO family_members (user_id, full_name, relationship) VALUES ($uid, 'Old Name', 'son')");
        $mid = self::$conn->insert_id;

        $stmt = self::$conn->prepare("UPDATE family_members SET full_name=?, relationship=?, age=?, gender=? WHERE id=? AND user_id=?");
        $name = 'Updated Name';
        $rel = 'daughter';
        $age = 10;
        $gender = 'female';
        $stmt->bind_param('ssisii', $name, $rel, $age, $gender, $mid, $uid);
        $this->assertTrue($stmt->execute());

        $row = self::$conn->query("SELECT * FROM family_members WHERE id = $mid")->fetch_assoc();
        $this->assertSame('Updated Name', $row['full_name']);
        $this->assertSame('daughter', $row['relationship']);
        $this->assertEquals(10, $row['age']);
    }

    public function test_delete_non_self_member(): void
    {
        $uid = self::$testUserId;
        self::$conn->query("INSERT INTO family_members (user_id, full_name, relationship) VALUES ($uid, 'Child', 'son')");
        $mid = self::$conn->insert_id;

        // Delete with self-protection guard
        $stmt = self::$conn->prepare("DELETE FROM family_members WHERE id=? AND user_id=? AND relationship != 'self'");
        $stmt->bind_param('ii', $mid, $uid);
        $this->assertTrue($stmt->execute());
        $this->assertGreaterThan(0, $stmt->affected_rows);

        $this->assertNull(self::$conn->query("SELECT id FROM family_members WHERE id = $mid")->fetch_assoc());
    }

    public function test_cannot_delete_self_member(): void
    {
        $uid = self::$testUserId;

        // Create self member
        self::$conn->query("INSERT INTO family_members (user_id, full_name, relationship) VALUES ($uid, 'Self', 'self')");
        $mid = self::$conn->insert_id;

        // Try to delete self (should fail due to guard)
        $stmt = self::$conn->prepare("DELETE FROM family_members WHERE id=? AND user_id=? AND relationship != 'self'");
        $stmt->bind_param('ii', $mid, $uid);
        $stmt->execute();
        $this->assertEquals(0, $stmt->affected_rows);

        // Self record still exists
        $this->assertNotNull(self::$conn->query("SELECT id FROM family_members WHERE id = $mid")->fetch_assoc());

        self::$conn->query("DELETE FROM family_members WHERE id = $mid");
    }

    public function test_list_family_members(): void
    {
        $uid = self::$testUserId;

        self::$conn->query("INSERT INTO family_members (user_id, full_name, relationship) VALUES ($uid, 'Self', 'self')");
        self::$conn->query("INSERT INTO family_members (user_id, full_name, relationship) VALUES ($uid, 'Spouse', 'spouse')");
        self::$conn->query("INSERT INTO family_members (user_id, full_name, relationship) VALUES ($uid, 'Child', 'daughter')");

        $rows = self::$conn->query("SELECT * FROM family_members WHERE user_id = $uid ORDER BY FIELD(relationship,'self','spouse','son','daughter','father','mother','other'), created_at");
        $this->assertEquals(3, $rows->num_rows);

        $members = $rows->fetch_all(MYSQLI_ASSOC);
        $this->assertSame('self', $members[0]['relationship']);
    }

    public function test_all_relationship_types(): void
    {
        $uid = self::$testUserId;
        $rels = ['self', 'spouse', 'son', 'daughter', 'father', 'mother', 'other'];

        foreach ($rels as $rel) {
            self::$conn->query("INSERT INTO family_members (user_id, full_name, relationship) VALUES ($uid, 'Test $rel', '$rel')");
            $id = self::$conn->insert_id;
            $row = self::$conn->query("SELECT relationship FROM family_members WHERE id = $id")->fetch_assoc();
            $this->assertSame($rel, $row['relationship']);
            self::$conn->query("DELETE FROM family_members WHERE id = $id");
        }
    }

    public function test_family_member_deletes_when_user_deleted(): void
    {
        $ts = time();
        $email = "fam_cascade_{$ts}@example.com";
        self::$conn->query("INSERT INTO users (full_name, email, password) VALUES ('CascadeUser', '$email', 'hash')");
        $uid = self::$conn->insert_id;

        self::$conn->query("INSERT INTO family_members (user_id, full_name, relationship) VALUES ($uid, 'Dep', 'self')");
        $mid = self::$conn->insert_id;

        // Delete user (should cascade delete family_members)
        self::$conn->query("DELETE FROM users WHERE id = $uid");

        $this->assertNull(self::$conn->query("SELECT id FROM family_members WHERE id = $mid")->fetch_assoc());
    }
}
