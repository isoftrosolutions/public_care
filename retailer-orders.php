<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/feature_helpers.php';
require_login();
$db = getDB(); $uid = (int)$_SESSION['user_id']; $site_title = 'Retailer Orders';
$orders = [];
if (table_exists($db, 'order_punch')) { $stmt=$db->prepare('SELECT * FROM order_punch WHERE user_id=? ORDER BY created_at DESC'); $stmt->bind_param('i',$uid); $stmt->execute(); $orders=$stmt->get_result()->fetch_all(MYSQLI_ASSOC); }
require_once __DIR__ . '/includes/header.php';
?>
<section class="pt-32 pb-section-gap px-margin-mobile md:px-margin-desktop max-w-container-max mx-auto">
<div class="mb-8 flex flex-col gap-4 md:flex-row md:items-end md:justify-between"><div><h1 class="font-display-lg text-display-lg text-primary">Retailer Order Management</h1><p class="text-on-surface-variant">View, track, repeat and manage B2B orders.</p></div><a href="<?= BASE_URL ?>/order-punch.php" class="rounded-lg bg-primary px-5 py-3 text-on-primary">Start Ordering</a></div>
<div class="mb-6 grid gap-3 md:grid-cols-4"><input type="date" class="rounded-lg border-outline-variant"><select class="rounded-lg border-outline-variant"><option>All Status</option><option>Pending</option><option>Approved</option><option>Shipped</option></select><input class="rounded-lg border-outline-variant" placeholder="Search order or product"><button onclick="downloadCsv()" class="rounded-lg border border-primary px-4 text-primary">Download CSV</button></div>
<div class="mb-6 flex flex-wrap gap-2"><?php foreach (['All Orders','Pending Approval','Shipped','Delivered','Drafts'] as $tab): ?><button class="rounded-full border border-outline-variant px-4 py-2 first:bg-primary first:text-on-primary"><?= $tab ?></button><?php endforeach; ?></div>
<div class="rounded-xl border border-outline-variant bg-surface-container-lowest overflow-hidden"><div class="overflow-x-auto"><table class="w-full min-w-[760px] text-left"><thead class="bg-surface-container text-label-sm uppercase text-on-surface-variant"><tr><th class="p-4">Order ID</th><th class="p-4">Date</th><th class="p-4">Distributor</th><th class="p-4">Items</th><th class="p-4">Total</th><th class="p-4">Status</th><th class="p-4">Actions</th></tr></thead><tbody class="divide-y divide-outline-variant"><?php foreach ($orders ?: [['order_number'=>'ORD-P-5678','created_at'=>date('Y-m-d'),'net_amount'=>4500,'status'=>'pending']] as $o): ?><tr class="hover:bg-surface-container"><td class="p-4"><?= h($o['order_number']) ?></td><td class="p-4"><?= date('d M Y', strtotime($o['created_at'])) ?></td><td class="p-4">AyurViora Distributor</td><td class="p-4">5</td><td class="p-4"><?= money($o['net_amount'] ?? 0) ?></td><td class="p-4"><span class="rounded-full bg-tertiary-fixed px-3 py-1 text-label-sm"><?= h(ucfirst($o['status'])) ?></span></td><td class="p-4 flex gap-2"><button data-detail class="text-primary">View Details</button><a class="text-primary" href="<?= BASE_URL ?>/order-punch.php">Repeat</a></td></tr><?php endforeach; ?></tbody></table></div></div>
</section>
<script>function downloadCsv(){const a=document.createElement('a');a.href='data:text/csv,Order ID,Date,Total,Status%0AORD-P-5678,<?= date('Y-m-d') ?>,4500,Pending';a.download='retailer-orders.csv';a.click();}</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
