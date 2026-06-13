<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/feature_helpers.php';
require_login();

$db = getDB();
$uid = (int)$_SESSION['user_id'];
$site_title = 'Retailer Dashboard';
$orders = [];
if (table_exists($db, 'order_punch')) {
    $stmt = $db->prepare('SELECT * FROM order_punch WHERE user_id = ? ORDER BY created_at DESC LIMIT 5');
    $stmt->bind_param('i', $uid);
    $stmt->execute();
    $orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
$products = fetch_products($db, 6);
require_once __DIR__ . '/includes/header.php';
?>
<section class="pt-28 pb-section-gap max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop">
<div class="grid gap-8 lg:grid-cols-[260px_1fr]">
    <aside class="rounded-xl border border-outline-variant bg-surface-container-lowest p-4 h-fit lg:sticky lg:top-28">
        <h1 class="font-headline-md text-headline-md text-primary">Retailer Panel</h1>
        <p class="text-label-sm text-on-surface-variant">GSTIN verified · Balance ₹12,500</p>
        <nav class="mt-5 grid gap-1"><?php foreach ([['retailer-dashboard.php','dashboard','Dashboard'],['retailer-stock.php','inventory_2','My Inventory'],['order-punch.php','local_shipping','Place Order'],['retailer-orders.php','receipt_long','Order History'],['distributor-panel.php','store','Distributors'],['retailer-reports.php','monitoring','Reports'],['profile.php','settings','Settings']] as $item): ?><a class="<?= $current_page===$item[0]?'bg-primary text-on-primary':'hover:bg-surface-container text-on-surface-variant' ?> rounded-lg px-3 py-2 flex gap-2" href="<?= BASE_URL ?>/<?= $item[0] ?>"><span class="material-symbols-outlined"><?= $item[1] ?></span><?= $item[2] ?></a><?php endforeach; ?></nav>
    </aside>
    <div>
        <div class="mb-8 flex flex-col gap-4 md:flex-row md:items-end md:justify-between"><div><h2 class="font-display-lg text-display-lg text-primary">Store Dashboard</h2><p class="text-on-surface-variant">Manage inventory, distributors and bulk order punching.</p></div><a href="<?= BASE_URL ?>/order-punch.php" class="rounded-lg bg-primary px-5 py-3 text-on-primary">Quick Order Punch</a></div>
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <?= stat_card('receipt_long','Orders This Month', (string)count($orders), '↑12%') ?>
            <?= stat_card('pending_actions','Pending Approvals','5','View') ?>
            <?= stat_card('warning','Low Stock Items','8','Reorder') ?>
            <?= stat_card('account_balance','Credit Used','₹45,000','45%') ?>
        </div>
        <div class="mt-8 grid gap-6 xl:grid-cols-[1fr_360px]">
            <section class="rounded-xl border border-outline-variant bg-surface-container-lowest overflow-hidden"><div class="p-5 border-b border-outline-variant flex justify-between"><h3 class="font-headline-md text-headline-md text-primary">Recent Orders</h3><a href="<?= BASE_URL ?>/retailer-orders.php" class="text-primary">View All</a></div><div class="overflow-x-auto"><table class="w-full min-w-[680px] text-left"><thead class="bg-surface-container text-label-sm uppercase text-on-surface-variant"><tr><th class="p-3">Order ID</th><th class="p-3">Date</th><th class="p-3">Items</th><th class="p-3">Total</th><th class="p-3">Status</th><th class="p-3">Action</th></tr></thead><tbody class="divide-y divide-outline-variant"><?php foreach ($orders ?: [['order_number'=>'ORD-P-5678','created_at'=>date('Y-m-d'),'net_amount'=>4500,'status'=>'pending']] as $o): ?><tr><td class="p-3"><?= h($o['order_number']) ?></td><td class="p-3"><?= date('d M Y', strtotime($o['created_at'])) ?></td><td class="p-3">3</td><td class="p-3"><?= money($o['net_amount'] ?? 0) ?></td><td class="p-3"><span class="rounded-full bg-tertiary-fixed px-3 py-1 text-label-sm"><?= h(ucfirst($o['status'])) ?></span></td><td class="p-3"><a class="text-primary" href="<?= BASE_URL ?>/retailer-orders.php">View</a></td></tr><?php endforeach; ?></tbody></table></div></section>
            <aside class="space-y-6"><section class="rounded-xl border border-outline-variant bg-surface-container-lowest p-5"><h3 class="font-title-lg text-title-lg text-primary">Quick Add Products</h3><input class="mt-4 w-full rounded-lg border-outline-variant" placeholder="Search products by name or SKU"><?php foreach (array_slice($products,0,4) as $p): ?><div class="mt-3 flex items-center justify-between rounded-lg bg-surface-container p-3"><span><?= h($p['name']) ?></span><a href="<?= BASE_URL ?>/order-punch.php" class="text-primary">Add</a></div><?php endforeach; ?></section><section class="rounded-xl border border-outline-variant bg-surface-container-lowest p-5"><h3 class="font-title-lg text-title-lg text-primary">Low Stock Alerts</h3><p class="mt-2 text-on-surface-variant">8 products below reorder level.</p><a href="<?= BASE_URL ?>/retailer-stock.php" class="mt-4 inline-block text-primary">Review Inventory</a></section></aside>
        </div>
    </div>
</div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
