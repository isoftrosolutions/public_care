<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/feature_helpers.php';
require_login();

$db = getDB();
$uid = (int)$_SESSION['user_id'];
$site_title = 'Payment';
$cart_items = fetch_cart_items($db, $uid);
$subtotal = 0;
$cart_count = 0;
foreach ($cart_items as $item) {
    $qty = (int)($item['quantity'] ?? 1);
    $cart_count += $qty;
    $subtotal += (float)$item['price'] * $qty;
}
$gst = round($subtotal * 0.05, 2);
$delivery = $subtotal > 499 || $subtotal == 0 ? 0 : 49;
$discount = 0;
$wallet_balance = 0;
if (table_exists($db, 'wallets')) {
    $stmt = $db->prepare('SELECT balance FROM wallets WHERE user_id = ?');
    $stmt->bind_param('i', $uid);
    $stmt->execute();
    $wallet_balance = (float)($stmt->get_result()->fetch_assoc()['balance'] ?? 0);
    $stmt->close();
} elseif (column_exists($db, 'users', 'wallet_balance')) {
    $wallet_balance = (float)(current_user($db)['wallet_balance'] ?? 0);
}

$error = '';
$success_order = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $error = 'Invalid form submission. Please refresh and try again.';
    } elseif (empty($cart_items)) {
        $error = 'Your cart is empty.';
    } elseif (($_POST['payment_method'] ?? '') === 'cod' && empty($_POST['cod_confirm'])) {
        $error = 'Please confirm that you will accept the cash-on-delivery package.';
    } else {
        foreach ($cart_items as $item) {
            if ((int)$item['stock'] < (int)$item['quantity']) {
                $error = $item['name'] . ' has insufficient stock.';
                break;
            }
        }
        if (!$error) {
            $use_wallet = isset($_POST['use_wallet']);
            $wallet_used = $use_wallet ? min($wallet_balance, $subtotal + $gst + $delivery - $discount) : 0;
            $total = max(0, $subtotal + $gst + $delivery - $discount - $wallet_used);
            $payment_method = $_POST['payment_method'] ?? 'upi';
            $order_num = 'ORD-' . strtoupper(bin2hex(random_bytes(4)));
            $ship_name = $_SESSION['user_name'] ?? 'Customer';
            $ship_phone = '';
            $ship_address = 'Address collected at checkout';
            $ship_city = '';
            $ship_zip = '';

            $has_enhanced_orders = column_exists($db, 'orders', 'discount_amount') && column_exists($db, 'orders', 'wallet_used') && column_exists($db, 'orders', 'gst_amount');
            if ($has_enhanced_orders) {
                $stmt = $db->prepare("INSERT INTO orders (user_id, order_number, total, tax, shipping, status, payment_status, shipping_name, shipping_phone, shipping_address, shipping_city, shipping_zip, payment_method, discount_amount, wallet_used, gst_amount) VALUES (?, ?, ?, ?, ?, 'pending', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $payment_status = $total <= 0 || $payment_method !== 'cod' ? 'paid' : 'pending';
                $stmt->bind_param('isdddsssssssddd', $uid, $order_num, $total, $gst, $delivery, $payment_status, $ship_name, $ship_phone, $ship_address, $ship_city, $ship_zip, $payment_method, $discount, $wallet_used, $gst);
            } else {
                $total = $subtotal + $gst + $delivery - $discount;
                $stmt = $db->prepare("INSERT INTO orders (user_id, order_number, total, tax, shipping, status, payment_status, shipping_name, shipping_phone, shipping_address, shipping_city, shipping_zip, payment_method) VALUES (?, ?, ?, ?, ?, 'pending', ?, ?, ?, ?, ?, ?, ?)");
                $payment_status = $payment_method === 'cod' ? 'pending' : 'paid';
                $stmt->bind_param('isdddsssssss', $uid, $order_num, $total, $gst, $delivery, $payment_status, $ship_name, $ship_phone, $ship_address, $ship_city, $ship_zip, $payment_method);
            }
            if ($stmt->execute()) {
                $order_id = $stmt->insert_id;
                foreach ($cart_items as $item) {
                    $qty = (int)$item['quantity'];
                    $price = (float)$item['price'];
                    $stmt2 = $db->prepare('INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)');
                    $stmt2->bind_param('iiid', $order_id, $item['product_id'], $qty, $price);
                    $stmt2->execute();
                    $stmt2->close();
                }
                $stmt3 = $db->prepare('DELETE FROM cart WHERE user_id = ?');
                $stmt3->bind_param('i', $uid);
                $stmt3->execute();
                $_SESSION['cart_count'] = 0;
                $success_order = $order_num;
                $cart_items = [];
            } else {
                $error = 'Payment could not be completed. Please retry.';
            }
            $stmt->close();
        }
    }
}

$total_due = max(0, $subtotal + $gst + $delivery - $discount);
require_once __DIR__ . '/includes/header.php';
?>
<section class="pt-32 pb-section-gap px-margin-mobile md:px-margin-desktop max-w-container-max mx-auto">
<?php if ($success_order): ?>
<div class="mx-auto max-w-2xl rounded-xl border border-primary-fixed bg-surface-container-lowest p-10 text-center shadow-lg">
    <div class="mx-auto mb-6 flex h-24 w-24 animate-pulse items-center justify-center rounded-full bg-primary-fixed text-primary">
        <span class="material-symbols-outlined text-6xl" style="font-variation-settings:'FILL' 1">check_circle</span>
    </div>
    <h1 class="font-display-lg text-display-lg text-primary">Payment Successful</h1>
    <p class="mt-3 text-on-surface-variant">Order <strong><?= h($success_order) ?></strong> is confirmed. Your invoice and tracking updates are ready.</p>
    <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
        <a href="<?= BASE_URL ?>/orders.php" class="rounded-lg bg-primary px-6 py-3 text-on-primary font-label-lg">View Order</a>
        <a href="<?= BASE_URL ?>/shop.php" class="rounded-lg border border-outline-variant px-6 py-3 font-label-lg text-primary">Continue Shopping</a>
    </div>
</div>
<?php elseif (empty($cart_items)): ?>
<?= empty_state('shopping_cart', 'Your cart is empty', 'Add medicines to your cart before opening payment.', 'Start Shopping', BASE_URL . '/shop.php') ?>
<?php else: ?>
<div class="mb-8 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
    <div>
        <div class="flex items-center gap-3 text-primary"><span class="material-symbols-outlined">lock</span><span class="text-label-lg">Secured by 256-bit SSL</span></div>
        <h1 class="mt-2 font-display-lg text-display-lg text-primary">Payment</h1>
    </div>
    <div class="flex items-center gap-2 text-label-lg text-on-surface-variant"><span>Address</span><span>→</span><span class="rounded-full bg-primary px-3 py-1 text-on-primary">Payment</span><span>→</span><span>Confirmation</span></div>
</div>
<?php if ($error): ?><div class="mb-6 rounded-lg bg-error-container p-4 text-on-error-container"><?= h($error) ?></div><?php endif; ?>
<form method="post" class="grid gap-8 lg:grid-cols-[1fr_380px]">
    <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
    <div class="space-y-5">
        <?php
        $methods = [
            'wallet' => ['account_balance_wallet', 'Wallet', 'Pay using available Ayurviro wallet balance'],
            'upi' => ['qr_code_2', 'UPI', 'Verify and pay using any UPI app'],
            'card' => ['credit_card', 'Credit / Debit Card', 'Visa, Mastercard, RuPay and Maestro'],
            'netbanking' => ['account_balance', 'Net Banking', 'Popular Indian banks supported'],
            'paylater' => ['schedule', 'Pay Later', 'Simpl, LazyPay, Amazon Pay Later and EMI'],
            'cod' => ['local_shipping', 'Cash on Delivery', 'Pay when your package arrives'],
        ];
        foreach ($methods as $key => $method):
        ?>
        <label class="block rounded-xl border border-outline-variant bg-surface-container-lowest p-5 transition hover:border-primary has-[:checked]:border-primary has-[:checked]:bg-primary-fixed/20">
            <div class="flex items-center gap-4">
                <input class="text-primary focus:ring-primary" type="radio" name="payment_method" value="<?= h($key) ?>" <?= $key === 'upi' ? 'checked' : '' ?>>
                <span class="material-symbols-outlined rounded-lg bg-primary-fixed p-2 text-primary"><?= h($method[0]) ?></span>
                <div class="flex-1"><p class="font-title-lg text-title-lg"><?= h($method[1]) ?></p><p class="text-on-surface-variant"><?= h($method[2]) ?></p></div>
                <span class="material-symbols-outlined text-on-surface-variant">chevron_right</span>
            </div>
            <?php if ($key === 'upi'): ?>
            <div class="mt-5 grid gap-4 md:grid-cols-[1fr_180px]">
                <div>
                    <label class="text-label-sm text-on-surface-variant">UPI ID</label>
                    <input class="mt-1 w-full rounded-lg border-outline-variant focus:border-primary focus:ring-primary" name="upi_id" placeholder="name@upi">
                    <div class="mt-3 flex flex-wrap gap-2 text-label-sm"><span class="rounded-full bg-surface-container px-3 py-1">Google Pay</span><span class="rounded-full bg-surface-container px-3 py-1">PhonePe</span><span class="rounded-full bg-surface-container px-3 py-1">Paytm</span><span class="rounded-full bg-surface-container px-3 py-1">BHIM</span></div>
                </div>
                <div class="flex min-h-36 items-center justify-center rounded-lg border-2 border-dashed border-outline-variant text-center text-on-surface-variant"><span>Scan any<br>UPI app</span></div>
            </div>
            <?php elseif ($key === 'card'): ?>
            <div class="mt-5 grid gap-3 md:grid-cols-2">
                <input class="md:col-span-2 rounded-lg border-outline-variant focus:border-primary focus:ring-primary" maxlength="19" data-card-format placeholder="Card number">
                <input class="rounded-lg border-outline-variant focus:border-primary focus:ring-primary" placeholder="MM/YY">
                <input class="rounded-lg border-outline-variant focus:border-primary focus:ring-primary" placeholder="CVV">
                <input class="md:col-span-2 rounded-lg border-outline-variant focus:border-primary focus:ring-primary" placeholder="Cardholder name">
                <label class="md:col-span-2 flex items-center gap-2 text-on-surface-variant"><input type="checkbox" class="rounded text-primary"> Save card for future</label>
            </div>
            <?php elseif ($key === 'netbanking'): ?>
            <div class="mt-5 flex flex-wrap gap-2"><span class="rounded-lg bg-surface-container px-4 py-2">SBI</span><span class="rounded-lg bg-surface-container px-4 py-2">HDFC</span><span class="rounded-lg bg-surface-container px-4 py-2">ICICI</span><span class="rounded-lg bg-surface-container px-4 py-2">Axis</span><select class="rounded-lg border-outline-variant"><option>Other Banks</option></select></div>
            <?php elseif ($key === 'cod'): ?>
            <label class="mt-5 flex items-center gap-2 text-on-surface-variant"><input type="checkbox" name="cod_confirm" class="rounded text-primary"> I confirm I will accept delivery. COD available up to ₹5000.</label>
            <?php endif; ?>
        </label>
        <?php endforeach; ?>
        <div class="rounded-xl border border-outline-variant bg-surface-container-lowest p-4 text-center text-on-surface-variant"><span class="material-symbols-outlined align-middle text-primary">verified_user</span> Your payment info is encrypted and never stored.</div>
    </div>
    <aside class="h-fit rounded-xl border border-outline-variant bg-surface-container-lowest p-6 lg:sticky lg:top-28">
        <h2 class="font-headline-md text-headline-md text-primary">Order Summary</h2>
        <div class="mt-5 space-y-4">
            <?php foreach (array_slice($cart_items, 0, 3) as $item): ?>
            <div class="flex gap-3">
                <img class="h-14 w-14 rounded-lg object-cover bg-surface-container" src="<?= h($item['image_url'] ?: 'assets/uploads/logo.jpeg') ?>" alt="<?= h($item['name']) ?>">
                <div class="flex-1"><p class="font-label-lg"><?= h($item['name']) ?></p><p class="text-label-sm text-on-surface-variant">Qty <?= (int)$item['quantity'] ?></p></div>
                <p class="font-label-lg text-primary"><?= money((float)$item['price'] * (int)$item['quantity']) ?></p>
            </div>
            <?php endforeach; ?>
            <?php if (count($cart_items) > 3): ?><p class="text-label-sm text-on-surface-variant">and <?= count($cart_items) - 3 ?> more items</p><?php endif; ?>
        </div>
        <div class="mt-6 border-t border-outline-variant pt-4 space-y-3 text-on-surface-variant">
            <div class="flex justify-between"><span>Subtotal</span><span><?= money($subtotal) ?></span></div>
            <div class="flex justify-between"><span>Delivery</span><span><?= $delivery ? money($delivery) : 'FREE' ?></span></div>
            <div class="flex justify-between"><span>GST</span><span><?= money($gst) ?></span></div>
            <div class="flex gap-2"><input name="coupon" class="min-w-0 flex-1 rounded-lg border-outline-variant" placeholder="Coupon code"><button type="button" class="rounded-lg border border-primary px-4 text-primary">Apply</button></div>
            <label class="flex items-center justify-between rounded-lg bg-surface-container p-3"><span>Pay from wallet <strong><?= money($wallet_balance) ?></strong></span><input type="checkbox" name="use_wallet" class="rounded text-primary"></label>
            <div class="flex justify-between border-t border-outline-variant pt-4 font-headline-md text-headline-md text-primary"><span>Total</span><span><?= money($total_due) ?></span></div>
        </div>
        <button name="place_order" class="mt-6 w-full rounded-lg bg-primary py-3 font-label-lg text-on-primary hover:bg-primary-container">Place Order</button>
        <p class="mt-3 text-center text-label-sm text-primary">Save 5 min — Pay via UPI</p>
    </aside>
</form>
<?php endif; ?>
</section>
<script>
document.querySelector('[data-card-format]')?.addEventListener('input', function () {
    this.value = this.value.replace(/\D/g, '').slice(0, 16).replace(/(.{4})/g, '$1 ').trim();
});
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
