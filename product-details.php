<?php
require_once __DIR__ . '/includes/config.php';

$db = getDB();
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$product = $db->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = $product_id")->fetch_assoc();

if (!$product) {
    header('Location: ' . BASE_URL . '/shop.php');
    exit;
}

$site_title = htmlspecialchars($product['name']);
require_once __DIR__ . '/includes/header.php';

$related = $db->query("SELECT * FROM products WHERE category_id = {$product['category_id']} AND id != {$product['id']} LIMIT 4")->fetch_all(MYSQLI_ASSOC);
if (empty($related)) {
    $related = $db->query("SELECT * FROM products WHERE id != {$product['id']} LIMIT 4")->fetch_all(MYSQLI_ASSOC);
}
?>

<section class="pt-[100px] max-w-container-max mx-auto px-gutter mb-section-gap">
<nav class="mb-8 flex items-center space-x-2 text-on-surface-variant font-label-sm">
<a class="hover:text-primary" href="<?= BASE_URL ?>/index.php">Home</a>
<span class="material-symbols-outlined text-[16px]">chevron_right</span>
<a class="hover:text-primary" href="<?= BASE_URL ?>/shop.php">Shop</a>
<span class="material-symbols-outlined text-[16px]">chevron_right</span>
<span class="text-primary font-bold"><?= htmlspecialchars($product['name']) ?></span>
</nav>

<section class="grid grid-cols-1 lg:grid-cols-12 gap-12">
<div class="lg:col-span-7">
<div class="relative aspect-square overflow-hidden rounded-xl bg-white border border-outline-variant/30 group">
<img alt="<?= htmlspecialchars($product['name']) ?>" class="w-full h-full object-contain p-8 transition-transform duration-700 group-hover:scale-110" src="<?= htmlspecialchars($product['image_url']) ?>">
<?php if ($product['is_bestseller']): ?>
<div class="absolute top-4 left-4 bg-secondary-container text-on-secondary-container px-3 py-1 rounded-full text-[12px] font-bold">BEST SELLER</div>
<?php endif; ?>
</div>
</div>

<div class="lg:col-span-5 flex flex-col space-y-6">
<div>
<span class="text-tertiary font-label-md tracking-widest"><?= strtoupper(htmlspecialchars($product['category_name'] ?? 'GENERAL')) ?></span>
<h1 class="font-display-lg text-display-lg mt-2 text-primary leading-tight"><?= htmlspecialchars($product['name']) ?></h1>
<div class="flex items-center space-x-3 mt-4">
<div class="flex text-secondary">
<?php for ($i = 0; $i < 5; $i++): ?>
<span class="material-symbols-outlined <?= $i < round($product['rating']) ? 'fill-icon' : '' ?>"><?= $i < round($product['rating']) ? 'star' : 'star' ?></span>
<?php endfor; ?>
</div>
<span class="text-on-surface-variant font-label-md border-l pl-3 border-outline-variant"><?= number_format($product['rating'], 1) ?> (<?= (int)$product['reviews_count'] ?> reviews)</span>
</div>
</div>

<div class="flex items-baseline space-x-4">
<span class="text-primary font-headline-lg">₹<?= number_format($product['price'], 2) ?></span>
<?php if ($product['compare_price'] > 0): ?>
<span class="text-on-surface-variant line-through text-body-lg">₹<?= number_format($product['compare_price'], 2) ?></span>
<span class="text-secondary font-bold text-label-md">Save <?= round((1 - $product['price'] / $product['compare_price']) * 100) ?>%</span>
<?php endif; ?>
</div>

<p class="text-on-surface-variant font-body-md leading-relaxed"><?= htmlspecialchars($product['description']) ?></p>

<form method="POST" action="<?= BASE_URL ?>/cart-update.php" class="space-y-4 pt-4">
<input type="hidden" name="product_id" value="<?= $product['id'] ?>">
<input type="hidden" name="action" value="add">
<div class="flex space-x-4">
<div class="flex items-center border border-outline rounded-lg bg-surface">
<button type="button" class="px-4 py-3 hover:text-primary transition-colors" onclick="decrementQty()"><span class="material-symbols-outlined">remove</span></button>
<input class="w-12 text-center bg-transparent border-none focus:ring-0 font-bold" id="qty" name="quantity" min="1" type="number" value="1"/>
<button type="button" class="px-4 py-3 hover:text-primary transition-colors" onclick="incrementQty()"><span class="material-symbols-outlined">add</span></button>
</div>
<button type="submit" class="flex-grow bg-primary text-on-primary rounded-lg py-4 font-label-md hover:shadow-lg transition-all active:scale-[0.98]">Add to Cart</button>
</div>
</form>

<div class="grid grid-cols-3 gap-4 py-8 border-y border-outline-variant/30 mt-4">
<div class="text-center"><span class="material-symbols-outlined text-primary mb-2 text-[32px]">eco</span><p class="text-[10px] font-bold text-on-surface-variant uppercase">100% Organic</p></div>
<div class="text-center"><span class="material-symbols-outlined text-primary mb-2 text-[32px]">biotech</span><p class="text-[10px] font-bold text-on-surface-variant uppercase">Lab Tested</p></div>
<div class="text-center"><span class="material-symbols-outlined text-primary mb-2 text-[32px]">verified</span><p class="text-[10px] font-bold text-on-surface-variant uppercase">GMP Certified</p></div>
</div>
</div>
</section>

<?php if (!empty($related)): ?>
<section class="mt-section-gap">
<h2 class="font-headline-lg text-headline-lg text-primary mb-8">Related Essentials</h2>
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
<?php foreach ($related as $r): ?>
<a href="<?= BASE_URL ?>/product-details.php?id=<?= $r['id'] ?>" class="group bg-white rounded-xl border border-outline-variant/30 overflow-hidden hover:shadow-xl transition-all">
<div class="aspect-square bg-surface-container relative overflow-hidden p-6">
<img class="w-full h-full object-cover" src="<?= htmlspecialchars($r['image_url']) ?>" alt="<?= htmlspecialchars($r['name']) ?>">
</div>
<div class="p-5">
<h4 class="font-bold text-primary group-hover:text-secondary transition-colors"><?= htmlspecialchars($r['name']) ?></h4>
<p class="font-bold text-primary mt-2">₹<?= number_format($r['price'], 2) ?></p>
</div>
</a>
<?php endforeach; ?>
</div>
</section>
<?php endif; ?>
</section>

<style>
.fill-icon { font-variation-settings: 'FILL' 1; }
</style>
<script>
function incrementQty() { const i = document.getElementById('qty'); i.value = parseInt(i.value) + 1; }
function decrementQty() { const i = document.getElementById('qty'); if (parseInt(i.value) > 1) i.value = parseInt(i.value) - 1; }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
