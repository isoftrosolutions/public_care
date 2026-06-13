<?php
require_once __DIR__ . '/includes/config.php';
$site_title = 'Shopping Cart';
require_once __DIR__ . '/includes/header.php';

$cart_items = [];
$cart_total = 0;
$cart_count = 0;

if (isset($_SESSION['user_id'])) {
    $uid = (int)$_SESSION['user_id'];
    $result = getDB()->query("SELECT c.*, p.name, p.price, p.image_url, p.compare_price FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = $uid");
    $cart_items = $result->fetch_all(MYSQLI_ASSOC);
} elseif (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    $ids = implode(',', array_map('intval', array_keys($_SESSION['cart'])));
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

<main class="max-w-container-max mx-auto px-base md:px-margin-desktop py-12">

<div class="flex flex-col gap-4 mb-8">
    <nav class="flex items-center gap-2 text-on-surface-variant text-label-sm font-label-sm">
        <a class="hover:text-primary transition-colors" href="<?= BASE_URL ?>">Home</a>
        <span class="material-symbols-outlined text-[16px]">chevron_right</span>
        <span class="text-on-surface">Your Shopping Cart</span>
    </nav>
    <h1 class="font-display-lg text-headline-lg md:text-display-lg text-on-surface">Your Shopping Cart</h1>
</div>

<?php if (empty($cart_items)): ?>

<div class="text-center py-20">
    <div class="w-40 h-40 mx-auto mb-8 bg-surface-container-low rounded-full flex items-center justify-center">
        <span class="material-symbols-outlined text-[64px] text-outline">shopping_cart</span>
    </div>
    <h2 class="font-headline-md text-headline-md text-on-surface mb-3">Your cart is empty</h2>
    <p class="text-body-lg text-on-surface-variant mb-8">Looks like you haven't added anything yet. Let's find something for your wellness journey.</p>
    <a class="inline-block bg-primary text-white font-label-lg px-8 py-4 rounded-lg hover:bg-primary-container hover:scale-[1.02] active:scale-[0.98] transition-all" href="<?= BASE_URL ?>/shop.php">Continue Shopping</a>
</div>

<?php else: ?>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-gutter items-start">

    <section class="lg:col-span-8 flex flex-col gap-6">

        <?php foreach ($cart_items as $item): ?>
        <?php $qty = $item['quantity'] ?? 1; $item_id = $item['product_id'] ?? $item['id']; ?>
        <div class="cart-card-shadow bg-surface-container-lowest rounded-xl p-6 flex flex-col sm:flex-row gap-6 items-center transition-all hover:shadow-[0px_8px_30px_rgba(0,0,0,0.08)]">
            <div class="w-32 h-32 rounded-lg bg-surface-container-low flex-shrink-0 overflow-hidden">
                <img class="w-full h-full object-cover" src="<?= htmlspecialchars($item['image_url'] ?? '') ?>" alt="<?= htmlspecialchars($item['name']) ?>">
            </div>
            <div class="flex-grow flex flex-col gap-1 text-center sm:text-left">
                <h3 class="font-title-lg text-title-lg text-on-surface"><?= htmlspecialchars($item['name']) ?></h3>
                <div class="mt-4 flex items-center justify-center sm:justify-start gap-4">
                    <div class="flex items-center border border-outline-variant rounded-lg">
                        <a href="<?= BASE_URL ?>/cart-update.php?action=remove&id=<?= $item_id ?>" class="px-3 py-1 hover:bg-surface-container-low text-primary transition-all rounded-l-lg">-</a>
                        <span class="px-4 py-1 border-x border-outline-variant font-label-lg text-label-lg min-w-[40px] text-center"><?= $qty ?></span>
                        <a href="<?= BASE_URL ?>/cart-update.php?action=add&id=<?= $item_id ?>" class="px-3 py-1 hover:bg-surface-container-low text-primary transition-all rounded-r-lg">+</a>
                    </div>
                    <a href="<?= BASE_URL ?>/cart-update.php?action=delete&id=<?= $item_id ?>" class="text-error text-label-lg font-label-lg hover:underline transition-all flex items-center gap-1">
                        <span class="material-symbols-outlined text-[18px]">delete</span>
                        Remove
                    </a>
                </div>
            </div>
            <div class="text-right flex-shrink-0">
                <p class="font-headline-md text-headline-md text-primary">₹<?= number_format($item['price'], 2) ?></p>
                <?php if (!empty($item['compare_price'])): ?>
                <p class="text-outline text-label-sm line-through">₹<?= number_format($item['compare_price'], 2) ?></p>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>

        <div class="mt-4">
            <a class="text-primary font-label-lg text-label-lg flex items-center gap-2 hover:translate-x-[-4px] transition-transform w-fit" href="<?= BASE_URL ?>/shop.php">
                <span class="material-symbols-outlined">arrow_back</span>
                Continue Shopping
            </a>
        </div>

    </section>

    <aside class="lg:col-span-4">
        <div class="cart-card-shadow bg-surface-container-lowest rounded-xl p-8 flex flex-col gap-6 sticky top-28">
            <h2 class="font-headline-md text-headline-md text-on-surface">Order Summary</h2>
            <div class="flex flex-col gap-4 py-4 border-y border-outline-variant">
                <div class="flex justify-between items-center text-body-lg font-body-lg">
                    <span class="text-on-surface-variant">Subtotal (<?= $cart_count ?> items)</span>
                    <span class="text-on-surface">₹<?= number_format($cart_total, 2) ?></span>
                </div>
                <div class="flex justify-between items-center text-body-lg font-body-lg">
                    <span class="text-on-surface-variant">Shipping</span>
                    <span class="text-primary font-semibold">FREE</span>
                </div>
                <div class="flex justify-between items-center text-body-lg font-body-lg">
                    <span class="text-on-surface-variant">Estimated Tax</span>
                    <span class="text-on-surface">₹0.00</span>
                </div>
            </div>
            <div class="flex justify-between items-center text-headline-md font-headline-md py-2">
                <span class="text-on-surface">Total Amount</span>
                <span class="text-primary">₹<?= number_format($cart_total, 2) ?></span>
            </div>
            <a href="<?= BASE_URL ?>/checkout.php" class="w-full py-4 bg-primary text-white font-headline-md rounded-lg shadow-lg hover:bg-primary-container hover:scale-[1.01] active:scale-[0.98] transition-all flex items-center justify-center gap-2">
                Proceed to Checkout
                <span class="material-symbols-outlined">arrow_forward</span>
            </a>
            <div class="flex items-center justify-center gap-2 py-2 text-on-surface-variant text-label-sm font-label-sm border-t border-outline-variant pt-6">
                <span class="material-symbols-outlined text-[18px]">verified_user</span>
                Secure Checkout Guaranteed
            </div>
            <div class="flex justify-center gap-4 opacity-50">
                <span class="material-symbols-outlined text-[32px]">credit_card</span>
                <span class="material-symbols-outlined text-[32px]">payments</span>
                <span class="material-symbols-outlined text-[32px]">account_balance_wallet</span>
            </div>
        </div>
    </aside>

</div>

<?php endif; ?>

</main>

<style>
.cart-card-shadow { box-shadow: 0px 4px 20px rgba(0, 0, 0, 0.05); }
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
