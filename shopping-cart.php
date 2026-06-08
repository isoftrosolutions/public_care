<?php
require_once __DIR__ . '/includes/config.php';
$site_title = 'Your Shopping Bag';
require_once __DIR__ . '/includes/header.php';

$cart_items = [];
$cart_total = 0;
$cart_count = 0;

if (isset($_SESSION['user_id'])) {
    $uid = (int)$_SESSION['user_id'];
    $result = getDB()->query("SELECT c.*, p.name, p.price, p.image_url, p.compare_price FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = $uid");
    $cart_items = $result->fetch_all(MYSQLI_ASSOC);
} elseif (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    $ids = implode(',', array_keys($_SESSION['cart']));
    if ($ids) {
        $result = getDB()->query("SELECT * FROM products WHERE id IN ($ids)");
        foreach ($result->fetch_all(MYSQLI_ASSOC) as $p) {
            $p['quantity'] = $_SESSION['cart'][$p['id']];
            $p['user_id'] = null;
            $cart_items[] = $p;
        }
    }
}

foreach ($cart_items as $item) {
    $qty = $item['quantity'] ?? 1;
    $cart_count += $qty;
    $cart_total += $item['price'] * $qty;
}
$_SESSION['cart_count'] = $cart_count;
?>

<section class="pt-32 pb-section-gap px-margin-mobile md:px-margin-desktop max-w-container-max mx-auto">
<div class="grid grid-cols-1 lg:grid-cols-12 gap-10" id="cart-container">
<section class="lg:col-span-8">
<div class="mb-8">
<h1 class="font-headline-lg text-headline-lg text-primary mb-2">Your Shopping Bag</h1>
<p class="font-body-md text-on-surface-variant">You have <span class="font-bold" id="cart-count"><?= $cart_count ?></span> items in your bag</p>
</div>

<?php if (empty($cart_items)): ?>
<div class="text-center py-20">
<div class="w-64 h-64 mx-auto mb-8 bg-tertiary-fixed rounded-full flex items-center justify-center overflow-hidden">
<img class="w-full h-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuBOeXfZoOZeVaT3ZH9MZetHAs9S-_JYiYWXIRbTKvJvEx-_uzJHCoW5CQQ3O7-EpOSccigLDqggyv9pEbgnheGtbq-9vLHEetEd151RVcLC5ZngMJfYEAUwkG6dO7Rya7WWI7hkY_cZbEpuJSzVoxAFOAqeonn3FrwAaVBmS7_IbJHyk45V8mMQk_8B0JVkG-O122GXKMe3fLyHWSgiIjFHq_YwAjfRm2UkhnbyWYj1JTfCk-AS71LjEYN79eyZa2NNBn_BWlc_Thw"/>
</div>
<h2 class="font-headline-lg text-headline-lg text-primary mb-4">Your cart is empty</h2>
<p class="font-body-lg text-on-surface-variant mb-10">Let's find something for your health journey.</p>
<a class="inline-block bg-primary text-on-primary px-10 py-4 rounded-full font-label-md hover:bg-primary-container transition-all" href="<?= BASE_URL ?>/shop.php">Continue Shopping</a>
</div>
<?php else: ?>
<div class="flex flex-col gap-gutter">
<?php foreach ($cart_items as $item): ?>
<?php $qty = $item['quantity'] ?? 1; $subtotal = $item['price'] * $qty; ?>
<div class="cart-item-card bg-surface-container-lowest border border-outline-variant p-6 rounded-xl flex flex-col sm:flex-row gap-6 transition-all">
<div class="w-full sm:w-32 h-32 bg-surface-container rounded-lg overflow-hidden flex-shrink-0">
<img class="w-full h-full object-cover" src="<?= htmlspecialchars($item['image_url']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
</div>
<div class="flex-grow flex flex-col justify-between">
<div class="flex justify-between items-start">
<div>
<h3 class="font-headline-md text-headline-md text-primary"><?= htmlspecialchars($item['name']) ?></h3>
</div>
<p class="font-headline-md text-headline-md text-primary">₹<?= number_format($item['price'], 2) ?></p>
</div>
<div class="flex flex-wrap items-center justify-between mt-6 gap-4">
<div class="flex items-center border border-outline rounded-full px-2 py-1">
<a href="<?= BASE_URL ?>/cart-update.php?action=remove&id=<?= $item['product_id'] ?>" class="p-1 hover:text-primary transition-colors"><span class="material-symbols-outlined">remove</span></a>
<span class="px-4 font-bold text-primary"><?= $qty ?></span>
<a href="<?= BASE_URL ?>/cart-update.php?action=add&id=<?= $item['product_id'] ?>" class="p-1 hover:text-primary transition-colors"><span class="material-symbols-outlined">add</span></a>
</div>
<a href="<?= BASE_URL ?>/cart-update.php?action=delete&id=<?= $item['product_id'] ?>" class="flex items-center gap-1 text-label-md text-error hover:opacity-80 transition-opacity">
<span class="material-symbols-outlined text-[18px]">delete</span> Remove
</a>
</div>
</div>
</div>
<?php endforeach; ?>
</div>
<div class="mt-12 grid grid-cols-1 md:grid-cols-3 gap-6">
<div class="flex items-start gap-3 p-4 bg-surface-container-low rounded-lg">
<span class="material-symbols-outlined text-primary-fixed-variant">verified_user</span>
<div><h4 class="font-label-md text-label-md text-primary">Secure Checkout</h4><p class="text-label-sm text-on-surface-variant">SSL encrypted payment</p></div>
</div>
<div class="flex items-start gap-3 p-4 bg-surface-container-low rounded-lg">
<span class="material-symbols-outlined text-primary-fixed-variant">local_shipping</span>
<div><h4 class="font-label-md text-label-md text-primary">Free Shipping</h4><p class="text-label-sm text-on-surface-variant">On orders over ₹1500</p></div>
</div>
<div class="flex items-start gap-3 p-4 bg-surface-container-low rounded-lg">
<span class="material-symbols-outlined text-primary-fixed-variant">assignment_return</span>
<div><h4 class="font-label-md text-label-md text-primary">Easy Returns</h4><p class="text-label-sm text-on-surface-variant">Hassle-free 30-day policy</p></div>
</div>
</div>
<?php endif; ?>
</section>

<?php if (!empty($cart_items)): ?>
<aside class="lg:col-span-4">
<div class="sticky top-[100px] bg-surface-container-lowest border border-outline-variant rounded-xl p-8 shadow-sm">
<h2 class="font-headline-md text-headline-md text-primary mb-6">Order Summary</h2>
<div class="space-y-4 mb-8">
<div class="flex justify-between items-center text-body-md text-on-surface-variant">
<span>Subtotal (<?= $cart_count ?> items)</span>
<span>₹<?= number_format($cart_total, 2) ?></span>
</div>
<div class="flex justify-between items-center text-body-md text-on-surface-variant">
<span>Shipping Estimate</span>
<span class="text-on-tertiary-container font-medium">Calculated at next step</span>
</div>
<div class="border-t border-outline-variant pt-4 flex justify-between items-center">
<span class="font-headline-md text-headline-md text-primary">Total</span>
<span class="font-display-lg text-headline-lg text-primary">₹<?= number_format($cart_total, 2) ?></span>
</div>
</div>
<a href="<?= BASE_URL ?>/checkout.php" class="block w-full bg-primary text-on-primary py-4 rounded-full font-label-md text-center hover:bg-primary-container hover:shadow-lg transform active:scale-95 transition-all mb-6">
Proceed to Checkout <span class="material-symbols-outlined align-middle">arrow_forward</span>
</a>
<div class="text-center">
<p class="text-label-sm text-on-surface-variant mb-4 uppercase tracking-widest">We Accept</p>
<div class="flex justify-center gap-4 opacity-60">
<span class="material-symbols-outlined text-3xl">credit_card</span>
<span class="material-symbols-outlined text-3xl">account_balance</span>
<span class="material-symbols-outlined text-3xl">contactless</span>
<span class="material-symbols-outlined text-3xl">payments</span>
</div>
</div>
</div>
</aside>
<?php endif; ?>
</div>
</section>

<style>
.cart-item-card:hover { box-shadow: 0 4px 20px -5px rgba(27, 67, 50, 0.08); }
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
