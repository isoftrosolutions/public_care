<?php
require_once __DIR__ . '/config/database.php';

header('Content-Type: application/xml; charset=utf-8');

$db = getDB();

$pages = [
    ['loc' => '', 'priority' => '1.0', 'changefreq' => 'weekly'],
    ['loc' => 'shop.php', 'priority' => '0.9', 'changefreq' => 'daily'],
    ['loc' => 'lab-tests.php', 'priority' => '0.8', 'changefreq' => 'weekly'],
    ['loc' => 'doctor-listing.php', 'priority' => '0.8', 'changefreq' => 'weekly'],
    ['loc' => 'ai-assistant.php', 'priority' => '0.7', 'changefreq' => 'weekly'],
    ['loc' => 'login.php', 'priority' => '0.3', 'changefreq' => 'monthly'],
    ['loc' => 'register.php', 'priority' => '0.3', 'changefreq' => 'monthly'],
    ['loc' => 'about-us.php', 'priority' => '0.5', 'changefreq' => 'monthly'],
    ['loc' => 'contact-us.php', 'priority' => '0.5', 'changefreq' => 'monthly'],
    ['loc' => 'wellness-blog.php', 'priority' => '0.6', 'changefreq' => 'weekly'],
    ['loc' => 'upload-report.php', 'priority' => '0.6', 'changefreq' => 'monthly'],
    ['loc' => 'privacy-policy.php', 'priority' => '0.3', 'changefreq' => 'yearly'],
    ['loc' => 'terms-of-service.php', 'priority' => '0.3', 'changefreq' => 'yearly'],
];

// Get product URLs
$products = $db->query("SELECT id, created_at FROM products WHERE stock > 0 ORDER BY id");
$product_pages = [];
if ($products) {
    while ($p = $products->fetch_assoc()) {
        $pages[] = ['loc' => 'product-details.php?id=' . $p['id'], 'priority' => '0.7', 'changefreq' => 'weekly'];
    }
}

// Get doctor URLs
$doctors = $db->query("SELECT id FROM doctors WHERE available = 1 AND reviews_count > 0");
if ($doctors) {
    while ($d = $doctors->fetch_assoc()) {
        $pages[] = ['loc' => 'doctor-profile.php?id=' . $d['id'], 'priority' => '0.7', 'changefreq' => 'weekly'];
    }
}

// Get blog post URLs
$posts = $db->query("SELECT slug FROM blog_posts WHERE status = 'published' OR status IS NULL ORDER BY published_at DESC");
if ($posts) {
    while ($b = $posts->fetch_assoc()) {
        $pages[] = ['loc' => 'wellness-blog.php?slug=' . $b['slug'], 'priority' => '0.6', 'changefreq' => 'monthly'];
    }
}

echo '<?xml version="1.0" encoding="UTF-8"?>';
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

$base = 'https://ayurviora.com';
foreach ($pages as $page) {
    $url = $base . '/' . ltrim($page['loc'], '/');
    echo '<url>';
    echo '<loc>' . htmlspecialchars($url) . '</loc>';
    echo '<priority>' . $page['priority'] . '</priority>';
    echo '<changefreq>' . $page['changefreq'] . '</changefreq>';
    echo '</url>';
}

echo '</urlset>';
