<?php

namespace Ayurviro\Tests\Integration;

use PHPUnit\Framework\TestCase;

class CartApiTest extends TestCase
{
    private static ?\mysqli $conn = null;
    private static int $testUserId = 0;
    private static int $productId = 0;

    public static function setUpBeforeClass(): void
    {
        try {
            require_once __DIR__ . '/../../config/database.php';
            require_once __DIR__ . '/../../includes/feature_helpers.php';
            self::$conn = getDB();

            // Create a disposable test user
            $email = 'cart_test_' . time() . '@example.com';
            $hashed = password_hash('testpass', PASSWORD_DEFAULT);
            $stmt = self::$conn->prepare(
                "INSERT INTO users (full_name, email, mobile, password, role) VALUES (?, ?, ?, ?, 'customer')"
            );
            $name = 'Cart Tester';
            $mobile = '7777777777';
            $stmt->bind_param('ssss', $name, $email, $mobile, $hashed);
            $stmt->execute();
            self::$testUserId = (int)$stmt->insert_id;

            // Get first product from seed data
            $result = self::$conn->query("SELECT id FROM products LIMIT 1");
            $row = $result->fetch_assoc();
            self::$productId = (int)$row['id'];
        } catch (\Throwable $e) {
            self::markTestSkipped('MySQL not available: ' . $e->getMessage());
        }
    }

    public static function tearDownAfterClass(): void
    {
        if (self::$conn) {
            self::$conn->query("DELETE FROM cart WHERE user_id = " . self::$testUserId);
            self::$conn->query("DELETE FROM users WHERE id = " . self::$testUserId);
        }
    }

    protected function setUp(): void
    {
        // Clean cart before each test
        if (self::$conn) {
            self::$conn->query("DELETE FROM cart WHERE user_id = " . self::$testUserId);
        }
        $_SESSION = ['csrf_token' => bin2hex(random_bytes(32))];
    }

    public function test_add_item_to_cart(): void
    {
        $uid = self::$testUserId;
        $pid = self::$productId;

        self::$conn->query("INSERT INTO cart (user_id, product_id, quantity) VALUES ($uid, $pid, 1)");
        $this->assertSame(0, self::$conn->errno);

        $result = self::$conn->query("SELECT quantity FROM cart WHERE user_id = $uid AND product_id = $pid");
        $row = $result->fetch_assoc();
        $this->assertEquals(1, (int)$row['quantity']);
    }

    public function test_increase_item_quantity(): void
    {
        $uid = self::$testUserId;
        $pid = self::$productId;

        self::$conn->query("INSERT INTO cart (user_id, product_id, quantity) VALUES ($uid, $pid, 1)");
        self::$conn->query("UPDATE cart SET quantity = quantity + 1 WHERE user_id = $uid AND product_id = $pid");

        $result = self::$conn->query("SELECT quantity FROM cart WHERE user_id = $uid AND product_id = $pid");
        $row = $result->fetch_assoc();
        $this->assertEquals(2, (int)$row['quantity']);
    }

    public function test_decrease_item_quantity(): void
    {
        $uid = self::$testUserId;
        $pid = self::$productId;

        self::$conn->query("INSERT INTO cart (user_id, product_id, quantity) VALUES ($uid, $pid, 3)");
        self::$conn->query("UPDATE cart SET quantity = quantity - 1 WHERE user_id = $uid AND product_id = $pid AND quantity > 1");

        $result = self::$conn->query("SELECT quantity FROM cart WHERE user_id = $uid AND product_id = $pid");
        $row = $result->fetch_assoc();
        $this->assertEquals(2, (int)$row['quantity']);
    }

    public function test_remove_item_from_cart(): void
    {
        $uid = self::$testUserId;
        $pid = self::$productId;

        self::$conn->query("INSERT INTO cart (user_id, product_id, quantity) VALUES ($uid, $pid, 1)");
        self::$conn->query("DELETE FROM cart WHERE user_id = $uid AND product_id = $pid");

        $result = self::$conn->query("SELECT COUNT(*) as c FROM cart WHERE user_id = $uid AND product_id = $pid");
        $row = $result->fetch_assoc();
        $this->assertEquals(0, (int)$row['c']);
    }

    public function test_cart_count_is_accurate(): void
    {
        $uid = self::$testUserId;
        $pid1 = self::$productId;

        // Add product with qty 2
        self::$conn->query("INSERT INTO cart (user_id, product_id, quantity) VALUES ($uid, $pid1, 2)");

        $result = self::$conn->query("SELECT SUM(quantity) as c FROM cart WHERE user_id = $uid");
        $row = $result->fetch_assoc();
        $this->assertEquals(2, (int)$row['c']);
    }

    public function test_cart_supports_multiple_items(): void
    {
        $uid = self::$testUserId;

        // Get another product
        $result = self::$conn->query("SELECT id FROM products WHERE id != " . self::$productId . " LIMIT 1");
        $row = $result->fetch_assoc();
        $pid2 = (int)$row['id'];

        // Add two different products
        self::$conn->query("INSERT INTO cart (user_id, product_id, quantity) VALUES ($uid, " . self::$productId . ", 1)");
        self::$conn->query("INSERT INTO cart (user_id, product_id, quantity) VALUES ($uid, $pid2, 3)");

        $result = self::$conn->query("SELECT COUNT(*) as c, SUM(quantity) as t FROM cart WHERE user_id = $uid");
        $row = $result->fetch_assoc();

        $this->assertEquals(2, (int)$row['c']);
        $this->assertEquals(4, (int)$row['t']);
    }

    public function test_cart_is_empty_after_delete_all(): void
    {
        $uid = self::$testUserId;
        self::$conn->query("DELETE FROM cart WHERE user_id = $uid");

        $result = self::$conn->query("SELECT COUNT(*) as c FROM cart WHERE user_id = $uid");
        $row = $result->fetch_assoc();
        $this->assertEquals(0, (int)$row['c']);
    }

    public function test_guest_cart_uses_session(): void
    {
        $_SESSION['cart'] = [];
        $_SESSION['cart'][self::$productId] = 2;

        $this->assertArrayHasKey(self::$productId, $_SESSION['cart']);
        $this->assertEquals(2, $_SESSION['cart'][self::$productId]);
    }

    public function test_guest_cart_count_sync(): void
    {
        $_SESSION['cart'] = [self::$productId => 3, (self::$productId + 1) => 1];
        $_SESSION['cart_count'] = array_sum($_SESSION['cart']);

        $this->assertEquals(4, $_SESSION['cart_count']);
    }

    public function test_fetch_cart_items_returns_joined_data(): void
    {
        $uid = self::$testUserId;
        self::$conn->query("INSERT INTO cart (user_id, product_id, quantity) VALUES ($uid, " . self::$productId . ", 1)");

        $items = fetch_cart_items(self::$conn, $uid);
        $this->assertIsArray($items);
        $this->assertGreaterThanOrEqual(1, count($items));

        $item = $items[0];
        $this->assertArrayHasKey('product_id', $item);
        $this->assertArrayHasKey('quantity', $item);
        $this->assertArrayHasKey('name', $item);
        $this->assertArrayHasKey('price', $item);
        $this->assertArrayHasKey('stock', $item);
    }

    public function test_product_stock_is_available(): void
    {
        $stmt = self::$conn->prepare("SELECT id, stock FROM products WHERE id = ? AND stock > 0");
        $stmt->bind_param('i', self::$productId);
        $stmt->execute();
        $product = $stmt->get_result()->fetch_assoc();

        $this->assertNotNull($product, 'Seed product should have stock');
        $this->assertGreaterThan(0, (int)$product['stock']);
    }
}
