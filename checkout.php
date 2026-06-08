<?php
require_once __DIR__ . '/includes/config.php';
$site_title = 'Checkout';
require_once __DIR__ . '/includes/header.php';

$cart_items = [];
$cart_total = 0;
$cart_count = 0;

if (isset($_SESSION['user_id'])) {
    $uid = (int)$_SESSION['user_id'];
    $result = getDB()->query("SELECT c.*, p.name, p.price, p.image_url FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = $uid");
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

$order_placed = false;
$order_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    if (empty($cart_items)) {
        $order_error = 'Your cart is empty.';
    } elseif (!isset($_SESSION['user_id'])) {
        $order_error = 'Please login to place an order.';
    } else {
        $db = getDB();
        $uid = (int)$_SESSION['user_id'];
        $order_num = 'ORD-' . strtoupper(bin2hex(random_bytes(4)));
        $stmt = $db->prepare("INSERT INTO orders (user_id, order_number, total, status, payment_status) VALUES (?, ?, ?, 'pending', 'pending')");
        $stmt->bind_param('isd', $uid, $order_num, $cart_total);
        if ($stmt->execute()) {
            $order_id = $stmt->insert_id;
            foreach ($cart_items as $item) {
                $qty = $item['quantity'] ?? 1;
                $stmt2 = $db->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
                $stmt2->bind_param('iiid', $order_id, $item['product_id'], $qty, $item['price']);
                $stmt2->execute();
            }
            $db->query("DELETE FROM cart WHERE user_id = $uid");
            unset($_SESSION['cart']);
            $_SESSION['cart_count'] = 0;
            $order_placed = true;
        } else {
            $order_error = 'Failed to place order. Please try again.';
        }
    }
}
?>

<section class="pt-32 pb-section-gap px-margin-desktop max-w-container-max mx-auto">
<?php if ($order_placed): ?>
<div class="text-center py-20">
<div class="w-24 h-24 bg-primary-fixed rounded-full flex items-center justify-center mx-auto mb-8">
<span class="material-symbols-outlined text-5xl text-primary" style="font-variation-settings: 'FILL' 1;">check_circle</span>
</div>
<h2 class="text-display-lg font-display-lg text-primary mb-4">Order Placed Successfully!</h2>
<p class="text-body-lg text-on-surface-variant mb-10">Your order number is <strong><?= htmlspecialchars($order_num) ?></strong>. We'll send you a confirmation shortly.</p>
<a href="<?= BASE_URL ?>/shop.php" class="inline-block bg-primary text-on-primary px-10 py-4 rounded-full font-label-md">Continue Shopping</a>
</div>
<?php else: ?>

<?php if ($order_error): ?>
<div class="p-4 bg-error-container text-on-error-container rounded-xl mb-8"><?= htmlspecialchars($order_error) ?></div>
<?php endif; ?>

<?php if (empty($cart_items)): ?>
<div class="text-center py-20">
<h2 class="font-headline-lg text-headline-lg text-primary mb-4">Your cart is empty</h2>
<a href="<?= BASE_URL ?>/shop.php" class="inline-block bg-primary text-on-primary px-10 py-4 rounded-full font-label-md">Shop Now</a>
</div>
<?php else: ?>
<div class="grid grid-cols-1 lg:grid-cols-12 gap-gutter items-start">
<div class="lg:col-span-8 space-y-6">
<section class="bg-surface-container-lowest p-8 rounded-xl shadow-sm border border-outline-variant">
<h2 class="font-headline-md text-headline-md text-primary mb-6">Shipping Address</h2>
<form method="POST">
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
<div class="flex flex-col gap-2">
<label class="font-label-md text-label-md text-on-surface-variant">Full Name</label>
<input name="full_name" class="bg-surface border-outline-variant rounded-lg p-3 text-body-md focus:ring-primary focus:border-primary" placeholder="Full Name" type="text" required>
</div>
<div class="flex flex-col gap-2">
<label class="font-label-md text-label-md text-on-surface-variant">Phone Number</label>
<input name="phone" class="bg-surface border-outline-variant rounded-lg p-3 text-body-md" placeholder="Phone" type="tel" required>
</div>
<div class="md:col-span-2 flex flex-col gap-2">
<label class="font-label-md text-label-md text-on-surface-variant">Street Address</label>
<input name="address" class="bg-surface border-outline-variant rounded-lg p-3 text-body-md" placeholder="House no, Street name" type="text" required>
</div>
<div class="flex flex-col gap-2">
<label class="font-label-md text-label-md text-on-surface-variant">City</label>
<input name="city" class="bg-surface border-outline-variant rounded-lg p-3 text-body-md" placeholder="City" type="text" required>
</div>
<div class="flex flex-col gap-2">
<label class="font-label-md text-label-md text-on-surface-variant">Postal Code</label>
<input name="postal_code" class="bg-surface border-outline-variant rounded-lg p-3 text-body-md" placeholder="Postal Code" type="text">
</div>
</div>
<div class="mt-8">
<button type="submit" name="place_order" class="w-full bg-primary text-on-primary py-4 rounded-full font-label-md hover:bg-primary-container transition-all text-lg">Place Order — ₹<?= number_format($cart_total, 2) ?></button>
</div>
</form>
</section>
<div class="flex flex-wrap items-center justify-center md:justify-start gap-8 opacity-60 mt-8">
<div class="flex items-center gap-2"><span class="material-symbols-outlined text-primary">verified_user</span><span class="font-label-sm">SSL Secure Payment</span></div>
<div class="flex items-center gap-2"><span class="material-symbols-outlined text-primary">lock</span><span class="font-label-sm">256-bit Encryption</span></div>
</div>
</div>
<aside class="lg:col-span-4 lg:sticky lg:top-32 space-y-6">
<div class="bg-surface-container-high p-8 rounded-xl">
<h3 class="font-headline-md text-headline-md text-primary mb-6">Order Summary</h3>
<div class="space-y-4 mb-6 border-b border-outline-variant pb-6">
<?php foreach ($cart_items as $item): ?>
<?php $qty = $item['quantity'] ?? 1; ?>
<div class="flex justify-between text-body-md"><span class="text-on-surface-variant"><?= htmlspecialchars($item['name']) ?> x<?= $qty ?></span><span class="font-bold">₹<?= number_format($item['price'] * $qty, 2) ?></span></div>
<?php endforeach; ?>
</div>
<div class="flex justify-between items-center">
<span class="font-headline-md text-headline-md text-primary">Total</span>
<span class="font-headline-md text-headline-md text-primary">₹<?= number_format($cart_total, 2) ?></span>
</div>
</div>
</aside>
</div>
<?php endif; ?>
<?php endif; ?>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
