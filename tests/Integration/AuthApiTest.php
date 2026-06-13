<?php

namespace Ayurviro\Tests\Integration;

use PHPUnit\Framework\TestCase;

class AuthApiTest extends TestCase
{
    private static ?\mysqli $conn = null;
    private static string $testEmail = '';
    private static int $testUserId = 0;

    public static function setUpBeforeClass(): void
    {
        $ts = time();
        self::$testEmail = "test_{$ts}@example.com";

        try {
            require_once __DIR__ . '/../../config/database.php';
            self::$conn = getDB();

            // Create test user for login tests
            $hashed = password_hash('testpass123', PASSWORD_DEFAULT);
            $stmt = self::$conn->prepare(
                "INSERT INTO users (full_name, email, mobile, password, role) VALUES (?, ?, ?, ?, 'customer')"
            );
            $name = 'Test User';
            $mobile = '9999999999';
            $stmt->bind_param('ssss', $name, self::$testEmail, $mobile, $hashed);
            $stmt->execute();
            self::$testUserId = (int)$stmt->insert_id;
        } catch (\Throwable $e) {
            self::markTestSkipped('MySQL not available: ' . $e->getMessage());
        }
    }

    public static function tearDownAfterClass(): void
    {
        if (self::$conn) {
            self::$conn->query("DELETE FROM users WHERE email LIKE 'test_%@example.com'");
        }
    }

    protected function setUp(): void
    {
        $_SESSION = ['csrf_token' => bin2hex(random_bytes(32))];
    }

    public function test_created_user_exists_in_db(): void
    {
        $stmt = self::$conn->prepare("SELECT id, full_name, email, role FROM users WHERE email = ?");
        $stmt->bind_param('s', self::$testEmail);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        $this->assertNotNull($user);
        $this->assertSame('Test User', $user['full_name']);
        $this->assertSame(self::$testEmail, $user['email']);
        $this->assertSame('customer', $user['role']);
        $this->assertGreaterThan(0, (int)$user['id']);
    }

    public function test_duplicate_email_throws_integrity_error(): void
    {
        $this->expectException(\mysqli_sql_exception::class);

        $hashed = password_hash('testpass123', PASSWORD_DEFAULT);
        $stmt = self::$conn->prepare(
            "INSERT INTO users (full_name, email, mobile, password, role) VALUES (?, ?, ?, ?, 'customer')"
        );
        $name = 'Dup User';
        $mobile = '8888888888';
        $stmt->bind_param('ssss', $name, self::$testEmail, $mobile, $hashed);
        $stmt->execute();
    }

    public function test_login_verify_password(): void
    {
        $stmt = self::$conn->prepare("SELECT id, full_name, password, role FROM users WHERE email = ?");
        $stmt->bind_param('s', self::$testEmail);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        $this->assertNotNull($user);
        $this->assertTrue(password_verify('testpass123', $user['password']));
    }

    public function test_login_with_invalid_password_fails(): void
    {
        $stmt = self::$conn->prepare("SELECT password FROM users WHERE email = ?");
        $stmt->bind_param('s', self::$testEmail);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        $this->assertNotNull($user);
        $this->assertFalse(password_verify('wrongpassword', $user['password']));
    }

    public function test_login_with_nonexistent_email_returns_null(): void
    {
        $stmt = self::$conn->prepare("SELECT id FROM users WHERE email = ?");
        $nonexistent = 'nonexistent_' . time() . '@example.com';
        $stmt->bind_param('s', $nonexistent);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        $this->assertNull($result);
    }

    public function test_password_hash_roundtrip(): void
    {
        $password = 'securePass!@#123';
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $this->assertTrue(password_verify($password, $hash));
        $this->assertFalse(password_verify('wrong', $hash));
    }

    public function test_user_role_is_customer(): void
    {
        $stmt = self::$conn->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->bind_param('i', self::$testUserId);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        $this->assertSame('customer', $user['role']);
    }

    public function test_new_user_has_auto_incremented_id(): void
    {
        $hashed = password_hash('temp1234', PASSWORD_DEFAULT);
        $email = 'temp_autoinc_' . time() . '@example.com';
        $stmt = self::$conn->prepare(
            "INSERT INTO users (full_name, email, mobile, password, role) VALUES (?, ?, ?, ?, 'customer')"
        );
        $name = 'Temp';
        $mobile = '1111111111';
        $stmt->bind_param('ssss', $name, $email, $mobile, $hashed);
        $stmt->execute();

        $newId = (int)$stmt->insert_id;
        $this->assertGreaterThan(self::$testUserId, $newId);

        // Cleanup
        self::$conn->query("DELETE FROM users WHERE id = $newId");
    }

    public function test_user_can_be_retrieved_by_id(): void
    {
        $stmt = self::$conn->prepare("SELECT full_name, email FROM users WHERE id = ?");
        $stmt->bind_param('i', self::$testUserId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        $this->assertSame(self::$testEmail, $result['email']);
    }

    public function test_delete_user_removes_record(): void
    {
        $email = 'temp_delete_' . time() . '@example.com';
        $hashed = password_hash('test', PASSWORD_DEFAULT);

        // Insert temp user
        $stmt = self::$conn->prepare("INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, 'customer')");
        $name = 'ToDelete';
        $stmt->bind_param('sss', $name, $email, $hashed);
        $stmt->execute();
        $delId = (int)$stmt->insert_id;

        // Delete
        self::$conn->query("DELETE FROM users WHERE id = $delId");

        // Verify gone
        $check = self::$conn->query("SELECT id FROM users WHERE id = $delId");
        $this->assertNull($check->fetch_assoc());
    }
}
