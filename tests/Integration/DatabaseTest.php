<?php

namespace Ayurviro\Tests\Integration;

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/feature_helpers.php';

use PHPUnit\Framework\TestCase;

class DatabaseTest extends TestCase
{
    private static ?\mysqli $conn = null;

    public static function setUpBeforeClass(): void
    {
        try {
            self::$conn = getDB();
        } catch (\Throwable $e) {
            self::markTestSkipped('MySQL not available: ' . $e->getMessage());
        }
    }

    public function test_getDB_returns_mysqli_instance(): void
    {
        $this->assertInstanceOf(\mysqli::class, self::$conn);
    }

    public function test_getDB_is_singleton(): void
    {
        $db1 = getDB();
        $db2 = getDB();
        $this->assertSame($db1, $db2);
    }

    /** @depends test_getDB_returns_mysqli_instance */
    public function test_connection_uses_correct_database(): void
    {
        $result = self::$conn->query("SELECT DATABASE() AS db");
        $row = $result->fetch_assoc();
        $this->assertSame('public_care_ayurveda', $row['db']);
    }

    /** @depends test_getDB_returns_mysqli_instance */
    public function test_table_exists_returns_true_for_core_tables(): void
    {
        $this->assertTrue(table_exists(self::$conn, 'users'));
        $this->assertTrue(table_exists(self::$conn, 'products'));
        $this->assertTrue(table_exists(self::$conn, 'categories'));
        $this->assertTrue(table_exists(self::$conn, 'orders'));
        $this->assertTrue(table_exists(self::$conn, 'cart'));
    }

    /** @depends test_getDB_returns_mysqli_instance */
    public function test_table_exists_returns_true_for_feature_tables(): void
    {
        $this->assertTrue(table_exists(self::$conn, 'dosha_questions'));
        $this->assertTrue(table_exists(self::$conn, 'dosha_assessments'));
        $this->assertTrue(table_exists(self::$conn, 'health_reminders'));
        $this->assertTrue(table_exists(self::$conn, 'patient_metrics'));
        $this->assertTrue(table_exists(self::$conn, 'family_members'));
        $this->assertTrue(table_exists(self::$conn, 'consultations'));
        $this->assertTrue(table_exists(self::$conn, 'prescriptions'));
        $this->assertTrue(table_exists(self::$conn, 'settings'));
    }

    /** @depends test_getDB_returns_mysqli_instance */
    public function test_table_exists_returns_false_for_nonexistent(): void
    {
        $this->assertFalse(table_exists(self::$conn, 'nonexistent_table_xyz'));
    }

    /** @depends test_getDB_returns_mysqli_instance */
    public function test_column_exists_returns_true_for_known_columns(): void
    {
        $this->assertTrue(column_exists(self::$conn, 'users', 'id'));
        $this->assertTrue(column_exists(self::$conn, 'users', 'email'));
        $this->assertTrue(column_exists(self::$conn, 'users', 'password'));
        $this->assertTrue(column_exists(self::$conn, 'users', 'role'));
    }

    /** @depends test_getDB_returns_mysqli_instance */
    public function test_column_exists_returns_true_for_preferred_language(): void
    {
        $this->assertTrue(column_exists(self::$conn, 'users', 'preferred_language'));
    }

    /** @depends test_getDB_returns_mysqli_instance */
    public function test_column_exists_returns_true_for_email_notifications(): void
    {
        $this->assertTrue(column_exists(self::$conn, 'users', 'email_notifications'));
    }

    /** @depends test_getDB_returns_mysqli_instance */
    public function test_column_exists_returns_false_for_nonexistent(): void
    {
        $this->assertFalse(column_exists(self::$conn, 'users', 'nonexistent_column'));
    }

    /** @depends test_getDB_returns_mysqli_instance */
    public function test_products_table_has_seed_data(): void
    {
        $result = self::$conn->query("SELECT COUNT(*) as c FROM products");
        $count = (int)$result->fetch_assoc()['c'];
        $this->assertGreaterThanOrEqual(4, $count);
    }

    /** @depends test_getDB_returns_mysqli_instance */
    public function test_categories_table_has_seed_data(): void
    {
        $result = self::$conn->query("SELECT COUNT(*) as c FROM categories");
        $count = (int)$result->fetch_assoc()['c'];
        $this->assertGreaterThanOrEqual(5, $count);
    }

    /** @depends test_getDB_returns_mysqli_instance */
    public function test_doctors_table_has_seed_data(): void
    {
        $result = self::$conn->query("SELECT COUNT(*) as c FROM doctors");
        $count = (int)$result->fetch_assoc()['c'];
        $this->assertGreaterThanOrEqual(3, $count);
    }

    /** @depends test_getDB_returns_mysqli_instance */
    public function test_blog_posts_has_seed_data(): void
    {
        $result = self::$conn->query("SELECT COUNT(*) as c FROM blog_posts");
        $count = (int)$result->fetch_assoc()['c'];
        $this->assertGreaterThanOrEqual(3, $count);
    }

    /** @depends test_getDB_returns_mysqli_instance */
    public function test_settings_has_openai_key_row(): void
    {
        $result = self::$conn->query("SELECT setting_value FROM settings WHERE setting_key = 'openai_api_key'");
        $this->assertNotNull($result->fetch_assoc());
    }

    /** @depends test_getDB_returns_mysqli_instance */
    public function test_settings_has_openai_model_row(): void
    {
        $result = self::$conn->query("SELECT setting_value FROM settings WHERE setting_key = 'openai_model'");
        $row = $result->fetch_assoc();
        $this->assertSame('gpt-5.2', $row['setting_value']);
    }

    /** @depends test_getDB_returns_mysqli_instance */
    public function test_fetch_products_returns_array(): void
    {
        $products = fetch_products(self::$conn, 4);
        $this->assertIsArray($products);
        $this->assertCount(4, $products);
    }

    /** @depends test_getDB_returns_mysqli_instance */
    public function test_fetch_products_clamps_limit(): void
    {
        $products = fetch_products(self::$conn, 100);
        $this->assertIsArray($products);
        $this->assertLessThanOrEqual(24, count($products));
    }

    /** @depends test_getDB_returns_mysqli_instance */
    public function test_fetch_products_bestseller_first(): void
    {
        $products = fetch_products(self::$conn, 10);
        if (count($products) >= 2) {
            $this->assertTrue(
                $products[0]['is_bestseller'] || !$products[1]['is_bestseller']
            );
        }
        $this->assertNotEmpty($products);
    }
}
