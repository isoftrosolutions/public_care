<?php
require_once __DIR__ . '/includes/config.php';
$site_title = 'Shop';
require_once __DIR__ . '/includes/header.php';
?>

<?php
$conn = getDB();
$category_filter = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'featured';
$min_price = isset($_GET['min_price']) ? (float)$_GET['min_price'] : 0;
$max_price = isset($_GET['max_price']) ? (float)$_GET['max_price'] : 0;

$where = [];
$params = [];

if ($category_filter > 0) {
    $where[] = "p.category_id = " . (int)$category_filter;
}
if ($search_term !== '') {
    $safe_search = $conn->real_escape_string($search_term);
    $where[] = "(p.name LIKE '%$safe_search%' OR p.description LIKE '%$safe_search%')";
}
if ($min_price > 0) {
    $where[] = "p.price >= " . (float)$min_price;
}
if ($max_price > 0) {
    $where[] = "p.price <= " . (float)$max_price;
}

$where_clause = '';
if (count($where) > 0) {
    $where_clause = 'WHERE ' . implode(' AND ', $where);
}

$order = 'ORDER BY p.created_at DESC';
if ($sort === 'price_asc') $order = 'ORDER BY p.price ASC';
elseif ($sort === 'price_desc') $order = 'ORDER BY p.price DESC';
elseif ($sort === 'rating') $order = 'ORDER BY p.rating DESC';
elseif ($sort === 'name') $order = 'ORDER BY p.name ASC';

$count_query = "SELECT COUNT(*) as total FROM products p $where_clause";
$count_result = $conn->query($count_query);
$total_products = $count_result ? $count_result->fetch_assoc()['total'] : 0;

$per_page = 12;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $per_page;
$total_pages = ceil($total_products / $per_page);

$products_query = "SELECT p.*, c.name as category_name
                   FROM products p
                   LEFT JOIN categories c ON p.category_id = c.id
                   $where_clause
                   $order
                   LIMIT $per_page OFFSET $offset";
$products_result = $conn->query($products_query);
$products = [];
if ($products_result && $products_result->num_rows > 0) {
    while ($row = $products_result->fetch_assoc()) {
        $products[] = $row;
    }
}

$categories_query = "SELECT * FROM categories ORDER BY name ASC";
$categories_result = $conn->query($categories_query);
$categories = [];
if ($categories_result && $categories_result->num_rows > 0) {
    while ($row = $categories_result->fetch_assoc()) {
        $categories[] = $row;
    }
}

$any_filter_active = $category_filter > 0 || $search_term !== '' || $min_price > 0 || $max_price > 0;

$max_price_in_db = 5000;
$price_query = "SELECT MAX(price) as max_price FROM products";
$price_result = $conn->query($price_query);
if ($price_result && $price_result->num_rows > 0) {
    $max_price_in_db = (int)ceil($price_result->fetch_assoc()['max_price']);
}
$min_price = $min_price ?: 0;
$max_price = $max_price ?: $max_price_in_db;
?>

<style>
.bg-pattern { background-image: radial-gradient(#ffffff20 1px, transparent 0); background-size: 24px 24px; }
@keyframes fadeSlideUp {
  from { opacity: 0; transform: translateY(24px); }
  to { opacity: 1; transform: translateY(0); }
}
.product-card-overlay { pointer-events: none; }
.product-card-overlay a,
.product-card-overlay button { pointer-events: auto; }
input[type="range"]::-webkit-slider-thumb {
  -webkit-appearance: none; appearance: none;
  width: 18px; height: 18px;
  border-radius: 50%;
  background: #005221;
  cursor: pointer;
  box-shadow: 0 2px 6px rgba(0, 82, 33, 0.3);
}
input[type="range"]::-moz-range-thumb {
  width: 18px; height: 18px;
  border-radius: 50%;
  background: #005221;
  cursor: pointer;
  border: none;
  box-shadow: 0 2px 6px rgba(0, 82, 33, 0.3);
}
input[type="range"] { -webkit-appearance: none; appearance: none; }
</style>

<!-- Hero Banner Section -->
<section class="relative overflow-hidden bg-primary py-16 md:py-24">
<div class="absolute inset-0 opacity-10 bg-pattern"></div>
<div class="max-w-container-max mx-auto px-base md:px-margin-desktop relative z-10 flex flex-col md:flex-row items-center gap-12">
<div class="flex-1 text-center md:text-left">
<p class="text-primary-fixed font-label-lg text-label-lg mb-4 tracking-widest uppercase">Natural • Balanced • Healthy</p>
<h1 class="font-display-lg text-display-lg text-on-primary-container mb-6">Explore Natural <br/>Wellness Categories</h1>
<p class="text-on-primary-container/80 font-body-lg text-body-lg max-w-xl mx-auto md:mx-0">
Discover authentic Ayurvedic products, expert consultations, herbal remedies, and wellness solutions designed for your holistic growth.
</p>
</div>
<div class="flex-1 w-full max-w-md">
<div class="relative group">
<div class="absolute -inset-4 bg-primary-fixed/20 blur-3xl rounded-full"></div>
<img class="rounded-2xl shadow-2xl relative z-10 transform group-hover:scale-[1.02] transition-transform duration-500 object-cover aspect-square" alt="Ayurvedic herbs and remedies" src="https://lh3.googleusercontent.com/aida-public/AB6AXuB11f5yaVl2I07DrrZo-AJEVtqrdAwiXF8a1NiYwdtAqdy0dnUz6p6Q0l81W5v893n8zpK3U1fS1Q7ZuSmQ44CkU_Xr0KqQ_PQjoxStMYqZtXXWNAN9B2eyNRCd8anAF5Nx5nnXExyy7dsu7ip4FAfQCczMB6lXSEoVm894cxpCgz4p8Egar4JFGnkhOghx2YHd1LjfHZWl2uck-4X-CMG_nFJVWTYFnm_0AoLYgKmt0k0qv5_g9gv1fcD4X1y5DP-4CUARiXMPsHc"/>
</div>
</div>
</div>
</section>

<!-- Breadcrumb -->
<div class="max-w-container-max mx-auto px-base md:px-margin-desktop pt-8">
<nav class="flex items-center gap-2 text-label-sm text-outline mb-2">
<a href="<?= BASE_URL ?>/index.php" class="hover:text-primary transition-colors">Home</a>
<span class="material-symbols-outlined text-[16px]">chevron_right</span>
<span class="text-primary font-medium">Shop</span>
</nav>
</div>

<!-- Main Content: Filters + Products -->
<div class="max-w-container-max mx-auto px-base md:px-margin-desktop py-8 lg:py-12">
<div class="flex flex-col lg:flex-row gap-10 relative">
<!-- Mobile Overlay -->
<div id="mobile-filter-overlay" class="fixed inset-0 bg-black/40 z-40 hidden opacity-0 transition-opacity duration-300 lg:hidden"></div>

<!-- Sidebar Filter -->
<aside id="filter-sidebar" class="w-full lg:w-72 flex-shrink-0 lg:sticky lg:top-28 lg:self-start hidden lg:block">
<div class="bg-surface-container-lowest border border-outline-variant/30 rounded-2xl p-6 lg:p-8 space-y-8">
<div class="flex items-center justify-between lg:hidden">
<h2 class="font-headline-md text-headline-md text-primary">Filters</h2>
<button id="mobile-filter-close" class="w-10 h-10 rounded-full flex items-center justify-center hover:bg-surface-container-low transition-colors">
<span class="material-symbols-outlined">close</span>
</button>
</div>

<form method="GET" action="<?= BASE_URL ?>/shop.php" id="filter-form">
<div class="space-y-3">
<label class="font-label-md text-primary block">Search</label>
<div class="relative">
<input class="w-full pl-10 pr-4 py-3 rounded-xl border border-outline-variant focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all bg-surface text-on-surface placeholder:text-outline" placeholder="Search products..." type="text" name="search" value="<?= htmlspecialchars($search_term) ?>"/>
<span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-[20px]">search</span>
</div>
</div>

<div class="space-y-4">
<h3 class="font-label-md text-primary uppercase tracking-wider">Category</h3>
<div class="space-y-2">
<label class="flex items-center gap-3 cursor-pointer group px-3 py-2 rounded-lg hover:bg-surface-container-low transition-colors <?= $category_filter === 0 ? 'bg-primary/5' : '' ?>">
<input type="radio" name="category" value="0" class="text-primary focus:ring-primary" <?= $category_filter === 0 ? 'checked' : '' ?>>
<span class="text-body-md <?= $category_filter === 0 ? 'text-primary font-semibold' : 'text-on-surface-variant group-hover:text-primary transition-colors' ?>">All Products</span>
</label>
<?php foreach ($categories as $cat): ?>
<label class="flex items-center gap-3 cursor-pointer group px-3 py-2 rounded-lg hover:bg-surface-container-low transition-colors <?= $category_filter === (int)$cat['id'] ? 'bg-primary/5' : '' ?>">
<input type="radio" name="category" value="<?= $cat['id'] ?>" class="text-primary focus:ring-primary" <?= $category_filter === (int)$cat['id'] ? 'checked' : '' ?>>
<span class="text-body-md <?= $category_filter === (int)$cat['id'] ? 'text-primary font-semibold' : 'text-on-surface-variant group-hover:text-primary transition-colors' ?>"><?= htmlspecialchars($cat['name']) ?></span>
</label>
<?php endforeach; ?>
</div>
</div>

<div class="space-y-4">
<h3 class="font-label-md text-primary uppercase tracking-wider">Price Range</h3>
<div class="px-1">
<input type="range" id="price-slider" name="max_price" min="0" max="<?= $max_price_in_db ?>" value="<?= (int)$max_price ?>" class="w-full h-1.5 bg-surface-container-highest rounded-lg appearance-none cursor-pointer accent-primary">
<div class="flex justify-between items-center mt-3">
<span class="text-label-sm text-outline font-medium">₹0</span>
<span class="text-label-sm text-primary font-semibold" id="price-label">Up to ₹<?= number_format((int)$max_price) ?></span>
</div>
</div>
</div>

<input type="hidden" name="sort" id="sort-input" value="<?= htmlspecialchars($sort) ?>">
<input type="hidden" name="page" id="page-input" value="1">
<input type="hidden" name="min_price" value="0">

<div class="space-y-3 pt-2">
<button type="submit" class="w-full bg-primary text-on-primary py-3 rounded-xl font-label-md hover:bg-primary-container transition-all active:scale-[0.98]">
Apply Filters
</button>
<?php if ($any_filter_active): ?>
<a href="<?= BASE_URL ?>/shop.php" class="block w-full text-center py-3 rounded-xl border border-outline-variant text-primary font-label-md hover:bg-surface-container-low transition-colors">
Clear All
</a>
<?php endif; ?>
</div>
</form>
</div>
</aside>

<!-- Main Product Area -->
<div class="flex-1 min-w-0">
<div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-6">
<div>
<h1 class="font-headline-lg text-headline-lg text-primary">Shop All</h1>
<p class="text-body-md text-on-surface-variant mt-1">Explore our range of authentic Ayurvedic formulations</p>
</div>
<button id="mobile-filter-toggle" class="lg:hidden flex items-center gap-2 px-5 py-2.5 border border-outline-variant rounded-full text-label-md text-primary hover:bg-surface-container-low transition-colors">
<span class="material-symbols-outlined text-[20px]">tune</span>
Filters
<?php if ($any_filter_active): ?>
<span class="w-2 h-2 rounded-full bg-secondary"></span>
<?php endif; ?>
</button>
</div>

<!-- Active Filters Chips -->
<?php if ($any_filter_active): ?>
<div class="flex flex-wrap items-center gap-2 mb-6">
<span class="text-label-sm text-outline">Active filters:</span>
<?php if ($search_term !== ''): ?>
<span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-primary/5 text-primary rounded-full text-label-sm">
Search: "<?= htmlspecialchars($search_term) ?>"
<a href="<?= str_replace(['?search=' . urlencode($search_term), '&search=' . urlencode($search_term)], '', $_SERVER['REQUEST_URI']); ?>" class="hover:text-error transition-colors">
<span class="material-symbols-outlined text-[16px]">close</span>
</a>
</span>
<?php endif; ?>
<?php if ($category_filter > 0): ?>
<?php $cat_name = ''; foreach ($categories as $cat) { if ($cat['id'] == $category_filter) { $cat_name = $cat['name']; break; } } ?>
<span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-primary/5 text-primary rounded-full text-label-sm">
<?= htmlspecialchars($cat_name) ?>
<a href="<?= str_replace(['?category=' . $category_filter, '&category=' . $category_filter], '', $_SERVER['REQUEST_URI']); ?>" class="hover:text-error transition-colors">
<span class="material-symbols-outlined text-[16px]">close</span>
</a>
</span>
<?php endif; ?>
<?php if ($min_price > 0 || $max_price < $max_price_in_db): ?>
<span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-primary/5 text-primary rounded-full text-label-sm">
₹<?= number_format((int)$min_price) ?> – ₹<?= number_format((int)$max_price) ?>
<a href="<?= BASE_URL ?>/shop.php" class="hover:text-error transition-colors">
<span class="material-symbols-outlined text-[16px]">close</span>
</a>
</span>
<?php endif; ?>
<a href="<?= BASE_URL ?>/shop.php" class="text-label-sm text-outline hover:text-primary underline transition-colors ml-1">Clear all</a>
</div>
<?php endif; ?>

<!-- Toolbar -->
<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
<p class="text-body-md text-on-surface-variant">
Showing <span class="font-semibold text-on-surface"><?= count($products) ?></span> of <span class="font-semibold text-on-surface"><?= $total_products ?></span> result<?= $total_products !== 1 ? 's' : '' ?>
<?php if ($search_term !== ''): ?> for "<span class="text-primary font-medium"><?= htmlspecialchars($search_term) ?></span>"<?php endif; ?>
</p>
<div class="flex items-center gap-3 w-full sm:w-auto">
<span class="text-label-sm text-outline whitespace-nowrap">Sort by:</span>
<select class="flex-1 sm:flex-none border border-outline-variant rounded-xl bg-surface text-on-surface px-4 py-2.5 font-label-md focus:ring-1 focus:ring-primary focus:border-primary outline-none cursor-pointer" id="sort-select">
<option value="featured" <?= $sort === 'featured' ? 'selected' : '' ?>>Featured</option>
<option value="name" <?= $sort === 'name' ? 'selected' : '' ?>>Name A–Z</option>
<option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
<option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
<option value="rating" <?= $sort === 'rating' ? 'selected' : '' ?>>Best Rating</option>
</select>
</div>
</div>

<!-- Product Grid -->
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6" id="product-grid">
<?php if (count($products) > 0): ?>
<?php foreach ($products as $index => $product): ?>
<div class="product-card group bg-surface-container-lowest rounded-2xl border border-outline-variant/20 overflow-hidden hover:shadow-xl hover:border-primary/20 transition-all duration-500 flex flex-col" style="animation: fadeSlideUp 0.5s ease-out <?= $index * 0.05 ?>s both;">
<div class="relative aspect-square bg-surface-container-low overflow-hidden">
<a href="<?= BASE_URL ?>/product-details.php?id=<?= $product['id'] ?>">
<img class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700" src="<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>"/>
</a>

<?php if ($product['is_bestseller']): ?>
<div class="absolute top-3 left-3">
<span class="bg-secondary text-on-secondary px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider shadow-sm">Best Seller</span>
</div>
<?php endif; ?>

<?php if ($product['compare_price'] > 0): ?>
<?php $discount = round((1 - $product['price'] / $product['compare_price']) * 100); ?>
<div class="absolute top-3 <?= $product['is_bestseller'] ? 'right-3' : 'left-3' ?>">
<span class="bg-error text-on-error px-2.5 py-1 rounded-full text-[10px] font-bold shadow-sm">-<?= $discount ?>%</span>
</div>
<?php endif; ?>

<?php if ((int)$product['stock'] <= 5 && (int)$product['stock'] > 0): ?>
<div class="absolute bottom-3 left-3">
<span class="bg-surface/80 backdrop-blur text-on-surface px-2.5 py-1 rounded-full text-[10px] font-medium">Only <?= (int)$product['stock'] ?> left</span>
</div>
<?php elseif ((int)$product['stock'] === 0): ?>
<div class="absolute inset-0 bg-surface/60 backdrop-blur-[2px] flex items-center justify-center">
<span class="bg-surface text-on-surface px-4 py-2 rounded-full text-label-sm font-bold shadow">Out of Stock</span>
</div>
<?php endif; ?>

<div class="product-card-overlay absolute inset-0 bg-primary/5 backdrop-blur-[1px] opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center gap-3">
<a href="<?= BASE_URL ?>/product-details.php?id=<?= $product['id'] ?>" class="bg-white text-primary px-5 py-2.5 rounded-xl font-label-md shadow-lg hover:bg-primary hover:text-white transition-all transform -translate-y-2 group-hover:translate-y-0 duration-300">
Quick View
</a>
<?php if ((int)$product['stock'] > 0): ?>
<button class="bg-primary text-on-primary p-2.5 rounded-xl shadow-lg hover:bg-primary-container transition-all transform translate-y-2 group-hover:translate-y-0 duration-300 add-to-cart" data-product-id="<?= $product['id'] ?>">
<span class="material-symbols-outlined">shopping_bag</span>
</button>
<?php endif; ?>
</div>
</div>

<div class="p-5 flex-1 flex flex-col">
<span class="text-[11px] font-medium text-tertiary uppercase tracking-widest mb-1.5"><?= htmlspecialchars($product['category_name'] ?? 'General') ?></span>
<a href="<?= BASE_URL ?>/product-details.php?id=<?= $product['id'] ?>">
<h3 class="font-headline-md text-headline-md text-primary mb-1.5 line-clamp-1 group-hover:text-primary-container transition-colors"><?= htmlspecialchars($product['name']) ?></h3>
</a>
<p class="text-body-md text-on-surface-variant line-clamp-2 mb-4 leading-relaxed"><?= htmlspecialchars($product['description']) ?></p>

<div class="flex items-center gap-1 mb-3">
<div class="flex text-secondary">
<?php $full_stars = round($product['rating']); for ($i = 1; $i <= 5; $i++): ?>
<span class="material-symbols-outlined <?= $i <= $full_stars ? '' : 'text-outline-variant' ?> text-[16px]"><?= $i <= $full_stars ? 'star' : 'star' ?></span>
<?php endfor; ?>
</div>
<span class="text-label-sm text-outline ml-1">(<?= (int)$product['reviews_count'] ?>)</span>
</div>

<div class="mt-auto flex items-center justify-between">
<div class="flex items-baseline gap-2">
<span class="font-headline-md text-primary">₹<?= number_format($product['price'], 2) ?></span>
<?php if ($product['compare_price'] > 0): ?>
<span class="text-label-sm text-outline-variant line-through">₹<?= number_format($product['compare_price'], 2) ?></span>
<?php endif; ?>
</div>
<?php if ((int)$product['stock'] > 0): ?>
<button class="w-10 h-10 rounded-xl bg-primary/5 text-primary flex items-center justify-center hover:bg-primary hover:text-on-primary transition-all add-to-cart active:scale-90" data-product-id="<?= $product['id'] ?>">
<span class="material-symbols-outlined text-[20px]">add</span>
</button>
<?php endif; ?>
</div>
</div>
</div>
<?php endforeach; ?>
<?php else: ?>
<div class="col-span-full py-24 text-center flex flex-col items-center" id="empty-state">
<div class="w-28 h-28 bg-surface-container-low rounded-full flex items-center justify-center mb-6">
<span class="material-symbols-outlined text-primary text-[56px]">search_off</span>
</div>
<h3 class="font-headline-md text-headline-md text-primary mb-2">No products found</h3>
<p class="text-body-md text-on-surface-variant max-w-md mx-auto mb-8">We couldn't find any products matching your current filters. Try adjusting your search or resetting all filters.</p>
<div class="flex flex-col sm:flex-row gap-4">
<a href="<?= BASE_URL ?>/shop.php" class="bg-primary text-on-primary px-8 py-3.5 rounded-full font-label-md hover:bg-primary-container transition-all inline-block">Reset All Filters</a>
<a class="border border-primary text-primary px-8 py-3.5 rounded-full font-label-md hover:bg-surface-container-low transition-all inline-block" href="<?= BASE_URL ?>/contact-us.php">Need help finding something?</a>
</div>
</div>
<?php endif; ?>
</div>

<!-- Pagination -->
<?php if ($total_pages > 1): ?>
<nav class="mt-16 flex items-center justify-center gap-3">
<button class="w-11 h-11 rounded-full border border-outline-variant flex items-center justify-center text-primary hover:bg-surface-container-low hover:border-primary transition-all <?= $page <= 1 ? 'opacity-30 cursor-not-allowed' : '' ?>" <?= $page <= 1 ? 'disabled' : '' ?> onclick="goToPage(<?= $page - 1 ?>)">
<span class="material-symbols-outlined text-[20px]">chevron_left</span>
</button>
<div class="flex gap-1.5">
<?php
$start_page = max(1, $page - 2);
$end_page = min($total_pages, $page + 2);
if ($start_page > 1): ?>
<button class="w-11 h-11 rounded-full border border-outline-variant text-on-surface-variant hover:border-primary hover:text-primary flex items-center justify-center font-label-md transition-colors" onclick="goToPage(1)">1</button>
<?php if ($start_page > 2): ?>
<span class="w-11 h-11 flex items-center justify-center text-outline">...</span>
<?php endif; ?>
<?php endif; ?>
<?php for ($i = $start_page; $i <= $end_page; $i++): ?>
<button class="w-11 h-11 rounded-full <?= $i === $page ? 'bg-primary text-on-primary shadow-lg shadow-primary/20' : 'border border-outline-variant text-on-surface-variant hover:border-primary hover:text-primary' ?> flex items-center justify-center font-label-md transition-all active:scale-90" onclick="goToPage(<?= $i ?>)"><?= $i ?></button>
<?php endfor; ?>
<?php if ($end_page < $total_pages): ?>
<?php if ($end_page < $total_pages - 1): ?>
<span class="w-11 h-11 flex items-center justify-center text-outline">...</span>
<?php endif; ?>
<button class="w-11 h-11 rounded-full border border-outline-variant text-on-surface-variant hover:border-primary hover:text-primary flex items-center justify-center font-label-md transition-colors" onclick="goToPage(<?= $total_pages ?>)"><?= $total_pages ?></button>
<?php endif; ?>
</div>
<button class="w-11 h-11 rounded-full border border-outline-variant flex items-center justify-center text-primary hover:bg-surface-container-low hover:border-primary transition-all <?= $page >= $total_pages ? 'opacity-30 cursor-not-allowed' : '' ?>" <?= $page >= $total_pages ? 'disabled' : '' ?> onclick="goToPage(<?= $page + 1 ?>)">
<span class="material-symbols-outlined text-[20px]">chevron_right</span>
</button>
</nav>
<?php endif; ?>
</div>
</div>
</div>

<!-- Shop by Health Concern (Bento Style) -->
<section class="max-w-container-max mx-auto px-base md:px-margin-desktop py-16">
<div class="flex items-center justify-between mb-8">
<h2 class="font-headline-md text-headline-md text-on-surface">Shop by Health Concern</h2>
<a href="<?= BASE_URL ?>/shop.php" class="text-primary font-label-lg flex items-center gap-1 group">
View All Concerns <span class="material-symbols-outlined text-base group-hover:translate-x-1 transition-transform">arrow_forward</span>
</a>
</div>
<div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
<div class="group relative overflow-hidden rounded-2xl bg-white p-6 shadow-sm hover:shadow-lg transition-all duration-300 border border-transparent hover:border-primary/20">
<div class="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center mb-4 text-primary group-hover:scale-110 transition-transform">
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">self_improvement</span>
</div>
<h4 class="font-title-lg text-title-lg mb-1">Stress Relief</h4>
<p class="text-on-surface-variant text-body-md font-body-md">Natural adaptogens</p>
</div>
<div class="group relative overflow-hidden rounded-2xl bg-white p-6 shadow-sm hover:shadow-lg transition-all duration-300 border border-transparent hover:border-primary/20">
<div class="w-12 h-12 bg-tertiary-fixed/30 rounded-full flex items-center justify-center mb-4 text-tertiary group-hover:scale-110 transition-transform">
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">bedtime</span>
</div>
<h4 class="font-title-lg text-title-lg mb-1">Better Sleep</h4>
<p class="text-on-surface-variant text-body-md font-body-md">Deep restorative rest</p>
</div>
<div class="group relative overflow-hidden rounded-2xl bg-white p-6 shadow-sm hover:shadow-lg transition-all duration-300 border border-transparent hover:border-primary/20">
<div class="w-12 h-12 bg-primary-fixed/30 rounded-full flex items-center justify-center mb-4 text-on-primary-fixed-variant group-hover:scale-110 transition-transform">
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">verified_user</span>
</div>
<h4 class="font-title-lg text-title-lg mb-1">Immunity</h4>
<p class="text-on-surface-variant text-body-md font-body-md">Defense support</p>
</div>
<div class="group relative overflow-hidden rounded-2xl bg-white p-6 shadow-sm hover:shadow-lg transition-all duration-300 border border-transparent hover:border-primary/20">
<div class="w-12 h-12 bg-secondary-container rounded-full flex items-center justify-center mb-4 text-secondary group-hover:scale-110 transition-transform">
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">nutrition</span>
</div>
<h4 class="font-title-lg text-title-lg mb-1">Digestive</h4>
<p class="text-on-surface-variant text-body-md font-body-md">Gut health & detox</p>
</div>
</div>
</section>

<!-- Categories Grid from DB -->
<section class="max-w-container-max mx-auto px-base md:px-margin-desktop py-8">
<h2 class="font-headline-md text-headline-md text-on-surface mb-8">Featured Categories</h2>
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
<?php foreach ($categories as $cat): ?>
<?php
$cat_count = 0;
$count_q = "SELECT COUNT(*) as cnt FROM products WHERE category_id = " . (int)$cat['id'];
$count_r = $conn->query($count_q);
if ($count_r) { $cat_count = $count_r->fetch_assoc()['cnt']; }
?>
<div class="group cursor-pointer" onclick="window.location='<?= BASE_URL ?>/shop.php?category=<?= $cat['id'] ?>'">
<div class="relative h-48 rounded-2xl overflow-hidden mb-4 shadow-sm">
<?php if ($cat['image_url']): ?>
<img class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700" src="<?= htmlspecialchars($cat['image_url']) ?>" alt="<?= htmlspecialchars($cat['name']) ?>"/>
<?php else: ?>
<div class="w-full h-full bg-gradient-to-br from-primary/10 to-primary/5 flex items-center justify-center">
<span class="material-symbols-outlined text-5xl text-primary/30">category</span>
</div>
<?php endif; ?>
<div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
<div class="absolute bottom-4 left-4">
<span class="bg-primary/90 text-white text-[10px] uppercase tracking-widest px-3 py-1 rounded-full font-bold">Explore</span>
</div>
</div>
<h3 class="font-title-lg text-title-lg mb-1 group-hover:text-primary transition-colors"><?= htmlspecialchars($cat['name']) ?></h3>
<p class="text-on-surface-variant text-body-md font-body-md"><?= $cat_count ?> Products</p>
</div>
<?php endforeach; ?>
</div>
</section>

<!-- Browse by Format -->
<section class="max-w-container-max mx-auto px-base md:px-margin-desktop py-8">
<div class="bg-surface-container-low rounded-3xl p-8">
<h2 class="font-headline-md text-headline-md text-on-surface mb-8">Browse by Format</h2>
<div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-6">
<div class="text-center group cursor-pointer">
<div class="aspect-square bg-white rounded-full flex items-center justify-center mb-3 shadow-sm group-hover:shadow-md group-hover:-translate-y-1 transition-all">
<span class="material-symbols-outlined text-3xl text-primary">pill</span>
</div>
<p class="font-label-lg text-label-lg">Capsules</p>
</div>
<div class="text-center group cursor-pointer">
<div class="aspect-square bg-white rounded-full flex items-center justify-center mb-3 shadow-sm group-hover:shadow-md group-hover:-translate-y-1 transition-all">
<span class="material-symbols-outlined text-3xl text-primary">liquor</span>
</div>
<p class="font-label-lg text-label-lg">Syrups</p>
</div>
<div class="text-center group cursor-pointer">
<div class="aspect-square bg-white rounded-full flex items-center justify-center mb-3 shadow-sm group-hover:shadow-md group-hover:-translate-y-1 transition-all">
<span class="material-symbols-outlined text-3xl text-primary">opacity</span>
</div>
<p class="font-label-lg text-label-lg">Oils</p>
</div>
<div class="text-center group cursor-pointer">
<div class="aspect-square bg-white rounded-full flex items-center justify-center mb-3 shadow-sm group-hover:shadow-md group-hover:-translate-y-1 transition-all">
<span class="material-symbols-outlined text-3xl text-primary">coffee</span>
</div>
<p class="font-label-lg text-label-lg">Herbal Tea</p>
</div>
<div class="text-center group cursor-pointer">
<div class="aspect-square bg-white rounded-full flex items-center justify-center mb-3 shadow-sm group-hover:shadow-md group-hover:-translate-y-1 transition-all">
<span class="material-symbols-outlined text-3xl text-primary">eco</span>
</div>
<p class="font-label-lg text-label-lg">Powders</p>
</div>
<div class="text-center group cursor-pointer">
<div class="aspect-square bg-white rounded-full flex items-center justify-center mb-3 shadow-sm group-hover:shadow-md group-hover:-translate-y-1 transition-all">
<span class="material-symbols-outlined text-3xl text-primary">redeem</span>
</div>
<p class="font-label-lg text-label-lg">Combos</p>
</div>
</div>
</div>
</section>

<!-- Expert Consultations -->
<section class="max-w-container-max mx-auto px-base md:px-margin-desktop py-8 pb-16">
<h2 class="font-headline-md text-headline-md text-on-surface mb-8">Expert Consultations</h2>
<div class="flex flex-col lg:flex-row gap-gutter">
<div class="flex-1 bg-white border border-outline-variant/30 rounded-3xl p-8 flex items-center gap-8 shadow-sm hover:shadow-md transition-shadow">
<div class="w-32 h-32 rounded-2xl overflow-hidden flex-shrink-0">
<img class="w-full h-full object-cover" alt="Ayurvedic doctor consultation" src="https://lh3.googleusercontent.com/aida-public/AB6AXuD3hGX4_nQCApx-s8yhMpbu2WwnRHNUnQp--yvUNmnarIhqCCeBrpiyV75lROJ6FdsLzp9C21wAOae6ouNxzg9auJ-mEO8cMR9cSExXN8PWhKVFqvedci9xJ12Yt1EXsO_O-qYPNBj1sWGOZ1CswH78Ybc80gbGlYAW-FOQ-nu25tbyeemeUM5sbxVmHVZK-pMoj6PvsYMavD3VMhbMAjYwqqBQHy18t_QwGoupudZDNAMsXWcJNrlanH3ncgcrmYD3aJl19CzMiG8"/>
</div>
<div>
<h3 class="font-title-lg text-title-lg mb-2">Ayurvedic Consultation</h3>
<p class="text-on-surface-variant font-body-md text-body-md mb-4">Get personalized advice from our top experts based on your unique body constitution (Dosha).</p>
<a href="<?= BASE_URL ?>/appointment-booking.php" class="inline-block bg-primary text-white font-label-lg text-label-lg px-6 py-2.5 rounded-full hover:bg-primary/90 transition-colors">Book Now</a>
</div>
</div>
<div class="flex-1 bg-tertiary-container text-on-tertiary-container rounded-3xl p-8 flex items-center gap-8 shadow-sm hover:shadow-md transition-shadow">
<div class="w-32 h-32 rounded-2xl overflow-hidden bg-white/10 flex items-center justify-center flex-shrink-0">
<span class="material-symbols-outlined text-5xl text-on-tertiary-container">spa</span>
</div>
<div>
<h3 class="font-title-lg text-title-lg mb-2">Panchakarma Therapy</h3>
<p class="text-on-tertiary-container/80 font-body-md text-body-md mb-4">Detoxify and rejuvenate your body with ancient therapeutic practices performed by skilled professionals.</p>
<a href="<?= BASE_URL ?>/appointment-booking.php" class="inline-block bg-on-tertiary-container text-tertiary-container font-label-lg text-label-lg px-6 py-2.5 rounded-full hover:opacity-90 transition-opacity">Learn More</a>
</div>
</div>
</div>
</section>

<script>
document.getElementById('sort-select').addEventListener('change', function() {
document.getElementById('sort-input').value = this.value;
document.getElementById('filter-form').submit();
});

let searchTimer;
document.querySelector('input[name="search"]').addEventListener('keydown', function(e) {
if (e.key === 'Enter') {
e.preventDefault();
clearTimeout(searchTimer);
document.getElementById('filter-form').submit();
}
});

function goToPage(page) {
document.getElementById('page-input').value = page;
document.getElementById('filter-form').submit();
}

document.querySelectorAll('input[name="category"]').forEach(radio => {
radio.addEventListener('change', function() {
if (this.checked) {
document.getElementById('page-input').value = 1;
document.getElementById('filter-form').submit();
}
});
});

const priceSlider = document.getElementById('price-slider');
const priceLabel = document.getElementById('price-label');
if (priceSlider) {
priceSlider.addEventListener('input', function() {
priceLabel.textContent = 'Up to ₹' + Number(this.value).toLocaleString('en-IN');
});
priceSlider.addEventListener('change', function() {
document.getElementById('page-input').value = 1;
document.getElementById('filter-form').submit();
});
}

const filterSidebar = document.getElementById('filter-sidebar');
const filterToggle = document.getElementById('mobile-filter-toggle');
const filterClose = document.getElementById('mobile-filter-close');
const filterOverlay = document.getElementById('mobile-filter-overlay');

function openMobileFilters() {
filterSidebar.classList.remove('hidden');
filterOverlay.classList.remove('hidden');
setTimeout(() => {
filterSidebar.classList.add('translate-x-0');
filterSidebar.classList.remove('translate-x-full');
filterOverlay.classList.remove('opacity-0');
document.body.style.overflow = 'hidden';
}, 10);
}

function closeMobileFilters() {
filterSidebar.classList.remove('translate-x-0');
filterSidebar.classList.add('translate-x-full');
filterOverlay.classList.add('opacity-0');
document.body.style.overflow = '';
setTimeout(() => {
filterSidebar.classList.add('hidden');
filterOverlay.classList.add('hidden');
}, 300);
}

if (filterToggle) { filterToggle.addEventListener('click', openMobileFilters); }
if (filterClose) { filterClose.addEventListener('click', closeMobileFilters); }
if (filterOverlay) { filterOverlay.addEventListener('click', closeMobileFilters); }

function applyMobileDrawer() {
if (!filterSidebar) return;
if (window.innerWidth < 1024) {
filterSidebar.classList.add('fixed', 'top-0', 'right-0', 'h-full', 'w-80', 'max-w-[85vw]', 'z-50', 'translate-x-full', 'transition-transform', 'duration-300', 'ease-in-out', 'p-0', 'bg-transparent', 'shadow-2xl');
filterSidebar.querySelector('.bg-surface-container-lowest')?.classList.add('h-full', 'overflow-y-auto', 'rounded-none', 'rounded-l-2xl');
} else {
filterSidebar.classList.remove('fixed', 'top-0', 'right-0', 'h-full', 'w-80', 'max-w-[85vw]', 'z-50', 'translate-x-full', 'transition-transform', 'duration-300', 'ease-in-out', 'p-0', 'bg-transparent', 'shadow-2xl');
filterSidebar.querySelector('.bg-surface-container-lowest')?.classList.remove('h-full', 'overflow-y-auto', 'rounded-none', 'rounded-l-2xl');
}
}
applyMobileDrawer();
window.addEventListener('resize', applyMobileDrawer);

document.querySelectorAll('.add-to-cart').forEach(btn => {
btn.addEventListener('click', function(e) {
e.preventDefault();
const id = this.dataset.productId;
if (!id) return;
const btnInner = this.querySelector('.material-symbols-outlined');
const orig = btnInner.textContent;
btnInner.textContent = 'sync';
fetch('<?= BASE_URL ?>/cart-update.php?action=add&id=' + id)
.then(() => {
btnInner.textContent = 'check';
this.classList.add('bg-secondary', 'text-on-secondary');
this.classList.remove('bg-primary/5', 'text-primary', 'bg-primary', 'hover:bg-primary-container');
const badge = document.querySelector('#cart-count-badge');
if (badge) {
const c = parseInt(badge.textContent) + 1;
badge.textContent = c;
}
setTimeout(() => {
btnInner.textContent = orig;
this.classList.remove('bg-secondary', 'text-on-secondary');
this.classList.add('bg-primary', 'text-on-primary');
}, 1500);
});
});
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
