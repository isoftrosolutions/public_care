<?php
require_once __DIR__ . '/includes/config.php';
$site_title = 'Lab Tests';
$conn = getDB();

$category_filter = isset($_GET['category']) ? trim($_GET['category']) : '';
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';

$where = ["active = 1"];
$params = [];

if ($category_filter !== '') {
    $where[] = "category = ?";
    $params[] = $category_filter;
}
if ($search_term !== '') {
    $where[] = "(name LIKE ? OR description LIKE ? OR category LIKE ?)";
    $like = '%' . $search_term . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

$where_clause = implode(' AND ', $where);

$stmt = $conn->prepare("SELECT * FROM lab_tests WHERE $where_clause ORDER BY category, name");
if (!empty($params)) {
    $types = str_repeat('s', count($params));
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$tests = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$categories = [];
$cat_result = $conn->query("SELECT DISTINCT category FROM lab_tests WHERE active = 1 AND category IS NOT NULL AND category != '' ORDER BY category");
while ($c = $cat_result->fetch_assoc()) {
    $categories[] = $c['category'];
}

require_once __DIR__ . '/includes/header.php';
?>
<style>
.bg-pattern { background-image: radial-gradient(#ffffff20 1px, transparent 0); background-size: 24px 24px; }
@keyframes fadeSlideUp {
  from { opacity: 0; transform: translateY(24px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>

<!-- Hero -->
<section class="relative overflow-hidden bg-primary py-16 md:py-24">
<div class="absolute inset-0 opacity-10 bg-pattern"></div>
<div class="max-w-container-max mx-auto px-base md:px-margin-desktop relative z-10 flex flex-col md:flex-row items-center gap-12">
<div class="flex-1 text-center md:text-left">
<p class="text-primary-fixed font-label-lg text-label-lg mb-4 tracking-widest uppercase">Convenient • Accurate • Trusted</p>
<h1 class="font-display-lg text-display-lg text-on-primary-container mb-6">Book Lab Tests<br class="hidden md:block">from Home</h1>
<p class="text-on-primary-container/80 font-body-lg text-body-lg max-w-xl mx-auto md:mx-0">
NABL accredited labs. Free home sample collection. Get digital reports within 24&ndash;48 hours.
</p>
</div>
<div class="flex-1 w-full max-w-md">
<div class="relative group">
<div class="absolute -inset-4 bg-primary-fixed/20 blur-3xl rounded-full"></div>
<img class="rounded-2xl shadow-2xl relative z-10 transform group-hover:scale-[1.02] transition-transform duration-500 object-cover aspect-square" alt="Lab test samples" src="https://images.unsplash.com/photo-1579154204601-01588f351e67?w=600&q=80"/>
</div>
</div>
</div>
</section>

<!-- Breadcrumb -->
<div class="max-w-container-max mx-auto px-base md:px-margin-desktop pt-8">
<nav class="flex items-center gap-2 text-label-sm text-outline mb-2">
<a href="<?= BASE_URL ?>/index.php" class="hover:text-primary transition-colors">Home</a>
<span class="material-symbols-outlined text-[16px]">chevron_right</span>
<span class="text-primary font-medium">Lab Tests</span>
</nav>
</div>

<div class="max-w-container-max mx-auto px-base md:px-margin-desktop py-8 lg:py-12">

<!-- Search & Filter -->
<div class="flex flex-col lg:flex-row gap-6 mb-10">
<div class="flex-1 relative">
<span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline text-[20px]">search</span>
<input id="test-search" class="w-full pl-12 pr-4 py-3.5 rounded-xl border border-outline-variant focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all bg-surface text-on-surface placeholder:text-outline" placeholder="Search tests by name, category, or keyword..." type="text" value="<?= htmlspecialchars($search_term) ?>">
</div>
</div>

<!-- Category Chips -->
<div class="flex flex-wrap items-center gap-2 mb-8">
<button class="category-chip px-4 py-2 rounded-full text-label-sm font-label-sm border transition-all <?= $category_filter === '' ? 'bg-primary text-on-primary border-primary' : 'border-outline-variant text-on-surface-variant hover:border-primary hover:text-primary bg-surface' ?>" data-category="">All Tests</button>
<?php foreach ($categories as $cat): ?>
<button class="category-chip px-4 py-2 rounded-full text-label-sm font-label-sm border transition-all <?= $category_filter === $cat ? 'bg-primary text-on-primary border-primary' : 'border-outline-variant text-on-surface-variant hover:border-primary hover:text-primary bg-surface' ?>" data-category="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></button>
<?php endforeach; ?>
</div>

<!-- Results Info -->
<div class="flex items-center justify-between mb-6">
<p class="text-body-md text-on-surface-variant">
Showing <span class="font-semibold text-on-surface"><?= count($tests) ?></span> test<?= count($tests) !== 1 ? 's' : '' ?>
<?php if ($search_term !== ''): ?> for "<span class="text-primary font-medium"><?= htmlspecialchars($search_term) ?></span>"<?php endif; ?>
</p>
</div>

<!-- Test Grid -->
<?php if (count($tests) > 0): ?>
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6" id="test-grid">
<?php foreach ($tests as $index => $test):
$orig_price = (float)$test['price'];
$disc_price = (float)($test['discount_price'] ?? 0);
$has_discount = $disc_price > 0 && $disc_price < $orig_price;
$discount_pct = $has_discount ? round((1 - $disc_price / $orig_price) * 100) : 0;
$report_hours = (int)($test['report_time_hours'] ?? 24);
$report_text = $report_hours < 24 ? $report_hours . ' hours' : ($report_hours >= 24 && $report_hours < 48 ? '1 day' : (int)($report_hours / 24) . ' days');
?>
<div class="group bg-surface-container-lowest rounded-2xl border border-outline-variant/20 overflow-hidden hover:shadow-xl hover:border-primary/20 transition-all duration-500 flex flex-col" style="animation: fadeSlideUp 0.5s ease-out <?= $index * 0.05 ?>s both;">
<div class="p-6 flex-1 flex flex-col">
<div class="flex items-start justify-between mb-3">
<span class="text-[11px] font-medium text-tertiary uppercase tracking-widest px-3 py-1 bg-tertiary-fixed/30 rounded-full"><?= htmlspecialchars($test['category'] ?? 'General') ?></span>
<?php if ($has_discount): ?>
<span class="bg-error text-on-error px-2.5 py-1 rounded-full text-[10px] font-bold shadow-sm">-<?= $discount_pct ?>%</span>
<?php endif; ?>
</div>
<h3 class="font-headline-md text-headline-md text-primary mb-2 group-hover:text-primary-container transition-colors"><?= htmlspecialchars($test['name']) ?></h3>
<p class="text-body-md text-on-surface-variant line-clamp-2 mb-4 leading-relaxed"><?= htmlspecialchars($test['description']) ?></p>

<div class="flex items-center gap-4 mb-4 text-label-sm text-on-surface-variant">
<div class="flex items-center gap-1">
<span class="material-symbols-outlined text-[16px]">schedule</span>
<span>Reports in <?= $report_text ?></span>
</div>
<?php if ($test['home_collection']): ?>
<div class="flex items-center gap-1">
<span class="material-symbols-outlined text-[16px]">home</span>
<span>Free pickup</span>
</div>
<?php endif; ?>
</div>

<?php if (!empty($test['includes'])): ?>
<p class="text-label-sm text-on-surface-variant mb-4 line-clamp-2"><span class="font-semibold">Includes:</span> <?= htmlspecialchars($test['includes']) ?></p>
<?php endif; ?>

<div class="mt-auto flex items-center justify-between pt-4 border-t border-outline-variant/20">
<div class="flex items-baseline gap-2">
<span class="font-headline-md text-primary">₹<?= number_format($has_discount ? $disc_price : $orig_price) ?></span>
<?php if ($has_discount): ?>
<span class="text-label-sm text-outline-variant line-through">₹<?= number_format($orig_price) ?></span>
<?php endif; ?>
</div>
<a href="<?= BASE_URL ?>/lab-booking.php?id=<?= $test['id'] ?>" class="inline-flex items-center gap-1.5 bg-primary text-on-primary px-5 py-2.5 rounded-xl font-label-md hover:bg-primary-container transition-all active:scale-[0.97]">
Book Now
<span class="material-symbols-outlined text-[16px]">arrow_forward</span>
</a>
</div>
</div>
</div>
<?php endforeach; ?>
</div>
<?php else: ?>
<div class="col-span-full py-24 text-center flex flex-col items-center" id="empty-state">
<div class="w-28 h-28 bg-surface-container-low rounded-full flex items-center justify-center mb-6">
<span class="material-symbols-outlined text-primary text-[56px]">search_off</span>
</div>
<h3 class="font-headline-md text-headline-md text-primary mb-2">No tests found</h3>
<p class="text-body-md text-on-surface-variant max-w-md mx-auto mb-8">We couldn't find any lab tests matching your search. Try a different keyword or browse all tests.</p>
<a href="<?= BASE_URL ?>/lab-tests.php" class="bg-primary text-on-primary px-8 py-3.5 rounded-full font-label-md hover:bg-primary-container transition-all inline-block">View All Tests</a>
</div>
<?php endif; ?>

<!-- Trust Badges -->
<section class="mt-20 bg-surface-container-low rounded-3xl p-8 md:p-12">
<div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
<div>
<div class="w-14 h-14 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
<span class="material-symbols-outlined text-primary text-[28px]" style="font-variation-settings: 'FILL' 1;">verified</span>
</div>
<h4 class="font-title-lg text-title-lg mb-1">NABL Accredited</h4>
<p class="text-body-md text-on-surface-variant">Certified labs with highest quality standards</p>
</div>
<div>
<div class="w-14 h-14 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
<span class="material-symbols-outlined text-primary text-[28px]" style="font-variation-settings: 'FILL' 1;">local_shipping</span>
</div>
<h4 class="font-title-lg text-title-lg mb-1">Free Home Visit</h4>
<p class="text-body-md text-on-surface-variant">Sample collection at your doorstep</p>
</div>
<div>
<div class="w-14 h-14 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
<span class="material-symbols-outlined text-primary text-[28px]" style="font-variation-settings: 'FILL' 1;">quickreply</span>
</div>
<h4 class="font-title-lg text-title-lg mb-1">Fast Reports</h4>
<p class="text-body-md text-on-surface-variant">Digital reports within 24&ndash;48 hours</p>
</div>
<div>
<div class="w-14 h-14 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
<span class="material-symbols-outlined text-primary text-[28px]" style="font-variation-settings: 'FILL' 1;">support_agent</span>
</div>
<h4 class="font-title-lg text-title-lg mb-1">Expert Support</h4>
<p class="text-body-md text-on-surface-variant">Consult our doctors for report analysis</p>
</div>
</div>
</section>

</div>

<script>
const searchInput = document.getElementById('test-search');
let searchTimer;
searchInput.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        clearTimeout(searchTimer);
        const params = new URLSearchParams(window.location.search);
        params.set('search', this.value);
        if (this.value === '') params.delete('search');
        window.location.search = params.toString();
    }
});

document.querySelectorAll('.category-chip').forEach(btn => {
    btn.addEventListener('click', function() {
        const params = new URLSearchParams(window.location.search);
        const cat = this.dataset.category;
        if (cat) {
            params.set('category', cat);
        } else {
            params.delete('category');
        }
        params.delete('search');
        window.location.search = params.toString();
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
