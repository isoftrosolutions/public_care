<?php
require_once __DIR__ . '/includes/config.php';

$db = getDB();
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $db->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
$stmt->bind_param('i', $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    header('Location: ' . BASE_URL . '/shop.php');
    exit;
}

$site_title = htmlspecialchars($product['name']);
require_once __DIR__ . '/includes/header.php';

$cat_id = (int)$product['category_id'];
$prod_id = (int)$product['id'];

$stmt2 = $db->prepare("SELECT * FROM products WHERE category_id = ? AND id != ? LIMIT 4");
$stmt2->bind_param('ii', $cat_id, $prod_id);
$stmt2->execute();
$related = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);
if (empty($related)) {
    $stmt3 = $db->prepare("SELECT * FROM products WHERE id != ? LIMIT 4");
    $stmt3->bind_param('i', $prod_id);
    $stmt3->execute();
    $related = $stmt3->get_result()->fetch_all(MYSQLI_ASSOC);
}

$reviews = [];
$stmt4 = $db->prepare("SELECT r.*, u.full_name as user_name FROM reviews r LEFT JOIN users u ON r.user_id = u.id WHERE r.product_id = ? ORDER BY r.created_at DESC");
$stmt4->bind_param('i', $prod_id);
$stmt4->execute();
while ($row = $stmt4->get_result()->fetch_assoc()) {
    $reviews[] = $row;
}

$rating = (float)($product['rating'] ?? 0);
$reviews_count = (int)($product['reviews_count'] ?? count($reviews));
$discount_percent = 0;
if (!empty($product['compare_price']) && $product['compare_price'] > 0 && $product['price'] > 0) {
    $discount_percent = round((1 - $product['price'] / $product['compare_price']) * 100);
    if ($discount_percent < 0) $discount_percent = 0;
}
$main_image = !empty($product['image_url']) ? htmlspecialchars($product['image_url']) : '';
?>
<!-- Product Hero Section -->
<div class="max-w-container-max mx-auto px-base md:px-margin-desktop pt-24 pb-12">
<div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-start">

<!-- Image Gallery -->
<div class="space-y-6">
<div class="relative group aspect-square rounded-xl overflow-hidden bg-surface-container-low shadow-sm border border-outline-variant/30 herbal-mesh-bg">
<img id="main-product-image" alt="<?= htmlspecialchars($product['name']) ?>" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" src="<?= $main_image ?>">
<?php if (!empty($product['is_bestseller'])): ?>
<div class="absolute top-4 left-4 bg-primary text-on-primary px-3 py-1 rounded-full text-label-sm uppercase tracking-wider font-bold">Bestseller</div>
<?php endif; ?>
</div>
<div class="grid grid-cols-4 gap-4" id="thumbnail-grid">
<button class="aspect-square rounded-lg border-2 border-primary overflow-hidden thumbnail-btn active" data-img="<?= $main_image ?>">
<img alt="<?= htmlspecialchars($product['name']) ?>" class="w-full h-full object-cover" src="<?= $main_image ?>">
</button>
<button class="aspect-square rounded-lg border border-outline-variant hover:border-primary overflow-hidden transition-all thumbnail-btn" data-img="<?= $main_image ?>">
<img alt="<?= htmlspecialchars($product['name']) ?>" class="w-full h-full object-cover" src="<?= $main_image ?>">
</button>
<button class="aspect-square rounded-lg border border-outline-variant hover:border-primary overflow-hidden transition-all thumbnail-btn" data-img="<?= $main_image ?>">
<img alt="<?= htmlspecialchars($product['name']) ?>" class="w-full h-full object-cover" src="<?= $main_image ?>">
</button>
<button class="aspect-square rounded-lg border border-outline-variant hover:border-primary overflow-hidden transition-all thumbnail-btn" data-img="<?= $main_image ?>">
<img alt="<?= htmlspecialchars($product['name']) ?>" class="w-full h-full object-cover" src="<?= $main_image ?>">
</button>
</div>
</div>

<!-- Product Details -->
<div class="flex flex-col gap-6">
<div>
<nav class="flex items-center gap-2 text-label-sm text-outline mb-4">
<a class="hover:text-primary transition-colors" href="<?= BASE_URL ?>/shop.php">Shop</a>
<span class="material-symbols-outlined text-sm">chevron_right</span>
<a class="hover:text-primary transition-colors" href="<?= BASE_URL ?>/shop.php?category=<?= $product['category_id'] ?>"><?= htmlspecialchars($product['category_name'] ?? 'Products') ?></a>
<span class="material-symbols-outlined text-sm">chevron_right</span>
<span class="text-on-surface"><?= htmlspecialchars($product['name']) ?></span>
</nav>
<h1 class="font-display-lg text-display-lg text-primary mb-2"><?= htmlspecialchars($product['name']) ?></h1>
<div class="flex items-center gap-4">
<div class="flex items-center">
<?php
$full_stars = floor($rating);
$half_star = ($rating - $full_stars) >= 0.3 ? 1 : 0;
$empty_stars = 5 - $full_stars - $half_star;
for ($i = 0; $i < $full_stars; $i++):
?>
<span class="material-symbols-outlined text-tertiary-fixed-dim" style="font-variation-settings: 'FILL' 1;">star</span>
<?php endfor; ?>
<?php if ($half_star): ?>
<span class="material-symbols-outlined text-tertiary-fixed-dim" style="font-variation-settings: 'FILL' 0.5;">star_half</span>
<?php endif; ?>
<?php for ($i = 0; $i < $empty_stars; $i++): ?>
<span class="material-symbols-outlined text-tertiary-fixed-dim" style="font-variation-settings: 'FILL' 0;">star</span>
<?php endfor; ?>
<span class="ml-2 font-label-lg text-label-lg text-on-surface-variant">(<?= $reviews_count ?> Reviews)</span>
</div>
<span class="h-4 w-px bg-outline-variant"></span>
<span class="text-primary font-label-lg text-label-lg"><?= $product['stock'] > 0 ? 'In Stock' : 'Out of Stock' ?></span>
</div>
</div>

<div class="flex items-baseline gap-4">
<span class="text-display-lg font-display-lg text-on-surface">₹<?= number_format($product['price'], 2) ?></span>
<?php if (!empty($product['compare_price']) && $product['compare_price'] > 0): ?>
<span class="text-outline-variant line-through text-title-lg">₹<?= number_format($product['compare_price'], 2) ?></span>
<?php if ($discount_percent > 0): ?>
<span class="bg-primary-fixed text-on-primary-fixed px-2 py-1 rounded text-label-sm font-bold"><?= $discount_percent ?>% OFF</span>
<?php endif; ?>
<?php endif; ?>
</div>

<p class="text-body-lg text-on-surface-variant leading-relaxed"><?= htmlspecialchars($product['description']) ?></p>

<!-- Natural Benefits -->
<div class="grid grid-cols-3 gap-4 py-6 border-y border-outline-variant/30">
<div class="text-center group">
<div class="w-12 h-12 rounded-full bg-secondary-container flex items-center justify-center mx-auto mb-2 group-hover:bg-primary-container transition-colors">
<span class="material-symbols-outlined text-primary group-hover:text-on-primary-container transition-colors">psychology</span>
</div>
<p class="font-label-lg text-label-lg">Stress Relief</p>
</div>
<div class="text-center group">
<div class="w-12 h-12 rounded-full bg-secondary-container flex items-center justify-center mx-auto mb-2 group-hover:bg-primary-container transition-colors">
<span class="material-symbols-outlined text-primary group-hover:text-on-primary-container transition-colors">bolt</span>
</div>
<p class="font-label-lg text-label-lg">Energy Boost</p>
</div>
<div class="text-center group">
<div class="w-12 h-12 rounded-full bg-secondary-container flex items-center justify-center mx-auto mb-2 group-hover:bg-primary-container transition-colors">
<span class="material-symbols-outlined text-primary group-hover:text-on-primary-container transition-colors">bedtime</span>
</div>
<p class="font-label-lg text-label-lg">Better Sleep</p>
</div>
</div>

<!-- Action Buttons -->
<form method="POST" action="<?= BASE_URL ?>/cart-update.php" id="addToCartForm">
<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
<input type="hidden" name="product_id" value="<?= $product['id'] ?>">
<input type="hidden" name="action" value="add">
<div class="flex flex-col gap-3 mt-4">
<div class="flex gap-4">
<div class="flex items-center border border-outline rounded-lg bg-surface px-4 py-2">
<button type="button" class="p-1 hover:text-primary" onclick="decrementQty()"><span class="material-symbols-outlined">remove</span></button>
<input class="w-10 text-center bg-transparent border-none focus:ring-0 font-bold" id="qty" name="quantity" type="text" value="1" readonly>
<button type="button" class="p-1 hover:text-primary" onclick="incrementQty()"><span class="material-symbols-outlined">add</span></button>
</div>
<button type="submit" class="flex-1 bg-primary text-on-primary py-4 rounded-lg font-label-lg text-label-lg hover:brightness-110 active:scale-[0.98] transition-all flex items-center justify-center gap-2 shadow-lg shadow-primary/20">
<span class="material-symbols-outlined">shopping_bag</span>
Add to Cart
</button>
</div>
<button type="button" class="w-full bg-secondary text-on-secondary py-4 rounded-lg font-label-lg text-label-lg hover:brightness-110 active:scale-[0.98] transition-all" onclick="buyNow(<?= $product['id'] ?>)">
Buy Now
</button>
</div>
</form>

<!-- Trust Badges -->
<div class="flex items-center justify-between mt-4">
<div class="flex flex-col items-center gap-1">
<span class="material-symbols-outlined text-primary text-3xl">eco</span>
<span class="text-label-sm">100% Organic</span>
</div>
<div class="flex flex-col items-center gap-1">
<span class="material-symbols-outlined text-primary text-3xl">nutrition</span>
<span class="text-label-sm">Vegan</span>
</div>
<div class="flex flex-col items-center gap-1">
<span class="material-symbols-outlined text-primary text-3xl">biotech</span>
<span class="text-label-sm">Lab Tested</span>
</div>
</div>
</div>
</div>

<!-- Detailed Description & Ayurvedic Properties -->
<section class="mt-20 grid grid-cols-1 md:grid-cols-3 gap-12">
<div class="md:col-span-2 space-y-10">
<div class="space-y-4">
<h2 class="font-headline-lg text-headline-lg text-primary">The Ancient Wisdom of Ayurveda</h2>
<p class="text-body-lg text-on-surface-variant"><?= htmlspecialchars($product['description']) ?></p>
</div>
<div class="bg-secondary-container/50 p-8 rounded-xl border border-secondary-fixed/50">
<h3 class="font-title-lg text-title-lg text-secondary mb-4 flex items-center gap-2">
<span class="material-symbols-outlined">balance</span>
Dosha Balancing Properties
</h3>
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
<div class="p-4 bg-surface rounded-lg">
<p class="font-bold text-primary mb-1">Vata</p>
<p class="text-body-md">Reduces anxiety, insomnia, and nervous energy through grounding properties.</p>
</div>
<div class="p-4 bg-surface rounded-lg">
<p class="font-bold text-primary mb-1">Pitta</p>
<p class="text-body-md">Cools inflammatory responses and manages stress-related heat in the body.</p>
</div>
<div class="p-4 bg-surface rounded-lg">
<p class="font-bold text-primary mb-1">Kapha</p>
<p class="text-body-md">Boosts metabolic fire and clears sluggishness while providing vitality.</p>
</div>
</div>
</div>
</div>
<div class="space-y-8">
<div class="p-6 bg-surface-container-low rounded-xl border border-outline-variant/30">
<h3 class="font-title-lg text-title-lg text-on-surface mb-4">How to Use</h3>
<ul class="space-y-4">
<li class="flex gap-3">
<span class="w-6 h-6 rounded-full bg-primary text-on-primary flex items-center justify-center flex-shrink-0 text-xs">1</span>
<p class="text-body-md">Take 1 capsule twice daily, preferably with meals.</p>
</li>
<li class="flex gap-3">
<span class="w-6 h-6 rounded-full bg-primary text-on-primary flex items-center justify-center flex-shrink-0 text-xs">2</span>
<p class="text-body-md">Best consumed with warm water or milk to aid absorption.</p>
</li>
<li class="flex gap-3">
<span class="w-6 h-6 rounded-full bg-primary text-on-primary flex items-center justify-center flex-shrink-0 text-xs">3</span>
<p class="text-body-md">Consistent daily use for 4-6 weeks yields optimal results.</p>
</li>
</ul>
</div>
<div class="p-6 border border-outline-variant/50 rounded-xl">
<h3 class="font-label-lg text-label-lg mb-2">Ingredients</h3>
<p class="text-body-md text-on-surface-variant">Premium Ayurvedic herbs sourced sustainably. 100% natural with no artificial additives or preservatives. Each batch is lab-tested for purity and potency.</p>
</div>
</div>
</section>

<!-- Customer Reviews Section -->
<?php if (!empty($reviews)): ?>
<section class="mt-24">
<div class="flex justify-between items-end mb-10">
<div>
<h2 class="font-headline-lg text-headline-lg text-on-surface mb-2">Customer Experiences</h2>
<p class="text-body-lg text-on-surface-variant">Real feedback from our holistic community</p>
</div>
</div>
<div class="grid grid-cols-1 md:grid-cols-3 gap-gutter">
<?php foreach ($reviews as $review):
$review_rating = (int)($review['rating'] ?? 5);
$initial = strtoupper(substr($review['user_name'] ?? 'U', 0, 1));
?>
<div class="p-6 bg-surface-container-lowest rounded-xl shadow-sm flex flex-col gap-4">
<div class="flex items-center gap-4">
<div class="w-12 h-12 rounded-full bg-primary-container flex items-center justify-center text-on-primary-container font-bold text-lg"><?= $initial ?></div>
<div>
<p class="font-bold"><?= htmlspecialchars($review['user_name'] ?? 'Anonymous') ?></p>
<div class="flex text-tertiary-fixed-dim text-sm">
<?php for ($s = 0; $s < 5; $s++): ?>
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' <?= $s < $review_rating ? 1 : 0 ?>;">star</span>
<?php endfor; ?>
</div>
</div>
</div>
<p class="text-body-md text-on-surface-variant italic"><?= htmlspecialchars($review['comment'] ?? '') ?></p>
</div>
<?php endforeach; ?>
</div>
</section>
<?php endif; ?>

<!-- Related Products -->
<?php if (!empty($related)): ?>
<section class="mt-24 mb-12">
<h2 class="font-headline-lg text-headline-lg text-on-surface mb-8">Complement Your Ritual</h2>
<div class="grid grid-cols-2 md:grid-cols-4 gap-gutter">
<?php foreach ($related as $r):
$r_image = !empty($r['image_url']) ? htmlspecialchars($r['image_url']) : '';
?>
<a href="<?= BASE_URL ?>/product-details.php?id=<?= $r['id'] ?>" class="group">
<div class="relative aspect-[3/4] rounded-xl overflow-hidden bg-surface-container-low mb-4 shadow-sm">
<img alt="<?= htmlspecialchars($r['name']) ?>" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" src="<?= $r_image ?>">
<div class="absolute bottom-4 right-4 w-10 h-10 rounded-full bg-primary text-on-primary flex items-center justify-center opacity-0 translate-y-4 group-hover:opacity-100 group-hover:translate-y-0 transition-all">
<span class="material-symbols-outlined">add_shopping_cart</span>
</div>
</div>
<h3 class="font-label-lg text-label-lg"><?= htmlspecialchars($r['name']) ?></h3>
<p class="text-primary font-bold">₹<?= number_format($r['price'], 2) ?></p>
</a>
<?php endforeach; ?>
</div>
</section>
<?php endif; ?>
</div>

<style>
.herbal-mesh-bg {
    background-color: #fbf9f8;
    background-image: radial-gradient(at 0% 0%, rgba(31, 108, 53, 0.05) 0px, transparent 50%),
                      radial-gradient(at 100% 100%, rgba(255, 223, 159, 0.1) 0px, transparent 50%);
}
.thumbnail-btn.active {
    border-color: #005221;
    border-width: 2px;
}
</style>
<script>
function incrementQty() {
    var i = document.getElementById('qty');
    i.value = parseInt(i.value) + 1;
}
function decrementQty() {
    var i = document.getElementById('qty');
    if (parseInt(i.value) > 1) i.value = parseInt(i.value) - 1;
}
function buyNow(productId) {
    var qty = document.getElementById('qty').value;
    var form = document.getElementById('addToCartForm');
    var csrf = form.querySelector('input[name="csrf_token"]').value;
    var xhr = new XMLHttpRequest();
    xhr.open('POST', '<?= BASE_URL ?>/cart-update.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        window.location.href = '<?= BASE_URL ?>/checkout.php';
    };
    xhr.send('csrf_token=' + encodeURIComponent(csrf) + '&product_id=' + productId + '&quantity=' + qty + '&action=add');
}
document.querySelectorAll('.thumbnail-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var img = this.getAttribute('data-img');
        document.getElementById('main-product-image').src = img;
        document.querySelectorAll('.thumbnail-btn').forEach(function(b) {
            b.classList.remove('active');
            b.style.borderColor = '';
            b.style.borderWidth = '';
        });
        this.classList.add('active');
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
