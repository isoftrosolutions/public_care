<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/feature_helpers.php';
require_once __DIR__ . '/includes/invoice-helper.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = BASE_URL . '/checkout.php';
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$site_title = 'Checkout';

$cart_items = [];
$cart_total = 0;
$cart_count = 0;

$uid = (int)$_SESSION['user_id'];
$db = getDB();
$result = $db->query("SELECT c.*, p.name, p.price, p.image_url, p.stock FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = $uid");
$cart_items = $result->fetch_all(MYSQLI_ASSOC);

$user = current_user($db);
$saved_name = (string)($user['full_name'] ?? ($_SESSION['user_name'] ?? ''));
$saved_phone = (string)($user['mobile'] ?? '');
$saved_address = $saved_city = $saved_zip = '';
$has_shipping_columns = column_exists($db, 'orders', 'shipping_name')
    && column_exists($db, 'orders', 'shipping_phone')
    && column_exists($db, 'orders', 'shipping_address')
    && column_exists($db, 'orders', 'shipping_city')
    && column_exists($db, 'orders', 'shipping_zip');

if (table_exists($db, 'saved_addresses')) {
    $stmt = $db->prepare("SELECT full_name, phone, address_line1, address_line2, city, pincode FROM saved_addresses WHERE user_id = ? ORDER BY is_default DESC, id DESC LIMIT 1");
    $stmt->bind_param('i', $uid);
    $stmt->execute();
    $saved_address_row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($saved_address_row) {
        $saved_name = $saved_address_row['full_name'] ?: $saved_name;
        $saved_phone = $saved_address_row['phone'] ?: $saved_phone;
        $saved_address = trim((string)$saved_address_row['address_line1'] . "\n" . (string)($saved_address_row['address_line2'] ?? ''));
        $saved_city = (string)$saved_address_row['city'];
        $saved_zip = (string)$saved_address_row['pincode'];
    }
}

if ($has_shipping_columns && $saved_address === '') {
    $last_order = $db->query("SELECT shipping_name, shipping_phone, shipping_address, shipping_city, shipping_zip FROM orders WHERE user_id = $uid AND shipping_name != '' ORDER BY id DESC LIMIT 1")->fetch_assoc();
    if ($last_order) {
        $saved_name = $last_order['shipping_name'] ?: $saved_name;
        $saved_phone = $last_order['shipping_phone'] ?: $saved_phone;
        $saved_address = (string)$last_order['shipping_address'];
        $saved_city = (string)$last_order['shipping_city'];
        $saved_zip = (string)$last_order['shipping_zip'];
    }
}

foreach ($cart_items as $item) {
    $qty = $item['quantity'] ?? 1;
    $cart_count += $qty;
    $cart_total += $item['price'] * $qty;
}
$_SESSION['cart_count'] = $cart_count;

$order_placed = false;
$order_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $order_error = 'Invalid form submission. Please try again.';
    } elseif (empty($cart_items)) {
        $order_error = 'Your cart is empty.';
    } else {
        foreach ($cart_items as $item) {
            $qty = $item['quantity'] ?? 1;
            if ((int)$item['stock'] < $qty) {
                $order_error = htmlspecialchars($item['name']) . ' has insufficient stock. Only ' . (int)$item['stock'] . ' available.';
                break;
            }
        }
        if (!$order_error) {
            $shipping_name = trim($_POST['full_name'] ?? '');
            $shipping_phone = trim($_POST['phone'] ?? '');
            $shipping_address = trim($_POST['address'] ?? '');
            $shipping_city = trim($_POST['city'] ?? '');
            $shipping_zip = trim($_POST['postal_code'] ?? '');
            $payment_method = trim($_POST['payment'] ?? 'cod');

            if ($shipping_name === '' || $shipping_phone === '' || $shipping_address === '' || $shipping_city === '') {
                $order_error = 'Please complete your delivery address before placing the order.';
            }
        }
        if (!$order_error) {
            $order_num = 'ORD-' . strtoupper(bin2hex(random_bytes(4)));
            if ($has_shipping_columns) {
                $stmt = $db->prepare("INSERT INTO orders (user_id, order_number, total, status, payment_status, shipping_name, shipping_phone, shipping_address, shipping_city, shipping_zip, payment_method) VALUES (?, ?, ?, 'pending', 'pending', ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param('isdssssss', $uid, $order_num, $cart_total, $shipping_name, $shipping_phone, $shipping_address, $shipping_city, $shipping_zip, $payment_method);
            } else {
                $stmt = $db->prepare("INSERT INTO orders (user_id, order_number, total, status, payment_status, payment_method) VALUES (?, ?, ?, 'pending', 'pending', ?)");
                $stmt->bind_param('isds', $uid, $order_num, $cart_total, $payment_method);
            }
            if ($stmt->execute()) {
                $order_id = $stmt->insert_id;
                foreach ($cart_items as $item) {
                    $qty = $item['quantity'] ?? 1;
                    $stmt2 = $db->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
                    $stmt2->bind_param('iiid', $order_id, $item['product_id'], $qty, $item['price']);
                    $stmt2->execute();
                }
                generate_and_email_order_invoice($db, $order_id);
                if (table_exists($db, 'saved_addresses') && $shipping_address !== '' && $shipping_city !== '') {
                    $stmt4 = $db->prepare("SELECT COUNT(*) FROM saved_addresses WHERE user_id = ? AND address_line1 = ? AND city = ? AND pincode = ?");
                    $stmt4->bind_param('isss', $uid, $shipping_address, $shipping_city, $shipping_zip);
                    $stmt4->execute();
                    $address_exists = (int)$stmt4->get_result()->fetch_row()[0] > 0;
                    $stmt4->close();
                    if (!$address_exists) {
                        $default_count_result = $db->query("SELECT COUNT(*) FROM saved_addresses WHERE user_id = $uid AND is_default = 1");
                        $is_default = ((int)$default_count_result->fetch_row()[0]) === 0 ? 1 : 0;
                        $address_label = 'Home';
                        $address_line2 = '';
                        $stmt5 = $db->prepare("INSERT INTO saved_addresses (user_id, label, full_name, phone, address_line1, address_line2, city, pincode, is_default) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                        $stmt5->bind_param('isssssssi', $uid, $address_label, $shipping_name, $shipping_phone, $shipping_address, $address_line2, $shipping_city, $shipping_zip, $is_default);
                        $stmt5->execute();
                        $stmt5->close();
                    }
                }
                $stmt3 = $db->prepare("DELETE FROM cart WHERE user_id = ?");
                $stmt3->bind_param('i', $uid);
                $stmt3->execute();
                unset($_SESSION['cart']);
                $_SESSION['cart_count'] = 0;
                $_SESSION['order_success'] = $order_num;
                header('Location: ' . BASE_URL . '/checkout.php?order=success');
                exit;
            } else {
                error_log('Checkout order insert failed: ' . $stmt->error);
                $order_error = 'Failed to place order. Please try again.';
            }
        }
    }
}

if (isset($_SESSION['order_success'])) {
    $order_placed = true;
    $order_num = $_SESSION['order_success'];
    unset($_SESSION['order_success']);
}

require_once __DIR__ . '/includes/header.php';
?>

<section class="pt-32 pb-section-gap px-margin-mobile md:px-margin-desktop max-w-container-max mx-auto">

<?php if ($order_placed): ?>
<div class="text-center py-20">
    <div class="w-24 h-24 bg-primary-fixed rounded-full flex items-center justify-center mx-auto mb-8">
        <span class="material-symbols-outlined text-5xl text-primary" style="font-variation-settings: 'FILL' 1;">check_circle</span>
    </div>
    <h2 class="font-display-lg text-display-lg text-primary mb-4">Order Placed Successfully!</h2>
    <p class="font-body-lg text-body-lg text-on-surface-variant mb-10">Your order number is <strong><?= htmlspecialchars($order_num) ?></strong>. We'll send you a confirmation shortly.</p>
    <a href="<?= BASE_URL ?>/shop.php" class="inline-block bg-primary text-on-primary px-10 py-4 rounded-full font-label-lg hover:shadow-lg transition-all">Continue Shopping</a>
</div>

<?php else: ?>

<?php if ($order_error): ?>
<div class="p-4 bg-error-container text-on-error-container rounded-xl mb-8 max-w-3xl mx-auto font-body-md text-body-md"><?= htmlspecialchars($order_error) ?></div>
<?php endif; ?>

<?php if (empty($cart_items)): ?>
<div class="text-center py-20">
    <div class="w-24 h-24 bg-surface-container-highest rounded-full flex items-center justify-center mx-auto mb-8">
        <span class="material-symbols-outlined text-4xl text-on-surface-variant">shopping_cart</span>
    </div>
    <h2 class="font-headline-lg text-headline-lg text-primary mb-4">Your cart is empty</h2>
    <p class="font-body-lg text-body-lg text-on-surface-variant mb-8">Add some products to your cart before checking out.</p>
    <a href="<?= BASE_URL ?>/shop.php" class="inline-block bg-primary text-on-primary px-10 py-4 rounded-full font-label-lg hover:shadow-lg transition-all">Shop Now</a>
</div>
<?php else: ?>

<div class="mb-12 max-w-3xl mx-auto">
    <div class="flex items-center justify-between relative">
        <div class="absolute top-1/2 left-0 w-full h-[2px] bg-surface-container-highest -z-10 -translate-y-1/2"></div>
        <div class="flex flex-col items-center gap-2">
            <div class="w-10 h-10 rounded-full flex items-center justify-center bg-primary text-on-primary ring-4 ring-primary-fixed shadow-lg transition-all" id="step-node-1">
                <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">local_shipping</span>
            </div>
            <span class="font-label-lg text-label-lg text-primary">Delivery</span>
        </div>
        <div class="flex flex-col items-center gap-2">
            <div class="w-10 h-10 rounded-full flex items-center justify-center bg-surface-container-highest text-on-surface-variant transition-all" id="step-node-2">
                <span class="material-symbols-outlined">payments</span>
            </div>
            <span class="font-label-lg text-label-lg text-on-surface-variant">Payment</span>
        </div>
        <div class="flex flex-col items-center gap-2">
            <div class="w-10 h-10 rounded-full flex items-center justify-center bg-surface-container-highest text-on-surface-variant transition-all" id="step-node-3">
                <span class="material-symbols-outlined">fact_check</span>
            </div>
            <span class="font-label-lg text-label-lg text-on-surface-variant">Review</span>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-gutter items-start">

<div class="lg:col-span-8">
<form method="POST" id="checkout-form" onkeydown="if(event.key==='Enter' && event.target.tagName!=='TEXTAREA')return false">
<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

<section class="step-transition bg-surface-container-lowest rounded-xl p-8 shadow-[0px_4px_20px_rgba(0,0,0,0.05)]" id="step-1">
    <h2 class="font-headline-md text-headline-md text-primary mb-8 flex items-center gap-3">
        <span class="material-symbols-outlined">location_on</span>
        Delivery Address
    </h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="flex flex-col gap-2">
            <label class="font-label-lg text-label-lg text-on-surface-variant">Full Name</label>
            <input name="full_name" value="<?= h($saved_name) ?>" class="w-full px-4 py-3 rounded-lg border border-outline-variant focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all bg-surface" placeholder="e.g. Rahul Sharma" required type="text" autocomplete="name">
        </div>
        <div class="flex flex-col gap-2">
            <label class="font-label-lg text-label-lg text-on-surface-variant">Phone Number</label>
            <input name="phone" value="<?= h($saved_phone) ?>" class="w-full px-4 py-3 rounded-lg border border-outline-variant focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all bg-surface" placeholder="+91 98765 43210" required type="tel" autocomplete="tel">
        </div>
        <div class="flex flex-col gap-2 md:col-span-2">
            <label class="font-label-lg text-label-lg text-on-surface-variant">Street Address</label>
            <textarea name="address" class="w-full px-4 py-3 rounded-lg border border-outline-variant focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all bg-surface" placeholder="Flat/House No., Building, Apartment" required rows="3" autocomplete="street-address"><?= h($saved_address) ?></textarea>
        </div>
        <div class="flex flex-col gap-2">
            <label class="font-label-lg text-label-lg text-on-surface-variant">Pincode</label>
            <input name="postal_code" value="<?= h($saved_zip) ?>" class="w-full px-4 py-3 rounded-lg border border-outline-variant focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all bg-surface" placeholder="110001" type="text" autocomplete="postal-code">
        </div>
        <div class="flex flex-col gap-2">
            <label class="font-label-lg text-label-lg text-on-surface-variant">City</label>
            <input name="city" value="<?= h($saved_city) ?>" class="w-full px-4 py-3 rounded-lg border border-outline-variant focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all bg-surface" placeholder="New Delhi" required type="text" autocomplete="address-level2">
        </div>
        <div class="md:col-span-2 mt-4">
            <button type="button" onclick="goToStep(2)" class="w-full md:w-auto px-8 py-3 bg-primary text-on-primary rounded-lg font-label-lg text-label-lg hover:shadow-lg active:scale-95 transition-all">
                Continue to Payment
            </button>
        </div>
    </div>
</section>

<section class="hidden step-transition bg-surface-container-lowest rounded-xl p-8 shadow-[0px_4px_20px_rgba(0,0,0,0.05)]" id="step-2">
    <h2 class="font-headline-md text-headline-md text-primary mb-8 flex items-center gap-3">
        <span class="material-symbols-outlined">payments</span>
        Payment Method
    </h2>
    <div class="grid grid-cols-1 gap-4 mb-8">
        <label class="flex items-center gap-4 p-4 rounded-xl border border-outline-variant cursor-pointer hover:bg-secondary-container/20 transition-all group has-[:checked]:border-primary has-[:checked]:bg-secondary-container/10">
            <input type="radio" name="payment" value="card" checked class="w-5 h-5 text-primary focus:ring-primary">
            <span class="material-symbols-outlined text-primary">credit_card</span>
            <div class="flex flex-col">
                <span class="font-title-lg text-title-lg text-on-surface">Credit/Debit Card</span>
                <span class="font-body-md text-body-md text-on-surface-variant">Visa, Mastercard, RuPay, Maestro</span>
            </div>
        </label>
        <label class="flex items-center gap-4 p-4 rounded-xl border border-outline-variant cursor-pointer hover:bg-secondary-container/20 transition-all group has-[:checked]:border-primary has-[:checked]:bg-secondary-container/10">
            <input type="radio" name="payment" value="upi" class="w-5 h-5 text-primary focus:ring-primary">
            <span class="material-symbols-outlined text-primary">account_balance_wallet</span>
            <div class="flex flex-col">
                <span class="font-title-lg text-title-lg text-on-surface">UPI (Google Pay, PhonePe)</span>
                <span class="font-body-md text-body-md text-on-surface-variant">Instant payment using your UPI ID</span>
            </div>
        </label>
        <label class="flex items-center gap-4 p-4 rounded-xl border border-outline-variant cursor-pointer hover:bg-secondary-container/20 transition-all group has-[:checked]:border-primary has-[:checked]:bg-secondary-container/10">
            <input type="radio" name="payment" value="netbanking" class="w-5 h-5 text-primary focus:ring-primary">
            <span class="material-symbols-outlined text-primary">account_balance</span>
            <div class="flex flex-col">
                <span class="font-title-lg text-title-lg text-on-surface">Net Banking</span>
                <span class="font-body-md text-body-md text-on-surface-variant">All major Indian banks supported</span>
            </div>
        </label>
        <label class="flex items-center gap-4 p-4 rounded-xl border border-outline-variant cursor-pointer hover:bg-secondary-container/20 transition-all group has-[:checked]:border-primary has-[:checked]:bg-secondary-container/10">
            <input type="radio" name="payment" value="cod" class="w-5 h-5 text-primary focus:ring-primary">
            <span class="material-symbols-outlined text-primary">payments</span>
            <div class="flex flex-col">
                <span class="font-title-lg text-title-lg text-on-surface">Cash on Delivery</span>
                <span class="font-body-md text-body-md text-on-surface-variant">Pay when you receive the package</span>
            </div>
        </label>
    </div>
    <div class="flex gap-4">
        <button type="button" onclick="goToStep(1)" class="px-8 py-3 bg-surface-container text-on-surface rounded-lg font-label-lg text-label-lg hover:bg-surface-container-high transition-all">Back</button>
        <button type="button" onclick="goToStep(3)" class="flex-grow md:flex-none md:px-12 py-3 bg-primary text-on-primary rounded-lg font-label-lg text-label-lg hover:shadow-lg active:scale-95 transition-all">Continue to Review</button>
        <a href="<?= BASE_URL ?>/payment.php" class="flex-grow md:flex-none md:px-12 py-3 border border-primary text-primary rounded-lg font-label-lg text-label-lg text-center hover:bg-primary-fixed transition-all">Open Full Payment</a>
    </div>
</section>

<section class="hidden step-transition bg-surface-container-lowest rounded-xl p-8 shadow-[0px_4px_20px_rgba(0,0,0,0.05)]" id="step-3">
    <h2 class="font-headline-md text-headline-md text-primary mb-8 flex items-center gap-3">
        <span class="material-symbols-outlined">task_alt</span>
        Order Confirmation
    </h2>
    <div class="bg-surface-bright rounded-lg p-6 border border-outline-variant/30 mb-8">
        <h3 class="font-title-lg text-title-lg text-on-surface mb-4">Review Your Selection</h3>
        <div class="space-y-4">
            <?php foreach ($cart_items as $item): ?>
            <?php $qty = $item['quantity'] ?? 1; ?>
            <div class="flex justify-between items-center pb-4 border-b border-outline-variant/20 last:border-b-0">
                <div class="flex gap-4">
                    <div class="w-16 h-16 rounded-lg overflow-hidden bg-surface-container flex-shrink-0">
                        <?php if (!empty($item['image_url'])): ?>
                        <img class="w-full h-full object-cover" src="<?= htmlspecialchars($item['image_url']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                        <?php else: ?>
                        <div class="w-full h-full flex items-center justify-center bg-secondary-container text-primary font-bold text-xl"><?= htmlspecialchars(mb_substr($item['name'], 0, 1)) ?></div>
                        <?php endif; ?>
                    </div>
                    <div>
                        <p class="font-title-lg text-title-lg text-on-surface"><?= htmlspecialchars($item['name']) ?></p>
                        <p class="font-body-md text-body-md text-on-surface-variant">Qty: <?= $qty ?></p>
                    </div>
                </div>
                <span class="font-label-lg text-label-lg text-primary">₹<?= number_format($item['price'] * $qty, 2) ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
        <button type="button" onclick="goToStep(2)" class="w-full md:w-auto px-8 py-3 text-on-surface-variant font-label-lg text-label-lg hover:underline transition-all">Edit Payment</button>
        <button type="submit" name="place_order" class="w-full md:w-auto px-12 py-4 bg-primary text-on-primary rounded-lg font-headline-md text-headline-md shadow-xl hover:scale-[1.02] active:scale-[0.98] transition-all flex items-center justify-center gap-3">
            Place Order
            <span class="material-symbols-outlined">rocket_launch</span>
        </button>
    </div>
</section>

</form>
</div>

<div class="lg:col-span-4 sticky top-24">
    <div class="bg-surface-container-lowest rounded-xl p-6 shadow-[0px_4px_20px_rgba(0,0,0,0.05)] border border-outline-variant/20">
        <h3 class="font-headline-md text-headline-md text-on-surface mb-6">Order Summary</h3>
        <div class="space-y-3 mb-6">
            <div class="flex justify-between text-on-surface-variant">
                <span class="font-body-md text-body-md">Subtotal (<?= $cart_count ?> items)</span>
                <span class="font-body-lg text-body-lg">₹<?= number_format($cart_total, 2) ?></span>
            </div>
            <div class="flex justify-between text-on-surface-variant">
                <span class="font-body-md text-body-md">Shipping</span>
                <span class="font-body-lg text-body-lg text-primary">FREE</span>
            </div>
            <div class="pt-4 mt-4 border-t border-outline-variant/30 flex justify-between items-center">
                <span class="font-title-lg text-title-lg text-on-surface">Total</span>
                <span class="font-display-lg text-headline-md text-primary">₹<?= number_format($cart_total, 2) ?></span>
            </div>
        </div>
        <div class="bg-surface-bright p-4 rounded-lg flex flex-col gap-4">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-primary text-[20px]" style="font-variation-settings: 'FILL' 1;">verified_user</span>
                <span class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider">Secure Checkout</span>
            </div>
            <div class="flex flex-wrap gap-4 opacity-70">
                <span class="font-body-sm text-body-sm text-on-surface-variant">Visa · Mastercard · RuPay · UPI · Net Banking</span>
            </div>
        </div>
        <p class="mt-6 font-body-md text-body-md text-on-surface-variant text-center flex items-center justify-center gap-2">
            <span class="material-symbols-outlined text-[16px]">lock</span>
            SSL Encrypted Payment
        </p>
    </div>
</div>

</div>

<?php endif; ?>
<?php endif; ?>

</section>

<style>
.step-transition {
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}
</style>

<script>
var currentCheckoutStep = 1;
var checkoutAddressDraftKey = 'ayurviro_checkout_address_<?= (int)$uid ?>';
var checkoutOrderPlaced = <?= $order_placed ? 'true' : 'false' ?>;
var checkoutAddressFields = ['full_name', 'phone', 'address', 'postal_code', 'city'];

function getCheckoutAddressControls() {
    return checkoutAddressFields.reduce(function(controls, name) {
        var control = document.querySelector('#checkout-form [name="' + name + '"]');
        if (control) controls[name] = control;
        return controls;
    }, {});
}

function readCheckoutAddressDraft() {
    try {
        return JSON.parse(localStorage.getItem(checkoutAddressDraftKey) || '{}');
    } catch (e) {
        return {};
    }
}

function writeCheckoutAddressDraft() {
    var controls = getCheckoutAddressControls();
    if (!Object.keys(controls).length) return;
    var draft = {};
    checkoutAddressFields.forEach(function(name) {
        draft[name] = controls[name] ? controls[name].value : '';
    });
    localStorage.setItem(checkoutAddressDraftKey, JSON.stringify(draft));
}

function applyCheckoutAddressDraft() {
    if (checkoutOrderPlaced) {
        localStorage.removeItem(checkoutAddressDraftKey);
        return;
    }
    var controls = getCheckoutAddressControls();
    var draft = readCheckoutAddressDraft();
    checkoutAddressFields.forEach(function(name) {
        if (controls[name] && !controls[name].value && draft[name]) {
            controls[name].value = draft[name];
        }
    });
    checkoutAddressFields.forEach(function(name) {
        if (controls[name]) {
            controls[name].addEventListener('input', writeCheckoutAddressDraft);
            controls[name].addEventListener('change', writeCheckoutAddressDraft);
        }
    });
}

function getInvalidRequiredControl(stepNumber) {
    var section = document.getElementById('step-' + stepNumber);
    if (!section) return null;
    return Array.prototype.find.call(section.querySelectorAll('[required]'), function(control) {
        return !control.checkValidity();
    }) || null;
}

function showCheckoutStep(stepNumber) {
    document.querySelectorAll('section[id^="step-"]').forEach(function(section) {
        section.classList.add('hidden');
    });
    document.getElementById('step-' + stepNumber).classList.remove('hidden');

    for (var i = 1; i <= 3; i++) {
        var node = document.getElementById('step-node-' + i);
        var text = node.nextElementSibling;
        var icon = node.querySelector('.material-symbols-outlined');

        if (i < stepNumber) {
            node.className = 'w-10 h-10 rounded-full flex items-center justify-center bg-primary-container text-on-primary-container shadow-sm transition-all';
            text.className = 'font-label-lg text-label-lg text-primary';
            icon.innerHTML = 'check';
            icon.style.fontVariationSettings = "'FILL' 0, 'wght' 700";
        } else if (i === stepNumber) {
            node.className = 'w-10 h-10 rounded-full flex items-center justify-center bg-primary text-on-primary ring-4 ring-primary-fixed shadow-lg transition-all';
            text.className = 'font-label-lg text-label-lg text-primary';
            var icons = { 1: 'local_shipping', 2: 'payments', 3: 'fact_check' };
            icon.innerHTML = icons[i];
            icon.style.fontVariationSettings = "'FILL' 1";
        } else {
            node.className = 'w-10 h-10 rounded-full flex items-center justify-center bg-surface-container-highest text-on-surface-variant transition-all';
            text.className = 'font-label-lg text-label-lg text-on-surface-variant';
            var pendingIcons = { 2: 'payments', 3: 'fact_check' };
            icon.innerHTML = pendingIcons[i];
            icon.style.fontVariationSettings = "'FILL' 0";
        }
    }

    currentCheckoutStep = stepNumber;
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function goToStep(stepNumber) {
    if (stepNumber > currentCheckoutStep) {
        var invalid = getInvalidRequiredControl(currentCheckoutStep);
        if (invalid) {
            invalid.reportValidity();
            return;
        }
    }
    showCheckoutStep(stepNumber);
}

document.getElementById('checkout-form')?.addEventListener('submit', function(e) {
    var invalidAddress = getInvalidRequiredControl(1);
    if (invalidAddress) {
        e.preventDefault();
        showCheckoutStep(1);
        window.setTimeout(function() {
            invalidAddress.reportValidity();
        }, 50);
    }
});

applyCheckoutAddressDraft();
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
