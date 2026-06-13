<?php
require_once __DIR__ . '/includes/config.php';

$site_title = 'My Orders';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = BASE_URL . '/orders.php';
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

require_once __DIR__ . '/includes/header.php';

$uid = (int)$_SESSION['user_id'];
$db = getDB();

$status_filter = isset($_GET['status']) && in_array($_GET['status'], ['pending', 'processing', 'shipped', 'delivered', 'cancelled']) ? $_GET['status'] : '';

$sql = "SELECT o.*, (SELECT COUNT(*) FROM order_items oi2 WHERE oi2.order_id = o.id) as item_count FROM orders o WHERE o.user_id = ?";
$params = [$uid];
$types = 'i';

if ($status_filter) {
    $sql .= " AND o.status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

$sql .= " ORDER BY o.created_at DESC";

$stmt = $db->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$order_ids = array_map('intval', array_column($orders, 'id'));
$order_items_map = [];
$invoice_map = [];

if (!empty($order_ids)) {
    $ids_list = implode(',', $order_ids);
    $result = $db->query("SELECT oi.*, p.name, p.image_url, p.slug FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id IN ($ids_list) ORDER BY oi.id");
    $all_items = $result->fetch_all(MYSQLI_ASSOC);
    foreach ($all_items as $item) {
        $order_items_map[(int)$item['order_id']][] = $item;
    }

    $inv_result = $db->query("SELECT order_id, invoice_number FROM invoices WHERE order_id IN ($ids_list) AND pdf_path IS NOT NULL");
    $invoice_map = [];
    while ($inv_row = $inv_result->fetch_assoc()) {
        $invoice_map[(int)$inv_row['order_id']] = $inv_row['invoice_number'];
    }
}

$tabs = [
    '' => 'All',
    'processing' => 'Processing',
    'shipped' => 'Shipped',
    'delivered' => 'Delivered',
    'cancelled' => 'Cancelled',
];

$status_colors = [
    'pending' => 'bg-[#f6be35]/15 text-[#f6be35]',
    'processing' => 'bg-primary-container/15 text-primary-container',
    'shipped' => 'bg-[#1f6c35]/15 text-[#1f6c35]',
    'delivered' => 'bg-primary/15 text-primary',
    'cancelled' => 'bg-error/15 text-error',
];

$status_bg_map = [
    'pending' => '#f6be35',
    'processing' => '#1e6b34',
    'shipped' => '#1f6c35',
    'delivered' => '#005221',
    'cancelled' => '#ba1a1a',
];
?>

<section class="pt-32 pb-section-gap px-base md:px-margin-desktop max-w-container-max mx-auto min-h-screen">

<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
    <div>
        <h1 class="font-display-lg text-display-lg text-primary">My Orders</h1>
        <p class="font-body-lg text-body-lg text-on-surface-variant mt-1"><?= count($orders) ?> order<?= count($orders) !== 1 ? 's' : '' ?> placed</p>
    </div>
    <a href="<?= BASE_URL ?>/shop.php" class="inline-flex items-center gap-2 bg-primary text-on-primary px-6 py-3 rounded-full font-label-lg text-label-lg hover:bg-primary-container transition-all active:scale-95 w-fit">
        <span class="material-symbols-outlined">shopping_bag</span>
        Continue Shopping
    </a>
</div>

<div class="flex gap-2 overflow-x-auto hide-scrollbar pb-2 mb-8 border-b border-outline-variant/30">
    <?php foreach ($tabs as $val => $label): ?>
    <a href="<?= $val ? '?status=' . $val : BASE_URL . '/orders.php' ?>"
       class="whitespace-nowrap px-5 py-2.5 rounded-full font-label-lg text-label-lg transition-all <?= ($status_filter === $val) ? 'bg-primary text-on-primary shadow-md' : 'text-on-surface-variant hover:bg-surface-container hover:text-on-surface' ?>">
        <?= $label ?>
        <?php if ($val === ''): ?>
        <span class="ml-1.5 text-sm opacity-70">(<?= count($orders) ?>)</span>
        <?php endif; ?>
    </a>
    <?php endforeach; ?>
</div>

<?php if (empty($orders)): ?>

<div class="text-center py-20">
    <div class="w-36 h-36 mx-auto mb-8 bg-surface-container-low rounded-full flex items-center justify-center">
        <span class="material-symbols-outlined text-[64px] text-outline">inventory_2</span>
    </div>
    <h2 class="font-headline-md text-headline-md text-on-surface mb-3">No orders yet</h2>
    <p class="font-body-lg text-body-lg text-on-surface-variant mb-8 max-w-md mx-auto">Start your wellness journey — browse our Ayurvedic products and place your first order today.</p>
    <a href="<?= BASE_URL ?>/shop.php" class="inline-flex items-center gap-2 bg-primary text-on-primary px-8 py-4 rounded-full font-label-lg text-label-lg hover:bg-primary-container hover:scale-[1.02] active:scale-[0.98] transition-all">
        <span class="material-symbols-outlined">storefront</span>
        Shop Now
    </a>
</div>

<?php else: ?>

<div class="flex flex-col gap-6">
    <?php foreach ($orders as $order):
        $oid = (int)$order['id'];
        $items = $order_items_map[$oid] ?? [];
        $first_item = $items[0] ?? null;
        $item_count = (int)$order['item_count'];
        $created = date('M d, Y', strtotime($order['created_at']));
        $total = (float)$order['total'];
        $status = $order['status'];
    ?>
    <div class="bg-surface-container-lowest rounded-xl shadow-[0px_4px_20px_rgba(0,0,0,0.05)] border border-outline-variant/10 hover:shadow-[0px_8px_30px_rgba(0,0,0,0.08)] transition-all">
        <div class="p-6">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-4">
                <div class="flex items-center gap-4 flex-wrap">
                    <div>
                        <p class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider">Order #</p>
                        <p class="font-title-lg text-title-lg text-on-surface"><?= htmlspecialchars($order['order_number']) ?></p>
                    </div>
                    <div class="w-px h-10 bg-outline-variant/50 hidden md:block"></div>
                    <div>
                        <p class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider">Placed on</p>
                        <p class="font-body-lg text-body-lg text-on-surface"><?= $created ?></p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="px-3 py-1.5 rounded-full font-label-sm text-label-sm <?= $status_colors[$status] ?? 'bg-surface-container text-on-surface-variant' ?>">
                        <?= ucfirst($status) ?>
                    </span>
                    <span class="px-3 py-1.5 rounded-full font-label-sm text-label-sm bg-surface-container-high text-on-surface-variant">
                        <?= ucfirst($order['payment_status']) ?>
                    </span>
                </div>
            </div>

            <div class="flex flex-col md:flex-row gap-6 items-start py-4 border-t border-outline-variant/20">
                <div class="flex gap-4 flex-1 min-w-0">
                    <div class="w-20 h-20 rounded-xl bg-surface-container overflow-hidden shrink-0">
                        <?php if ($first_item && !empty($first_item['image_url'])): ?>
                        <img class="w-full h-full object-cover" src="<?= htmlspecialchars($first_item['image_url']) ?>" alt="<?= htmlspecialchars($first_item['name']) ?>">
                        <?php else: ?>
                        <div class="w-full h-full flex items-center justify-center text-outline">
                            <span class="material-symbols-outlined text-3xl">image</span>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="min-w-0">
                        <p class="font-title-lg text-title-lg text-on-surface truncate">
                            <?php if ($first_item): ?><?= htmlspecialchars($first_item['name']) ?><?php endif; ?>
                        </p>
                        <p class="font-body-md text-body-md text-on-surface-variant">
                            <?= $item_count ?> item<?= $item_count !== 1 ? 's' : '' ?>
                            <?php if ($item_count > 1): ?>
                            <span class="text-outline"> &middot; +<?= $item_count - 1 ?> more</span>
                            <?php endif; ?>
                        </p>
                        <p class="font-headline-md text-headline-md text-primary mt-1">₹<?= number_format($total, 2) ?></p>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2 md:flex-nowrap md:shrink-0">
                    <button onclick="openOrderDetail(<?= $oid ?>)" class="flex items-center gap-1.5 px-4 py-2.5 border border-outline-variant rounded-lg font-label-lg text-label-lg text-on-surface hover:bg-surface-container transition-all active:scale-95">
                        <span class="material-symbols-outlined text-[18px]">visibility</span>
                        View Details
                    </button>
                    <?php if (isset($invoice_map[$oid])): ?>
                    <a href="<?= BASE_URL ?>/invoice-download.php?order_id=<?= $oid ?>" class="flex items-center gap-1.5 px-4 py-2.5 border border-outline-variant rounded-lg font-label-lg text-label-lg text-on-surface hover:bg-surface-container transition-all active:scale-95">
                        <span class="material-symbols-outlined text-[18px]">description</span>
                        Invoice
                    </a>
                    <?php endif; ?>
                    <?php if (in_array($status, ['processing', 'shipped'])): ?>
                    <a href="<?= BASE_URL ?>/order-tracking.php?order_number=<?= urlencode($order['order_number']) ?>" class="flex items-center gap-1.5 px-4 py-2.5 bg-primary text-on-primary rounded-lg font-label-lg text-label-lg hover:bg-primary-container transition-all active:scale-95">
                        <span class="material-symbols-outlined text-[18px]">local_shipping</span>
                        Track Order
                    </a>
                    <?php endif; ?>
                    <?php if ($status === 'delivered'): ?>
                    <a href="<?= BASE_URL ?>/returns.php?order=<?= urlencode($order['order_number']) ?>" class="flex items-center gap-1.5 px-4 py-2.5 border border-outline-variant rounded-lg font-label-lg text-label-lg text-on-surface hover:bg-surface-container transition-all active:scale-95">
                        <span class="material-symbols-outlined text-[18px]">assignment_return</span>
                        Return
                    </a>
                    <?php endif; ?>
                    <a href="<?= BASE_URL ?>/cart-update.php?action=add&id=<?= $first_item ? (int)$first_item['product_id'] : 0 ?>" class="flex items-center gap-1.5 px-4 py-2.5 border border-outline-variant rounded-lg font-label-lg text-label-lg text-on-surface hover:bg-surface-container transition-all active:scale-95">
                        <span class="material-symbols-outlined text-[18px]">replay</span>
                        Reorder
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Detail Modal -->
    <div id="order-detail-<?= $oid ?>" class="fixed inset-0 z-[100] hidden" role="dialog" aria-modal="true">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="closeOrderDetail(<?= $oid ?>)"></div>
        <div class="absolute inset-x-4 top-8 bottom-8 md:inset-x-auto md:left-1/2 md:-translate-x-1/2 md:w-full md:max-w-3xl bg-surface-container-lowest rounded-2xl shadow-2xl overflow-y-auto">
            <div class="sticky top-0 bg-surface-container-lowest z-10 flex items-center justify-between px-6 py-4 border-b border-outline-variant/20">
                <h2 class="font-headline-md text-headline-md text-primary">Order Details</h2>
                <button onclick="closeOrderDetail(<?= $oid ?>)" class="w-10 h-10 rounded-full flex items-center justify-center hover:bg-surface-container transition-all">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <div class="p-6 space-y-6">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-surface-container-low rounded-lg p-4">
                        <p class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider">Order #</p>
                        <p class="font-title-lg text-title-lg text-on-surface mt-1 break-all"><?= htmlspecialchars($order['order_number']) ?></p>
                    </div>
                    <div class="bg-surface-container-low rounded-lg p-4">
                        <p class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider">Date</p>
                        <p class="font-title-lg text-title-lg text-on-surface mt-1"><?= $created ?></p>
                    </div>
                    <div class="bg-surface-container-low rounded-lg p-4">
                        <p class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider">Status</p>
                        <span class="inline-block mt-1 px-3 py-1 rounded-full font-label-sm text-label-sm <?= $status_colors[$status] ?? 'bg-surface-container text-on-surface-variant' ?>"><?= ucfirst($status) ?></span>
                    </div>
                    <div class="bg-surface-container-low rounded-lg p-4">
                        <p class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider">Payment</p>
                        <p class="font-title-lg text-title-lg text-on-surface mt-1"><?= ucfirst($order['payment_status']) ?></p>
                        <p class="font-body-md text-body-md text-on-surface-variant"><?= htmlspecialchars(ucfirst($order['payment_method'] ?? 'N/A')) ?></p>
                    </div>
                </div>

                <?php if (!empty($items)): ?>
                <div>
                    <h3 class="font-title-lg text-title-lg text-on-surface mb-4">Items (<?= count($items) ?>)</h3>
                    <div class="divide-y divide-outline-variant/20 border border-outline-variant/20 rounded-xl overflow-hidden">
                        <?php foreach ($items as $item): ?>
                        <div class="flex items-center gap-4 p-4 bg-surface-container-lowest">
                            <div class="w-16 h-16 rounded-lg bg-surface-container overflow-hidden shrink-0">
                                <?php if (!empty($item['image_url'])): ?>
                                <img class="w-full h-full object-cover" src="<?= htmlspecialchars($item['image_url']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                                <?php else: ?>
                                <div class="w-full h-full flex items-center justify-center text-outline">
                                    <span class="material-symbols-outlined text-2xl">image</span>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-title-lg text-title-lg text-on-surface truncate"><?= htmlspecialchars($item['name']) ?></p>
                                <p class="font-body-md text-body-md text-on-surface-variant">Qty: <?= (int)$item['quantity'] ?> &times; ₹<?= number_format((float)$item['price'], 2) ?></p>
                            </div>
                            <p class="font-title-lg text-title-lg text-primary shrink-0">₹<?= number_format((float)$item['price'] * (int)$item['quantity'], 2) ?></p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($order['shipping_name']): ?>
                <div>
                    <h3 class="font-title-lg text-title-lg text-on-surface mb-3">Shipping Address</h3>
                    <div class="bg-surface-container-low rounded-xl p-5">
                        <p class="font-title-lg text-title-lg text-on-surface"><?= htmlspecialchars($order['shipping_name']) ?></p>
                        <?php if ($order['shipping_phone']): ?>
                        <p class="font-body-md text-body-md text-on-surface-variant"><?= htmlspecialchars($order['shipping_phone']) ?></p>
                        <?php endif; ?>
                        <p class="font-body-md text-body-md text-on-surface-variant"><?= htmlspecialchars($order['shipping_address']) ?></p>
                        <p class="font-body-md text-body-md text-on-surface-variant"><?= htmlspecialchars($order['shipping_city']) ?> <?= htmlspecialchars($order['shipping_zip']) ?></p>
                    </div>
                </div>
                <?php endif; ?>

                <div>
                    <h3 class="font-title-lg text-title-lg text-on-surface mb-3">Payment Summary</h3>
                    <div class="bg-surface-container-low rounded-xl p-5 space-y-3">
                        <div class="flex justify-between font-body-lg text-body-lg">
                            <span class="text-on-surface-variant">Subtotal</span>
                            <span class="text-on-surface">₹<?= number_format($total, 2) ?></span>
                        </div>
                        <div class="flex justify-between font-body-lg text-body-lg">
                            <span class="text-on-surface-variant">Shipping</span>
                            <span class="text-on-surface <?= (float)$order['shipping'] > 0 ? '' : 'text-primary' ?>"><?= (float)$order['shipping'] > 0 ? '₹' . number_format((float)$order['shipping'], 2) : 'FREE' ?></span>
                        </div>
                        <?php if ((float)$order['tax'] > 0): ?>
                        <div class="flex justify-between font-body-lg text-body-lg">
                            <span class="text-on-surface-variant">Tax</span>
                            <span class="text-on-surface">₹<?= number_format((float)$order['tax'], 2) ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="flex justify-between font-title-lg text-title-lg pt-2 border-t border-outline-variant/30">
                            <span class="text-on-surface font-bold">Total</span>
                            <span class="text-primary font-bold">₹<?= number_format($total, 2) ?></span>
                        </div>
                    </div>
                </div>

                <?php if ($status === 'shipped'): ?>
                <div class="bg-primary-fixed/20 rounded-xl p-5 flex items-center gap-4">
                    <span class="material-symbols-outlined text-primary text-3xl">local_shipping</span>
                    <div class="flex-1">
                        <p class="font-title-lg text-title-lg text-primary">Your order has been shipped!</p>
                        <p class="font-body-md text-body-md text-on-surface-variant">Track your shipment for real-time updates.</p>
                    </div>
                    <a href="<?= BASE_URL ?>/order-tracking.php?order_number=<?= urlencode($order['order_number']) ?>" class="shrink-0 bg-primary text-on-primary px-5 py-2.5 rounded-lg font-label-lg text-label-lg hover:bg-primary-container transition-all active:scale-95">
                        Track Now
                    </a>
                </div>
                <?php elseif ($status === 'delivered'): ?>
                <div class="bg-primary/10 rounded-xl p-5 flex items-center gap-4">
                    <span class="material-symbols-outlined text-primary text-3xl" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                    <div class="flex-1">
                        <p class="font-title-lg text-title-lg text-primary">Delivered successfully!</p>
                        <p class="font-body-md text-body-md text-on-surface-variant">Thank you for choosing <?= SITE_NAME ?>. We hope you love your products.</p>
                    </div>
                    <a href="<?= BASE_URL ?>/returns.php?order=<?= urlencode($order['order_number']) ?>" class="shrink-0 border border-outline-variant text-on-surface px-5 py-2.5 rounded-lg font-label-lg text-label-lg hover:bg-surface-container transition-all active:scale-95">
                        Return
                    </a>
                </div>
                <?php elseif ($status === 'cancelled'): ?>
                <div class="bg-error-container rounded-xl p-5 flex items-center gap-4">
                    <span class="material-symbols-outlined text-error text-3xl">cancel</span>
                    <div class="flex-1">
                        <p class="font-title-lg text-title-lg text-error">Order cancelled</p>
                        <p class="font-body-md text-body-md text-on-surface-variant">If you have any questions, please contact our support team.</p>
                    </div>
                    <a href="<?= BASE_URL ?>/contact-us.php" class="shrink-0 bg-error text-on-error px-5 py-2.5 rounded-lg font-label-lg text-label-lg hover:bg-error/80 transition-all active:scale-95">
                        Contact Support
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php endif; ?>

</section>

<style>
.hide-scrollbar::-webkit-scrollbar { display: none; }
.hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>

<script>
function openOrderDetail(id) {
    document.getElementById('order-detail-' + id).classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}
function closeOrderDetail(id) {
    document.getElementById('order-detail-' + id).classList.add('hidden');
    document.body.style.overflow = '';
}
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('[id^="order-detail-"]').forEach(function(el) {
            if (!el.classList.contains('hidden')) {
                el.classList.add('hidden');
                document.body.style.overflow = '';
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
