<?php
require_once __DIR__ . '/includes/config.php';

$site_title = 'Order Tracking';
require_once __DIR__ . '/includes/header.php';

$order = null;
$order_items = [];
$error = '';
$order_number = isset($_GET['order_number']) ? trim($_GET['order_number']) : '';

if ($order_number !== '') {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM orders WHERE order_number = ?");
    $stmt->bind_param("s", $order_number);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();
    $stmt->close();

    if ($order) {
        $stmt2 = $db->prepare("SELECT oi.*, p.name, p.image_url FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
        $stmt2->bind_param("i", $order['id']);
        $stmt2->execute();
        $order_items = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt2->close();
    } else {
        $error = 'No order found with that order number. Please check and try again.';
    }
}

function getStepClass($step, $current_status) {
    $status_order = ['pending' => 1, 'processing' => 2, 'shipped' => 3, 'delivered' => 4];
    $step_order = ['confirmed' => 1, 'shipped' => 2, 'ontheway' => 3, 'delivered' => 4];
    $current = $status_order[$current_status] ?? 0;
    $step_val = $step_order[$step] ?? 0;

    if ($current >= 4 && $current_status === 'delivered') {
        return $step_val <= $current ? 'completed' : 'inactive';
    }

    if ($step_val < $current) return 'completed';
    if ($step_val === $current) return 'active';
    return 'inactive';
}

function getStepTimestamp($status, $step) {
    $step_order = ['confirmed' => 1, 'shipped' => 2, 'ontheway' => 3, 'delivered' => 4];
    $status_order = ['pending' => 1, 'processing' => 2, 'shipped' => 3, 'delivered' => 4];

    if (($status_order[$status] ?? 0) >= ($step_order[$step] ?? 0)) {
        $labels = [
            'confirmed' => 'Order confirmed',
            'shipped' => 'Shipped from warehouse',
            'ontheway' => 'Out for delivery',
            'delivered' => 'Delivered'
        ];
        return $labels[$step] ?? '';
    }
    return '';
}

function getStepLabel($step) {
    $labels = [
        'confirmed' => 'Confirmed',
        'shipped' => 'Shipped',
        'ontheway' => 'On the Way',
        'delivered' => 'Delivered'
    ];
    return $labels[$step] ?? '';
}

function getStepIcon($step, $step_class) {
    $icons = [
        'confirmed' => 'check_circle',
        'shipped' => 'package_2',
        'ontheway' => 'local_shipping',
        'delivered' => 'home'
    ];
    return $icons[$step] ?? 'circle';
}

function getMobileDescription($step) {
    $descriptions = [
        'confirmed' => 'Your order has been received and is being prepared.',
        'shipped' => 'Package is in transit to your local sorting facility.',
        'ontheway' => 'Courier has picked up your package for final delivery.',
        'delivered' => 'Your order has been delivered. Enjoy your wellness products!'
    ];
    return $descriptions[$step] ?? '';
}
?>

<section class="pt-32 pb-section-gap px-base md:px-margin-desktop max-w-container-max mx-auto min-h-screen">
<?php if ($order_number === ''): ?>
    <!-- Search Form -->
    <div class="max-w-xl mx-auto text-center">
        <div class="w-20 h-20 bg-primary-fixed rounded-full flex items-center justify-center mx-auto mb-6">
            <span class="material-symbols-outlined text-4xl text-primary">local_shipping</span>
        </div>
        <h1 class="font-headline-lg text-headline-lg text-primary mb-3">Track Your Order</h1>
        <p class="font-body-lg text-body-lg text-on-surface-variant mb-10">Enter your order number below to see the latest status of your wellness shipment.</p>
        <form method="GET" class="flex flex-col sm:flex-row gap-4">
            <input type="text" name="order_number" placeholder="e.g. ORD-AB12CD34" required class="flex-1 bg-surface-container-lowest border border-outline-variant rounded-xl px-6 py-4 font-body-lg text-body-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all" />
            <button type="submit" class="bg-primary text-on-primary font-label-lg text-label-lg px-8 py-4 rounded-xl hover:bg-primary-container transition-all active:scale-95 flex items-center justify-center gap-2">
                <span class="material-symbols-outlined">search</span> Track
            </button>
        </form>
        <div class="mt-8 flex gap-4 justify-center">
            <a href="<?= BASE_URL ?>/shop.php" class="font-label-lg text-label-lg text-primary hover:underline flex items-center gap-2">
                <span class="material-symbols-outlined">arrow_back</span> Continue Shopping
            </a>
            <?php if (isset($_SESSION['user_id'])): ?>
            <a href="<?= BASE_URL ?>/my-account.php" class="font-label-lg text-label-lg text-on-surface-variant hover:text-primary transition-colors flex items-center gap-2">
                <span class="material-symbols-outlined">account_circle</span> My Account
            </a>
            <?php endif; ?>
        </div>
    </div>
<?php elseif ($error): ?>
    <!-- Error State -->
    <div class="max-w-xl mx-auto text-center">
        <div class="w-20 h-20 bg-error-container rounded-full flex items-center justify-center mx-auto mb-6">
            <span class="material-symbols-outlined text-4xl text-error">search_off</span>
        </div>
        <h1 class="font-headline-lg text-headline-lg text-primary mb-3">Order Not Found</h1>
        <p class="font-body-lg text-body-lg text-on-surface-variant mb-6"><?= htmlspecialchars($error) ?></p>
        <a href="<?= BASE_URL ?>/order-tracking.php" class="bg-primary text-on-primary font-label-lg text-label-lg px-8 py-4 rounded-xl hover:bg-primary-container transition-all inline-flex items-center gap-2">
            <span class="material-symbols-outlined">arrow_back</span> Try Again
        </a>
        <div class="mt-6">
            <a href="<?= BASE_URL ?>/shop.php" class="font-label-lg text-label-lg text-primary hover:underline">Continue Shopping</a>
        </div>
    </div>
<?php elseif ($order): ?>
    <?php
    $status = $order['status'];
    $is_cancelled = $status === 'cancelled';
    $created = date('M d, Y', strtotime($order['created_at']));
    $estimated = date('M d, Y', strtotime($order['created_at'] . ' + 5 days'));
    $progress_pct = 0;
    if ($status === 'pending') $progress_pct = 25;
    elseif ($status === 'processing') $progress_pct = 50;
    elseif ($status === 'shipped') $progress_pct = 75;
    elseif ($status === 'delivered') $progress_pct = 100;
    ?>
    <!-- Header Section -->
    <div class="mb-12">
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <h1 class="font-headline-lg text-headline-lg text-primary mb-2">Track Your Wellness Journey</h1>
                <p class="font-body-lg text-body-lg text-on-surface-variant">Order #<?= htmlspecialchars($order['order_number']) ?> • Placed on <?= $created ?></p>
            </div>
            <?php if (!$is_cancelled): ?>
            <div class="bg-primary-container text-on-primary-container px-6 py-3 rounded-xl flex items-center gap-3">
                <span class="material-symbols-outlined">calendar_today</span>
                <div>
                    <p class="font-label-sm text-label-sm uppercase tracking-wider opacity-80">Estimated Delivery</p>
                    <p class="font-title-lg text-title-lg"><?= $estimated ?></p>
                </div>
            </div>
            <?php else: ?>
            <div class="bg-error-container text-on-error-container px-6 py-3 rounded-xl flex items-center gap-3">
                <span class="material-symbols-outlined">cancel</span>
                <div>
                    <p class="font-label-sm text-label-sm uppercase tracking-wider opacity-80">Status</p>
                    <p class="font-title-lg text-title-lg">Cancelled</p>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-gutter items-start">
        <!-- Left Column -->
        <div class="lg:col-span-2 space-y-gutter">
            <?php if ($is_cancelled): ?>
            <section class="bg-surface-container-lowest rounded-xl p-8 shadow-[0px_4px_20px_rgba(0,0,0,0.05)]">
                <div class="text-center py-8">
                    <div class="w-16 h-16 bg-error-container rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="material-symbols-outlined text-3xl text-error">cancel</span>
                    </div>
                    <h2 class="font-headline-md text-headline-md text-error mb-2">Order Cancelled</h2>
                    <p class="font-body-lg text-body-lg text-on-surface-variant mb-6">This order has been cancelled. If you have any questions, please contact our support team.</p>
                    <a href="<?= BASE_URL ?>/shop.php" class="bg-primary text-on-primary font-label-lg text-label-lg px-8 py-3 rounded-full hover:bg-primary-container transition-all inline-flex items-center gap-2">
                        <span class="material-symbols-outlined">shopping_bag</span> Continue Shopping
                    </a>
                </div>
            </section>
            <?php else: ?>
            <!-- Tracking Progress Card -->
            <section class="bg-surface-container-lowest rounded-xl p-8 shadow-[0px_4px_20px_rgba(0,0,0,0.05)]">
                <h2 class="font-title-lg text-title-lg mb-8 text-on-surface">Delivery Status</h2>
                <div class="relative">
                    <!-- Desktop Progress Bar -->
                    <div class="hidden md:flex justify-between items-start relative z-10">
                        <?php
                        $steps = ['confirmed', 'shipped', 'ontheway', 'delivered'];
                        foreach ($steps as $i => $step):
                            $sc = getStepClass($step, $status);
                            $is_completed = $sc === 'completed' || $sc === 'active';
                        ?>
                        <div class="flex flex-col items-center text-center w-1/4">
                            <div class="w-12 h-12 rounded-full <?= $is_completed ? 'bg-primary text-white ring-8 ring-primary-fixed/30' : 'bg-surface-container-highest text-outline' ?> flex items-center justify-center mb-4 transition-all duration-500">
                                <span class="material-symbols-outlined"><?= getStepIcon($step, $sc) ?></span>
                            </div>
                            <p class="font-label-lg text-label-lg <?= $is_completed ? 'text-primary' : 'text-on-surface-variant' ?>"><?= getStepLabel($step) ?></p>
                            <p class="font-label-sm text-label-sm <?= $is_completed ? 'text-on-surface-variant' : 'text-outline' ?>"><?= $is_completed ? getStepTimestamp($status, $step) : 'Pending' ?></p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <!-- Progress Lines (Desktop) -->
                    <div class="hidden md:block absolute top-6 left-[12.5%] right-[12.5%] h-1 bg-surface-container-highest -z-0">
                        <div class="h-full bg-primary transition-all duration-1000 ease-in-out" style="width: <?= $progress_pct ?>%"></div>
                    </div>
                    <!-- Mobile Vertical Stepper -->
                    <div class="md:hidden space-y-8 relative before:absolute before:left-6 before:top-2 before:bottom-2 before:w-0.5 before:bg-surface-container-highest">
                        <?php foreach ($steps as $step):
                            $sc = getStepClass($step, $status);
                            $is_completed = $sc === 'completed' || $sc === 'active';
                        ?>
                        <div class="flex gap-6 relative">
                            <div class="w-12 h-12 rounded-full <?= $is_completed ? 'bg-primary text-white' : 'bg-surface-container-highest text-outline' ?> flex items-center justify-center shrink-0 z-10 transition-all duration-500">
                                <span class="material-symbols-outlined"><?= getStepIcon($step, $sc) ?></span>
                            </div>
                            <div>
                                <p class="font-label-lg text-label-lg <?= $is_completed ? 'text-primary' : 'text-on-surface' ?>"><?= getStepLabel($step) ?></p>
                                <p class="font-body-md text-body-md <?= $is_completed ? 'text-on-surface-variant' : 'text-outline' ?>"><?= $is_completed ? getMobileDescription($step) : 'Awaiting update' ?></p>
                                <?php if ($is_completed && getStepTimestamp($status, $step)): ?>
                                <p class="font-label-sm text-label-sm text-outline mt-1"><?= getStepTimestamp($status, $step) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <?php if ($order['shipping_name']): ?>
                <div class="mt-12 p-6 border border-outline-variant rounded-xl bg-surface-bright flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-full bg-secondary-container flex items-center justify-center">
                            <span class="material-symbols-outlined text-primary text-3xl">delivery_dining</span>
                        </div>
                        <div>
                            <p class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wide">Shipping Address</p>
                            <p class="font-title-lg text-title-lg"><?= htmlspecialchars($order['shipping_name']) ?></p>
                            <p class="font-body-md text-body-md text-on-surface-variant"><?= htmlspecialchars($order['shipping_address']) ?>, <?= htmlspecialchars($order['shipping_city']) ?> <?= htmlspecialchars($order['shipping_zip']) ?></p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </section>
            <?php endif; ?>

            <!-- Order Items Summary -->
            <section class="bg-surface-container-lowest rounded-xl p-8 shadow-[0px_4px_20px_rgba(0,0,0,0.05)]">
                <h2 class="font-title-lg text-title-lg mb-6 text-on-surface">Order Summary</h2>
                <?php if (!empty($order_items)): ?>
                <div class="divide-y divide-outline-variant">
                    <?php foreach ($order_items as $item): ?>
                    <div class="py-4 flex items-center gap-6">
                        <div class="w-24 h-24 rounded-lg bg-surface overflow-hidden shrink-0">
                            <?php if ($item['image_url']): ?>
                            <img class="w-full h-full object-cover" src="<?= htmlspecialchars($item['image_url']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" />
                            <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center text-outline"><span class="material-symbols-outlined text-3xl">image</span></div>
                            <?php endif; ?>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-title-lg text-title-lg text-on-surface"><?= htmlspecialchars($item['name']) ?></h3>
                            <p class="font-body-md text-body-md text-on-surface-variant">Qty: <?= (int)$item['quantity'] ?></p>
                        </div>
                        <div class="text-right">
                            <p class="font-title-lg text-title-lg text-primary">₹<?= number_format($item['price'] * $item['quantity'], 2) ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <p class="font-body-lg text-body-lg text-on-surface-variant">No items found for this order.</p>
                <?php endif; ?>
                <div class="mt-6 pt-6 border-t-2 border-dashed border-outline-variant space-y-3">
                    <div class="flex justify-between font-body-lg text-body-lg">
                        <span class="text-on-surface-variant">Subtotal</span>
                        <span class="text-on-surface">₹<?= number_format($order['total'], 2) ?></span>
                    </div>
                    <div class="flex justify-between font-body-lg text-body-lg">
                        <span class="text-on-surface-variant">Shipping</span>
                        <span class="text-on-surface <?= (float)$order['shipping'] > 0 ? '' : 'text-primary' ?>"><?= (float)$order['shipping'] > 0 ? '₹' . number_format($order['shipping'], 2) : 'FREE' ?></span>
                    </div>
                    <div class="flex justify-between font-title-lg text-title-lg pt-2">
                        <span class="text-on-surface font-bold">Total</span>
                        <span class="text-primary font-bold">₹<?= number_format($order['total'], 2) ?></span>
                    </div>
                </div>
            </section>

            <!-- Back Links -->
            <div class="flex flex-wrap gap-4 pt-4">
                <a href="<?= BASE_URL ?>/shop.php" class="bg-primary text-on-primary font-label-lg text-label-lg px-8 py-3 rounded-full hover:bg-primary-container transition-all active:scale-95 inline-flex items-center gap-2">
                    <span class="material-symbols-outlined">shopping_bag</span> Continue Shopping
                </a>
                <?php if (isset($_SESSION['user_id'])): ?>
                <a href="<?= BASE_URL ?>/my-account.php" class="border border-outline-variant text-on-surface font-label-lg text-label-lg px-8 py-3 rounded-full hover:bg-surface-container transition-all inline-flex items-center gap-2">
                    <span class="material-symbols-outlined">account_circle</span> My Account
                </a>
                <?php endif; ?>
            </div>
            <?php
            $inv_stmt = $db->prepare("SELECT invoice_number FROM invoices WHERE order_id = ? AND pdf_path IS NOT NULL LIMIT 1");
            $inv_stmt->bind_param('i', $order['id']);
            $inv_stmt->execute();
            $has_invoice = $inv_stmt->get_result()->fetch_assoc();
            $inv_stmt->close();
            ?>
            <?php if ($has_invoice && !$is_cancelled): ?>
            <div class="pt-4">
                <a href="<?= BASE_URL ?>/invoice-download.php?order_id=<?= (int)$order['id'] ?>" class="flex items-center justify-center gap-2 w-full bg-surface-container-high text-on-surface font-label-lg text-label-lg px-8 py-3 rounded-full hover:bg-surface-container-highest transition-all border border-outline-variant">
                    <span class="material-symbols-outlined">description</span> Download Invoice
                </a>
            </div>
            <?php endif; ?>
        </div>

        <!-- Right Column: Sidebar -->
        <aside class="space-y-gutter">
            <!-- Need Help Card -->
            <section class="bg-primary text-on-primary rounded-xl p-8 shadow-lg relative overflow-hidden group">
                <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                    <span class="material-symbols-outlined text-8xl">support_agent</span>
                </div>
                <h3 class="font-headline-md text-headline-md mb-2 relative z-10">Need Assistance?</h3>
                <p class="font-body-md text-body-md mb-6 relative z-10 opacity-90">Our wellness experts are available to help with any questions regarding your order.</p>
                <a class="flex items-center justify-center gap-3 bg-white text-primary font-label-lg text-label-lg py-4 rounded-xl hover:bg-secondary-fixed transition-colors relative z-10" href="<?= BASE_URL ?>/contact-us.php">
                    <span class="material-symbols-outlined">mail</span> Contact Support
                </a>
            </section>
            <!-- Wellness Tip Card -->
            <section class="bg-tertiary-container text-on-tertiary-container rounded-xl p-8 shadow-sm border border-tertiary">
                <div class="flex items-center gap-3 mb-4">
                    <span class="material-symbols-outlined text-tertiary-fixed">eco</span>
                    <h4 class="font-label-lg text-label-lg uppercase tracking-widest font-bold">Wellness Tip</h4>
                </div>
                <h5 class="font-title-lg text-title-lg mb-4">Mindful Unpacking</h5>
                <p class="font-body-md text-body-md opacity-90 leading-relaxed mb-6">
                    "When your wellness order arrives, transform unpacking into a ritual. Create a calm space, open each item with intention, and set a positive affirmation for your healing journey."
                </p>
                <div class="flex items-center gap-2 text-tertiary-fixed">
                    <span class="material-symbols-outlined scale-75">auto_awesome</span>
                    <a href="<?= BASE_URL ?>/wellness-blog.php" class="font-label-sm text-label-sm italic hover:underline">Explore more tips</a>
                </div>
            </section>
            <!-- Payment Info -->
            <section class="bg-surface-container-low rounded-xl p-6 border border-outline-variant">
                <p class="font-label-lg text-label-lg mb-3">Payment Details</p>
                <div class="space-y-2 font-body-md text-body-md">
                    <div class="flex justify-between">
                        <span class="text-on-surface-variant">Method</span>
                        <span class="font-medium"><?= htmlspecialchars(ucfirst($order['payment_method'] ?? 'N/A')) ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-on-surface-variant">Payment Status</span>
                        <span class="<?= $order['payment_status'] === 'paid' ? 'text-primary' : ($order['payment_status'] === 'refunded' ? 'text-error' : 'text-on-surface') ?> font-medium"><?= htmlspecialchars(ucfirst($order['payment_status'] ?? 'N/A')) ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-on-surface-variant">Order Status</span>
                        <span class="font-medium"><?= htmlspecialchars(ucfirst($status)) ?></span>
                    </div>
                </div>
            </section>
        </aside>
    </div>
<?php endif; ?>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
