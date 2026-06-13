<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/feature_helpers.php';
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') { header('Location: ' . BASE_URL . '/login.php'); exit; }
$db=getDB(); $page_title='Returns & Refunds'; $active_page='returns';
$returns=[];
if (table_exists($db,'return_requests')) {
    $sql="SELECT r.*, u.full_name, u.mobile, o.order_number FROM return_requests r JOIN users u ON u.id=r.user_id JOIN orders o ON o.id=r.order_id ORDER BY r.created_at DESC";
    $res=$db->query($sql); $returns=$res?$res->fetch_all(MYSQLI_ASSOC):[];
}
require_once __DIR__ . '/includes/head.php';
?>
<body class="bg-surface font-body-md text-on-surface flex min-h-screen">
<?php require_once __DIR__ . '/includes/sidebar.php'; ?>
<main class="flex-grow ml-64 p-margin-desktop bg-surface min-h-screen">
<header class="mb-10"><h1 class="text-display-lg font-display-lg text-primary">Returns & Refunds</h1><p class="text-on-surface-variant">Approve, reject, schedule pickup and process refunds.</p></header>
<div class="mb-6 grid gap-3 md:grid-cols-4"><input class="rounded-lg border-outline-variant" placeholder="Search Return or Order ID"><select class="rounded-lg border-outline-variant"><option>All Status</option><option>Pending Approval</option><option>Refunded</option></select><select class="rounded-lg border-outline-variant"><option>All Types</option><option>Return</option><option>Replacement</option></select><input type="date" class="rounded-lg border-outline-variant"></div>
<section class="grid gap-4 md:grid-cols-4 mb-8"><?= stat_card('assignment_return','Total Returns This Month','24','') ?><?= stat_card('pending_actions','Pending Approval','8','') ?><?= stat_card('timer','Avg Refund Time','2.3 days','') ?><?= stat_card('percent','Return Rate','1.8%','') ?></section>
<section class="grid gap-5">
<?php foreach ($returns ?: [['return_number'=>'RET-5678','order_number'=>'ORD-1234','full_name'=>'Sample Customer','mobile'=>'9999999999','return_type'=>'return','status'=>'pending','reason'=>'expired','created_at'=>date('Y-m-d')]] as $r): ?>
<article class="rounded-xl border border-outline-variant bg-surface-container-lowest p-6"><div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"><div><h2 class="text-headline-md text-primary"><?= h($r['return_number']) ?> <span class="text-on-surface-variant">| Order <?= h($r['order_number']) ?></span></h2><p class="text-on-surface-variant"><?= h($r['full_name']) ?> · <?= h($r['mobile']) ?> · Reason: <?= h($r['reason']) ?></p></div><span class="w-fit rounded-full bg-tertiary-fixed px-3 py-1 text-label-sm"><?= h(ucfirst($r['status'])) ?></span></div><div class="mt-5 grid grid-cols-5 gap-2 text-center text-label-sm text-on-surface-variant"><?php foreach(['Requested','Approved','Pickup','Received','Refunded'] as $i=>$step): ?><span><i class="mx-auto mb-1 block h-3 w-3 rounded-full <?= $i<2?'bg-primary':'bg-outline-variant' ?>"></i><?= $step ?></span><?php endforeach; ?></div><div class="mt-5 flex flex-wrap gap-2"><button class="rounded-lg border border-outline-variant px-4 py-2">View Details</button><button class="rounded-lg bg-primary px-4 py-2 text-on-primary">Approve</button><button class="rounded-lg border border-error px-4 py-2 text-error">Reject</button><button class="rounded-lg border border-outline-variant px-4 py-2">Process Refund</button></div></article>
<?php endforeach; ?>
</section>
</main></body></html>
