<?php

namespace Ayurviro\Tests\Integration;

use PHPUnit\Framework\TestCase;

class AdminCrudTest extends TestCase
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

    // ========== CATEGORIES CRUD ==========

    public function test_category_create(): void
    {
        $stmt = self::$conn->prepare("INSERT INTO categories (name, slug, image_url) VALUES (?, ?, ?)");
        $name = 'Test Cat ' . time();
        $slug = 'test-cat-' . time();
        $img = 'https://example.com/cat.jpg';
        $stmt->bind_param('sss', $name, $slug, $img);
        $this->assertTrue($stmt->execute());
        $id = $stmt->insert_id;
        $this->assertGreaterThan(0, $id);

        // Verify
        $res = self::$conn->query("SELECT * FROM categories WHERE id = $id");
        $row = $res->fetch_assoc();
        $this->assertSame($name, $row['name']);
        $this->assertSame($slug, $row['slug']);

        // Cleanup
        self::$conn->query("DELETE FROM categories WHERE id = $id");
    }

    public function test_category_update(): void
    {
        // Create
        $ts = time();
        self::$conn->query("INSERT INTO categories (name, slug) VALUES ('orig-$ts', 'orig-$ts')");
        $id = self::$conn->insert_id;

        // Update
        $stmt = self::$conn->prepare("UPDATE categories SET name = ?, slug = ?, image_url = ? WHERE id = ?");
        $newName = 'updated-' . $ts;
        $newSlug = 'updated-' . $ts;
        $img = '';
        $stmt->bind_param('sssi', $newName, $newSlug, $img, $id);
        $this->assertTrue($stmt->execute());

        // Verify
        $res = self::$conn->query("SELECT name, slug FROM categories WHERE id = $id");
        $row = $res->fetch_assoc();
        $this->assertSame($newName, $row['name']);
        $this->assertSame($newSlug, $row['slug']);

        self::$conn->query("DELETE FROM categories WHERE id = $id");
    }

    public function test_category_delete(): void
    {
        $ts = time();
        self::$conn->query("INSERT INTO categories (name, slug) VALUES ('del-$ts', 'del-$ts')");
        $id = self::$conn->insert_id;

        $stmt = self::$conn->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->bind_param('i', $id);
        $this->assertTrue($stmt->execute());

        $res = self::$conn->query("SELECT id FROM categories WHERE id = $id");
        $this->assertNull($res->fetch_assoc());
    }

    public function test_category_rejects_duplicate_slug(): void
    {
        $ts = time();
        self::$conn->query("INSERT INTO categories (name, slug) VALUES ('first-$ts', 'dup-$ts')");

        $this->expectException(\mysqli_sql_exception::class);
        self::$conn->query("INSERT INTO categories (name, slug) VALUES ('second-$ts', 'dup-$ts')");

        self::$conn->query("DELETE FROM categories WHERE slug = 'dup-$ts'");
    }

    // ========== PRODUCTS CRUD ==========

    public function test_product_create(): void
    {
        $ts = time();
        $name = 'Test Product ' . $ts;
        $slug = 'test-product-' . $ts;
        $catId = 1;

        $stmt = self::$conn->prepare("INSERT INTO products (name, slug, category_id, description, price, compare_price, stock, image_url, is_bestseller) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $desc = 'Test description';
        $price = 29.99;
        $comp = 39.99;
        $stock = 10;
        $img = '';
        $bestseller = 1;
        $stmt->bind_param('ssisddiss', $name, $slug, $catId, $desc, $price, $comp, $stock, $img, $bestseller);
        $this->assertTrue($stmt->execute());
        $id = $stmt->insert_id;

        $res = self::$conn->query("SELECT * FROM products WHERE id = $id");
        $row = $res->fetch_assoc();
        $this->assertSame($name, $row['name']);
        $this->assertSame('29.99', $row['price']);
        $this->assertEquals(1, $row['is_bestseller']);

        self::$conn->query("DELETE FROM products WHERE id = $id");
    }

    public function test_product_update(): void
    {
        $ts = time();
        self::$conn->query("INSERT INTO products (name, slug, price) VALUES ('orig-$ts', 'orig-$ts', 10.00)");
        $id = self::$conn->insert_id;

        $stmt = self::$conn->prepare("UPDATE products SET name=?, slug=?, price=?, stock=? WHERE id=?");
        $newName = 'updated-' . $ts;
        $newSlug = 'updated-' . $ts;
        $price = 25.00;
        $stock = 50;
        $stmt->bind_param('ssdii', $newName, $newSlug, $price, $stock, $id);
        $this->assertTrue($stmt->execute());

        $row = self::$conn->query("SELECT name, price, stock FROM products WHERE id = $id")->fetch_assoc();
        $this->assertSame('updated-' . $ts, $row['name']);
        $this->assertSame('25.00', $row['price']);
        $this->assertSame(50, (int)$row['stock']);

        self::$conn->query("DELETE FROM products WHERE id = $id");
    }

    public function test_product_delete(): void
    {
        $ts = time();
        self::$conn->query("INSERT INTO products (name, slug, price) VALUES ('del-$ts', 'del-$ts', 5.00)");
        $id = self::$conn->insert_id;

        $stmt = self::$conn->prepare("DELETE FROM products WHERE id = ?");
        $stmt->bind_param('i', $id);
        $this->assertTrue($stmt->execute());

        $res = self::$conn->query("SELECT id FROM products WHERE id = $id");
        $this->assertNull($res->fetch_assoc());
    }

    public function test_product_slug_unique_enforced(): void
    {
        $ts = time();
        self::$conn->query("INSERT INTO products (name, slug, price) VALUES ('first-$ts', 'slug-dup-$ts', 5.00)");

        $this->expectException(\mysqli_sql_exception::class);
        self::$conn->query("INSERT INTO products (name, slug, price) VALUES ('second-$ts', 'slug-dup-$ts', 5.00)");

        self::$conn->query("DELETE FROM products WHERE slug LIKE 'slug-dup-%'");
    }

    // ========== DOCTORS CRUD ==========

    public function test_doctor_create(): void
    {
        $ts = time();
        $stmt = self::$conn->prepare("INSERT INTO doctors (name, slug, qualifications, specialty, experience_years, languages, fee, bio, image_url, available) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $name = 'Dr. Test ' . $ts;
        $slug = 'dr-test-' . $ts;
        $quals = 'BAMS';
        $spec = 'Internal Medicine';
        $exp = 10;
        $langs = 'English, Hindi';
        $fee = 500.00;
        $bio = 'Test bio';
        $img = '';
        $avail = 1;
        $stmt->bind_param('sssssisssi', $name, $slug, $quals, $spec, $exp, $langs, $fee, $bio, $img, $avail);
        $this->assertTrue($stmt->execute());
        $id = $stmt->insert_id;

        $row = self::$conn->query("SELECT * FROM doctors WHERE id = $id")->fetch_assoc();
        $this->assertSame($name, $row['name']);
        $this->assertSame('500.00', $row['fee']);
        $this->assertEquals(1, $row['available']);

        self::$conn->query("DELETE FROM doctors WHERE id = $id");
    }

    public function test_doctor_update(): void
    {
        $ts = time();
        self::$conn->query("INSERT INTO doctors (name, slug, fee) VALUES ('orig-$ts', 'orig-$ts', 100.00)");
        $id = self::$conn->insert_id;

        $stmt = self::$conn->prepare("UPDATE doctors SET name=?, slug=?, fee=?, available=?, experience_years=? WHERE id=?");
        $name = 'updated-' . $ts;
        $slug = 'updated-' . $ts;
        $fee = 750.00;
        $avail = 0;
        $exp = 15;
        $stmt->bind_param('ssdiii', $name, $slug, $fee, $avail, $exp, $id);
        $this->assertTrue($stmt->execute());

        $row = self::$conn->query("SELECT name, fee, available, experience_years FROM doctors WHERE id = $id")->fetch_assoc();
        $this->assertSame('updated-' . $ts, $row['name']);
        $this->assertSame('750.00', $row['fee']);
        $this->assertEquals(0, $row['available']);
        $this->assertEquals(15, $row['experience_years']);

        self::$conn->query("DELETE FROM doctors WHERE id = $id");
    }

    public function test_doctor_delete(): void
    {
        $ts = time();
        self::$conn->query("INSERT INTO doctors (name, slug) VALUES ('del-$ts', 'del-$ts')");
        $id = self::$conn->insert_id;

        $stmt = self::$conn->prepare("DELETE FROM doctors WHERE id = ?");
        $stmt->bind_param('i', $id);
        $this->assertTrue($stmt->execute());

        $this->assertNull(self::$conn->query("SELECT id FROM doctors WHERE id = $id")->fetch_assoc());
    }

    // ========== BLOG POSTS CRUD ==========

    public function test_blog_create(): void
    {
        $ts = time();
        $stmt = self::$conn->prepare("INSERT INTO blog_posts (title, slug, category, excerpt, content, image_url, author, published_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
        $title = 'Test Blog ' . $ts;
        $slug = 'test-blog-' . $ts;
        $cat = 'HERBAL GUIDE';
        $excerpt = 'Test excerpt';
        $content = 'Full content here';
        $img = '';
        $author = 'Tester';
        $stmt->bind_param('sssssss', $title, $slug, $cat, $excerpt, $content, $img, $author);
        $this->assertTrue($stmt->execute());
        $id = $stmt->insert_id;

        $row = self::$conn->query("SELECT title, slug, category FROM blog_posts WHERE id = $id")->fetch_assoc();
        $this->assertSame($title, $row['title']);
        $this->assertSame($cat, $row['category']);

        self::$conn->query("DELETE FROM blog_posts WHERE id = $id");
    }

    public function test_blog_update(): void
    {
        $ts = time();
        self::$conn->query("INSERT INTO blog_posts (title, slug, category) VALUES ('orig-$ts', 'orig-$ts', 'TEST')");
        $id = self::$conn->insert_id;

        $stmt = self::$conn->prepare("UPDATE blog_posts SET title=?, slug=?, category=?, excerpt=?, content=?, image_url=?, author=?, published_at=NOW() WHERE id=?");
        $title = 'updated-' . $ts;
        $slug = 'updated-' . $ts;
        $cat = 'DIET & LIFESTYLE';
        $excerpt = 'Updated excerpt';
        $content = 'Updated content';
        $img = '';
        $author = 'Editor';
        $stmt->bind_param('sssssssi', $title, $slug, $cat, $excerpt, $content, $img, $author, $id);
        $this->assertTrue($stmt->execute());

        $row = self::$conn->query("SELECT title, category, author FROM blog_posts WHERE id = $id")->fetch_assoc();
        $this->assertSame('updated-' . $ts, $row['title']);
        $this->assertSame('DIET & LIFESTYLE', $row['category']);
        $this->assertSame('Editor', $row['author']);

        self::$conn->query("DELETE FROM blog_posts WHERE id = $id");
    }

    public function test_blog_delete(): void
    {
        $ts = time();
        self::$conn->query("INSERT INTO blog_posts (title, slug) VALUES ('del-$ts', 'del-$ts')");
        $id = self::$conn->insert_id;

        $stmt = self::$conn->prepare("DELETE FROM blog_posts WHERE id = ?");
        $stmt->bind_param('i', $id);
        $this->assertTrue($stmt->execute());

        $this->assertNull(self::$conn->query("SELECT id FROM blog_posts WHERE id = $id")->fetch_assoc());
    }

    public function test_blog_slug_unique(): void
    {
        $ts = time();
        self::$conn->query("INSERT INTO blog_posts (title, slug) VALUES ('first-$ts', 'blog-dup-$ts')");

        $this->expectException(\mysqli_sql_exception::class);
        self::$conn->query("INSERT INTO blog_posts (title, slug) VALUES ('second-$ts', 'blog-dup-$ts')");

        self::$conn->query("DELETE FROM blog_posts WHERE slug = 'blog-dup-$ts'");
    }

    // ========== DOSHA QUESTIONS CRUD ==========

    public function test_dosha_question_create(): void
    {
        $ts = time();
        $stmt = self::$conn->prepare("INSERT INTO dosha_questions (question_text, category, weight, display_order, active) VALUES (?, ?, ?, ?, ?)");
        $text = 'Test question ' . $ts;
        $cat = 'vata';
        $weight = 2;
        $order = 99;
        $active = 1;
        $stmt->bind_param('ssiii', $text, $cat, $weight, $order, $active);
        $this->assertTrue($stmt->execute());
        $id = $stmt->insert_id;

        $row = self::$conn->query("SELECT * FROM dosha_questions WHERE id = $id")->fetch_assoc();
        $this->assertSame($text, $row['question_text']);
        $this->assertSame('vata', $row['category']);
        $this->assertEquals(1, $row['active']);

        self::$conn->query("DELETE FROM dosha_questions WHERE id = $id");
    }

    public function test_dosha_question_update(): void
    {
        $ts = time();
        self::$conn->query("INSERT INTO dosha_questions (question_text, category, weight, display_order, active) VALUES ('orig-$ts', 'pitta', 1, 1, 1)");
        $id = self::$conn->insert_id;

        $stmt = self::$conn->prepare("UPDATE dosha_questions SET question_text=?, category=?, weight=?, display_order=?, active=? WHERE id=?");
        $text = 'updated-' . $ts;
        $cat = 'kapha';
        $weight = 3;
        $order = 50;
        $active = 0;
        $stmt->bind_param('ssiiii', $text, $cat, $weight, $order, $active, $id);
        $this->assertTrue($stmt->execute());

        $row = self::$conn->query("SELECT * FROM dosha_questions WHERE id = $id")->fetch_assoc();
        $this->assertSame('updated-' . $ts, $row['question_text']);
        $this->assertSame('kapha', $row['category']);
        $this->assertEquals(0, $row['active']);
        $this->assertEquals(3, $row['weight']);

        self::$conn->query("DELETE FROM dosha_questions WHERE id = $id");
    }

    public function test_dosha_question_toggle_active(): void
    {
        self::$conn->query("INSERT INTO dosha_questions (question_text, category, weight, display_order, active) VALUES ('toggle-test', 'vata', 1, 1, 1)");
        $id = self::$conn->insert_id;

        self::$conn->query("UPDATE dosha_questions SET active = NOT active WHERE id = $id");
        $row = self::$conn->query("SELECT active FROM dosha_questions WHERE id = $id")->fetch_assoc();
        $this->assertEquals(0, $row['active']);

        // Toggle back
        self::$conn->query("UPDATE dosha_questions SET active = NOT active WHERE id = $id");
        $row = self::$conn->query("SELECT active FROM dosha_questions WHERE id = $id")->fetch_assoc();
        $this->assertEquals(1, $row['active']);

        self::$conn->query("DELETE FROM dosha_questions WHERE id = $id");
    }

    public function test_dosha_question_delete(): void
    {
        self::$conn->query("INSERT INTO dosha_questions (question_text, category, weight, display_order) VALUES ('del-test', 'vata', 1, 1)");
        $id = self::$conn->insert_id;

        $stmt = self::$conn->prepare("DELETE FROM dosha_questions WHERE id = ?");
        $stmt->bind_param('i', $id);
        $this->assertTrue($stmt->execute());

        $this->assertNull(self::$conn->query("SELECT id FROM dosha_questions WHERE id = $id")->fetch_assoc());
    }

    public function test_dosha_question_validates_category(): void
    {
        $this->expectException(\mysqli_sql_exception::class);
        self::$conn->query("INSERT INTO dosha_questions (question_text, category) VALUES ('bad-cat', 'invalid_category')");
    }

    // ========== PRESCRIPTIONS CRUD ==========

    public function test_prescription_create(): void
    {
        $ts = time();
        $email = "rx_test_{$ts}@example.com";
        self::$conn->query("INSERT INTO users (full_name, email, password, role) VALUES ('RxPatient', '$email', 'hash', 'customer')");
        $uid = self::$conn->insert_id;
        self::$conn->query("INSERT INTO doctors (name, slug) VALUES ('RxDoc', 'rx-doc-$ts')");
        $did = self::$conn->insert_id;
        self::$conn->query("INSERT INTO appointments (user_id, doctor_id, appointment_date, appointment_time) VALUES ($uid, $did, CURDATE(), CURTIME())");
        $aid = self::$conn->insert_id;
        self::$conn->query("INSERT INTO consultations (user_id, doctor_id, type, status) VALUES ($uid, $did, 'video', 'completed')");
        $cid = self::$conn->insert_id;

        $stmt = self::$conn->prepare("INSERT INTO prescriptions (consultation_id, doctor_id, user_id, diagnosis, medicines, advice, follow_up_date) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $diagnosis = 'Test diagnosis';
        $medicines = 'Medicine A, Medicine B';
        $advice = 'Rest well';
        $followup = date('Y-m-d', strtotime('+1 month'));
        $stmt->bind_param('iiissss', $cid, $did, $uid, $diagnosis, $medicines, $advice, $followup);
        $this->assertTrue($stmt->execute());
        $pid = $stmt->insert_id;

        $row = self::$conn->query("SELECT * FROM prescriptions WHERE id = $pid")->fetch_assoc();
        $this->assertSame($diagnosis, $row['diagnosis']);
        $this->assertSame($medicines, $row['medicines']);
        $this->assertSame($followup, $row['follow_up_date']);

        self::$conn->query("DELETE FROM prescriptions WHERE id = $pid");
        self::$conn->query("DELETE FROM consultations WHERE id = $cid");
        self::$conn->query("DELETE FROM appointments WHERE id = $aid");
        self::$conn->query("DELETE FROM doctors WHERE id = $did");
        self::$conn->query("DELETE FROM users WHERE id = $uid");
    }

    public function test_prescription_update(): void
    {
        $ts = time();
        $email = "rx_upd_{$ts}@example.com";
        self::$conn->query("INSERT INTO users (full_name, email, password, role) VALUES ('UpdPatient', '$email', 'hash', 'customer')");
        $uid = self::$conn->insert_id;
        self::$conn->query("INSERT INTO doctors (name, slug) VALUES ('UpdDoc', 'upd-doc-$ts')");
        $did = self::$conn->insert_id;
        self::$conn->query("INSERT INTO consultations (user_id, doctor_id, type, status) VALUES ($uid, $did, 'video', 'completed')");
        $cid = self::$conn->insert_id;
        self::$conn->query("INSERT INTO prescriptions (consultation_id, doctor_id, user_id, diagnosis, medicines, advice) VALUES ($cid, $did, $uid, 'orig', 'orig', 'orig')");
        $pid = self::$conn->insert_id;

        $stmt = self::$conn->prepare("UPDATE prescriptions SET diagnosis=?, medicines=?, advice=?, follow_up_date=? WHERE id=?");
        $diag = 'Updated diagnosis';
        $meds = 'New Medicine';
        $advice = 'Updated advice';
        $fu = date('Y-m-d', strtotime('+2 weeks'));
        $stmt->bind_param('ssssi', $diag, $meds, $advice, $fu, $pid);
        $this->assertTrue($stmt->execute());

        $row = self::$conn->query("SELECT * FROM prescriptions WHERE id = $pid")->fetch_assoc();
        $this->assertSame('Updated diagnosis', $row['diagnosis']);
        $this->assertSame('New Medicine', $row['medicines']);

        self::$conn->query("DELETE FROM prescriptions WHERE id = $pid");
        self::$conn->query("DELETE FROM consultations WHERE id = $cid");
        self::$conn->query("DELETE FROM doctors WHERE id = $did");
        self::$conn->query("DELETE FROM users WHERE id = $uid");
    }

    public function test_prescription_delete(): void
    {
        $ts = time();
        $email = "rx_del_{$ts}@example.com";
        self::$conn->query("INSERT INTO users (full_name, email, password, role) VALUES ('DelPatient', '$email', 'hash', 'customer')");
        $uid = self::$conn->insert_id;
        self::$conn->query("INSERT INTO doctors (name, slug) VALUES ('DelDoc', 'del-doc-$ts')");
        $did = self::$conn->insert_id;
        self::$conn->query("INSERT INTO consultations (user_id, doctor_id, type, status) VALUES ($uid, $did, 'audio', 'completed')");
        $cid = self::$conn->insert_id;
        self::$conn->query("INSERT INTO prescriptions (consultation_id, doctor_id, user_id, diagnosis) VALUES ($cid, $did, $uid, 'to-delete')");
        $pid = self::$conn->insert_id;

        $stmt = self::$conn->prepare("DELETE FROM prescriptions WHERE id = ?");
        $stmt->bind_param('i', $pid);
        $this->assertTrue($stmt->execute());

        $this->assertNull(self::$conn->query("SELECT id FROM prescriptions WHERE id = $pid")->fetch_assoc());

        self::$conn->query("DELETE FROM consultations WHERE id = $cid");
        self::$conn->query("DELETE FROM doctors WHERE id = $did");
        self::$conn->query("DELETE FROM users WHERE id = $uid");
    }

    // ========== SETTINGS UPSERT ==========

    public function test_settings_upsert_insert(): void
    {
        $key = 'test_key_' . time();
        $value = 'test_value';

        $stmt = self::$conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        $stmt->bind_param('ss', $key, $value);
        $this->assertTrue($stmt->execute());

        $row = self::$conn->query("SELECT setting_value FROM settings WHERE setting_key = '$key'")->fetch_assoc();
        $this->assertSame($value, $row['setting_value']);

        self::$conn->query("DELETE FROM settings WHERE setting_key = '$key'");
    }

    public function test_settings_upsert_update(): void
    {
        $key = 'test_update_' . time();
        self::$conn->query("INSERT INTO settings (setting_key, setting_value) VALUES ('$key', 'original')");

        $stmt = self::$conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        $updated = 'updated_value';
        $stmt->bind_param('ss', $key, $updated);
        $this->assertTrue($stmt->execute());

        $row = self::$conn->query("SELECT setting_value FROM settings WHERE setting_key = '$key'")->fetch_assoc();
        $this->assertSame('updated_value', $row['setting_value']);

        self::$conn->query("DELETE FROM settings WHERE setting_key = '$key'");
    }

    // ========== DOSHA ASSESSMENT FLOW ==========

    public function test_dosha_assessment_create(): void
    {
        $ts = time();
        $email = "dosha_{$ts}@example.com";
        self::$conn->query("INSERT INTO users (full_name, email, password) VALUES ('DoshaUser', '$email', 'hash')");
        $uid = self::$conn->insert_id;

        $stmt = self::$conn->prepare("INSERT INTO dosha_assessments (user_id, vata_score, pitta_score, kapha_score, dominant_dosha, recommendations) VALUES (?, ?, ?, ?, ?, ?)");
        $vata = 10;
        $pitta = 20;
        $kapha = 30;
        $dominant = 'kapha';
        $recs = 'Eat warm foods';
        $stmt->bind_param('iiiiss', $uid, $vata, $pitta, $kapha, $dominant, $recs);
        $this->assertTrue($stmt->execute());
        $aid = $stmt->insert_id;

        $row = self::$conn->query("SELECT * FROM dosha_assessments WHERE id = $aid")->fetch_assoc();
        $this->assertSame('kapha', $row['dominant_dosha']);
        $this->assertEquals(30, $row['kapha_score']);

        self::$conn->query("DELETE FROM dosha_assessments WHERE id = $aid");
        self::$conn->query("DELETE FROM users WHERE id = $uid");
    }

    public function test_dosha_assessment_delete_cascades_responses(): void
    {
        $email = "dosha_cascade_" . time() . "@example.com";
        self::$conn->query("INSERT INTO users (full_name, email, password) VALUES ('CascadeUser', '$email', 'hash')");
        $uid = self::$conn->insert_id;

        self::$conn->query("INSERT INTO dosha_assessments (user_id, vata_score) VALUES ($uid, 5)");
        $aid = self::$conn->insert_id;

        // Get a dosha question
        $qRes = self::$conn->query("SELECT id FROM dosha_questions LIMIT 1");
        $qid = (int)$qRes->fetch_assoc()['id'];

        // Insert response
        self::$conn->query("INSERT INTO dosha_responses (assessment_id, question_id, answer_value) VALUES ($aid, $qid, 3)");

        // Delete assessment (should cascade delete response)
        self::$conn->query("DELETE FROM dosha_assessments WHERE id = $aid");

        $resp = self::$conn->query("SELECT id FROM dosha_responses WHERE assessment_id = $aid");
        $this->assertNull($resp->fetch_assoc());

        self::$conn->query("DELETE FROM users WHERE id = $uid");
    }
}
