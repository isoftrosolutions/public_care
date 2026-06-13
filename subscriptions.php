<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/feature_helpers.php';
require_login();

$db = getDB();
$uid = (int)$_SESSION['user_id'];
$site_title = 'Subscriptions';
$notice = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '') && table_exists($db, 'subscriptions')) {
    $plan = trim($_POST['plan_name'] ?? 'Monthly Medicine Plan');
    $frequency = $_POST['frequency'] ?? 'monthly';
    $next = $_POST['next_delivery_date'] ?: date('Y-m-d', strtotime('+30 days'));
    $amount = (float)($_POST['amount'] ?? 499);
    $stmt = $db->prepare("INSERT INTO subscriptions (user_id, plan_name, frequency, status, next_delivery_date, total_amount) VALUES (?, ?, ?, 'active', ?, ?)");
    $stmt->bind_param('isssd', $uid, $plan, $frequency, $next, $amount);
    $stmt->execute();
    $notice = 'Subscription created.';
}
$subs = [];
if (table_exists($db, 'subscriptions')) {
    $stmt = $db->prepare('SELECT * FROM subscriptions WHERE user_id = ? ORDER BY created_at DESC');
    $stmt->bind_param('i', $uid);
    $stmt->execute();
    $subs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
if (!$subs) {
    $subs = [['plan_name' => 'Ashwagandha Monthly Care', 'frequency' => 'monthly', 'status' => 'active', 'next_delivery_date' => date('Y-m-d', strtotime('+5 days')), 'total_amount' => 499]];
}
require_once __DIR__ . '/includes/header.php';
?>
<section class="pt-32 pb-section-gap px-margin-mobile md:px-margin-desktop max-w-container-max mx-auto">
<div class="mb-8 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
    <div><h1 class="font-display-lg text-display-lg text-primary">My Subscriptions</h1><p class="text-on-surface-variant">Never run out of your essential Ayurvedic medicines.</p></div>
    <button data-create class="rounded-lg bg-primary px-5 py-3 text-on-primary">Create New Subscription</button>
</div>
<?php if ($notice): ?><div class="mb-6 rounded-lg bg-primary-fixed p-4 text-primary"><?= h($notice) ?></div><?php endif; ?>
<div class="grid gap-6 lg:grid-cols-2">
<?php foreach ($subs as $sub): $next = strtotime($sub['next_delivery_date'] ?? '+30 days'); ?>
    <article class="rounded-xl border border-outline-variant bg-surface-container-lowest p-6">
        <div class="flex gap-4">
            <div class="flex h-20 w-20 items-center justify-center rounded-xl bg-primary-fixed text-primary"><span class="material-symbols-outlined text-4xl">medication</span></div>
            <div class="flex-1"><div class="flex justify-between gap-3"><h2 class="font-headline-md text-headline-md"><?= h($sub['plan_name'] ?? 'Monthly Plan') ?></h2><span class="h-fit rounded-full bg-primary-fixed px-3 py-1 text-label-sm text-primary"><?= h(ucfirst($sub['status'] ?? 'active')) ?></span></div><p class="text-on-surface-variant">Delivery: Every <?= h($sub['frequency'] ?? 'monthly') ?></p><p class="mt-1 font-title-lg text-primary"><?= money($sub['total_amount'] ?? 0) ?>/cycle <span class="ml-2 rounded-full bg-tertiary-fixed px-2 py-1 text-label-sm text-on-tertiary-fixed">Save 15%</span></p></div>
        </div>
        <div class="mt-5 rounded-lg bg-surface-container p-4"><p class="font-label-lg">Next delivery: <?= date('d M Y', $next) ?></p><p class="text-on-surface-variant"><?= max(0, ceil(($next - time()) / 86400)) ?> days left</p><div class="mt-4 flex items-center justify-between text-label-sm text-on-surface-variant"><span>Delivered</span><span>Delivered</span><span class="text-primary">Scheduled</span></div><div class="mt-2 grid grid-cols-3 gap-2"><span class="h-2 rounded-full bg-primary"></span><span class="h-2 rounded-full bg-primary"></span><span class="h-2 rounded-full bg-primary-fixed"></span></div></div>
        <div class="mt-5 flex flex-wrap gap-2"><button class="rounded-lg border border-outline-variant px-4 py-2">Skip Next Delivery</button><button class="rounded-lg border border-outline-variant px-4 py-2">Pause Subscription</button><button data-create class="rounded-lg border border-outline-variant px-4 py-2">Edit Schedule</button><button class="rounded-lg border border-error px-4 py-2 text-error">Cancel</button></div>
    </article>
<?php endforeach; ?>
</div>
<div class="mt-10 rounded-xl border border-outline-variant bg-surface-container-lowest p-6"><div class="mb-5 flex flex-wrap gap-2"><?php foreach (['Active','Paused','Completed','Cancelled'] as $tab): ?><button class="rounded-full border border-outline-variant px-4 py-2 first:bg-primary first:text-on-primary"><?= $tab ?></button><?php endforeach; ?></div><p class="text-on-surface-variant">Subscription cycle history and auto-pay receipts will appear here.</p></div>
</section>
<div id="sub-modal" class="fixed inset-0 z-[60] hidden items-center justify-center bg-black/40 p-4">
    <form method="post" class="w-full max-w-2xl rounded-xl bg-surface-container-lowest p-6">
        <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
        <div class="flex justify-between"><h3 class="font-headline-md text-headline-md text-primary">Create Subscription</h3><button type="button" onclick="closeSubModal()"><span class="material-symbols-outlined">close</span></button></div>
        <div class="mt-5 grid gap-4 md:grid-cols-2"><input name="plan_name" class="rounded-lg border-outline-variant" placeholder="Plan/product name"><select name="frequency" class="rounded-lg border-outline-variant"><option value="weekly">Every 7 days</option><option value="biweekly">Every 15 days</option><option value="monthly" selected>Every 30 days</option><option value="quarterly">Every 60 days</option></select><input name="next_delivery_date" type="date" class="rounded-lg border-outline-variant"><input name="amount" type="number" value="499" class="rounded-lg border-outline-variant"><select class="rounded-lg border-outline-variant"><option>Default saved address</option></select><select class="rounded-lg border-outline-variant"><option>UPI AutoPay</option><option>Card</option><option>Wallet</option></select></div>
        <button class="mt-5 w-full rounded-lg bg-primary py-3 text-on-primary">Start Subscription</button>
    </form>
</div>
<script>document.querySelectorAll('[data-create]').forEach(b=>b.addEventListener('click',()=>{const m=document.getElementById('sub-modal');m.classList.remove('hidden');m.classList.add('flex')}));function closeSubModal(){const m=document.getElementById('sub-modal');m.classList.add('hidden');m.classList.remove('flex')}</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
