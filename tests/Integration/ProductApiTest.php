<?php

namespace Ayurviro\Tests\Integration;

use PHPUnit\Framework\TestCase;

class ProductApiTest extends TestCase
{
    private static ?\mysqli $conn = null;

    public static function setUpBeforeClass(): void
    {
        try {
            require_once __DIR__ . '/../../config/database.php';
            self::$conn = getDB();
        } catch (\Throwable $e) {
            self::markTestSkipped('MySQL not available: ' . $e->getMessage());
        }
    }

    public function test_product_has_required_columns(): void
    {
        $result = self::$conn->query("SELECT * FROM products LIMIT 1");
        $row = $result->fetch_assoc();

        $expected = ['id', 'category_id', 'name', 'slug', 'description', 'price', 'compare_price', 'image_url', 'stock', 'is_bestseller', 'rating', 'reviews_count', 'created_at'];
        foreach ($expected as $col) {
            $this->assertArrayHasKey($col, $row, "Missing column: $col");
        }
    }

    public function test_product_price_is_positive(): void
    {
        $result = self::$conn->query("SELECT MIN(price) as min_price FROM products");
        $row = $result->fetch_assoc();
        $this->assertGreaterThan(0, (float)$row['min_price']);
    }

    public function test_product_slugs_are_unique(): void
    {
        $result = self::$conn->query("SELECT slug, COUNT(*) as c FROM products GROUP BY slug HAVING c > 1");
        $this->assertEquals(0, $result->num_rows);
    }

    public function test_category_filter_returns_products(): void
    {
        $stmt = self::$conn->prepare("SELECT c.id FROM categories c LIMIT 1");
        $stmt->execute();
        $cat = $stmt->get_result()->fetch_assoc();
        $categoryId = (int)$cat['id'];

        $stmt = self::$conn->prepare("SELECT COUNT(*) as c FROM products WHERE category_id = ?");
        $stmt->bind_param('i', $categoryId);
        $stmt->execute();
        $count = (int)$stmt->get_result()->fetch_assoc()['c'];

        $this->assertGreaterThan(0, $count);
    }

    public function test_sort_by_price_ascending(): void
    {
        $result = self::$conn->query("SELECT price FROM products ORDER BY price ASC LIMIT 2");
        $prices = [];
        while ($row = $result->fetch_assoc()) {
            $prices[] = (float)$row['price'];
        }

        $this->assertCount(2, $prices);
        $this->assertLessThanOrEqual($prices[1], $prices[0]);
    }

    public function test_sort_by_price_descending(): void
    {
        $result = self::$conn->query("SELECT price FROM products ORDER BY price DESC LIMIT 2");
        $prices = [];
        while ($row = $result->fetch_assoc()) {
            $prices[] = (float)$row['price'];
        }

        $this->assertCount(2, $prices);
        $this->assertGreaterThanOrEqual($prices[1], $prices[0]);
    }

    public function test_sort_by_rating_descending(): void
    {
        $result = self::$conn->query("SELECT rating FROM products ORDER BY rating DESC LIMIT 2");
        $ratings = [];
        while ($row = $result->fetch_assoc()) {
            $ratings[] = (float)$row['rating'];
        }

        $this->assertCount(2, $ratings);
        $this->assertGreaterThanOrEqual($ratings[1], $ratings[0]);
    }

    public function test_search_finds_matching_products(): void
    {
        $result = self::$conn->query("SELECT * FROM products WHERE name LIKE '%Ashwagandha%'");
        $products = $result->fetch_all(MYSQLI_ASSOC);
        $this->assertGreaterThan(0, count($products));
    }

    public function test_search_returns_empty_for_nonsense(): void
    {
        $result = self::$conn->query("SELECT * FROM products WHERE name LIKE '%xyznonexistent999%'");
        $this->assertEquals(0, $result->num_rows);
    }

    public function test_pagination_returns_correct_count(): void
    {
        $perPage = 2;
        $page = 1;
        $offset = ($page - 1) * $perPage;

        $result = self::$conn->query("SELECT id FROM products LIMIT $perPage OFFSET $offset");
        $products = $result->fetch_all(MYSQLI_ASSOC);
        $this->assertCount($perPage, $products);
    }

    public function test_category_slugs_are_unique(): void
    {
        $result = self::$conn->query("SELECT slug, COUNT(*) as c FROM categories GROUP BY slug HAVING c > 1");
        $this->assertEquals(0, $result->num_rows);
    }

    public function test_no_orphan_products(): void
    {
        $result = self::$conn->query("SELECT p.id FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE c.id IS NULL AND p.category_id IS NOT NULL");
        $this->assertEquals(0, $result->num_rows);
    }

    public function test_bestseller_products_exist(): void
    {
        $result = self::$conn->query("SELECT COUNT(*) as c FROM products WHERE is_bestseller = TRUE");
        $count = (int)$result->fetch_assoc()['c'];
        $this->assertGreaterThan(0, $count);
    }

    public function test_compare_price_is_optional(): void
    {
        $result = self::$conn->query("SELECT compare_price FROM products WHERE compare_price IS NOT NULL LIMIT 1");
        $this->assertEquals(1, $result->num_rows);
    }
}
