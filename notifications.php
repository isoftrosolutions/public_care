<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/feature_helpers.php';
require_login();

$db = getDB();
$uid = (int)$_SESSION['user_id'];
$site_title = 'Notifications';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '') && table_exists($db, 'notifications')) {
    $stmt = $db->prepare('UPDATE notifications SET is_read = TRUE WHERE user_id = ?');
    $stmt->bind_param('i', $uid);
    $stmt->execute();
}
$notifications = [];
if (table_exists($db, 'notifications')) {
    $stmt = $db->prepare('SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 80');
    $stmt->bind_param('i', $uid);
    $stmt->execute();
    $notifications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
if (!$notifications) {
    $notifications = [
        ['title' => 'Your order #ORD-1234 has been shipped!', 'body' => 'Track your Ayurvedic medicine delivery in real time.', 'type' => 'order', 'is_read' => 0, 'created_at' => date('Y-m-d H:i:s'), 'cta' => 'Track Order', 'href' => BASE_URL . '/order-tracking.php'],
        ['title' => 'Reminder: Dr. Priya Sharma tomorrow at 10:00 AM', 'body' => 'Join a few minutes early and keep your notes ready.', 'type' => 'consultation', 'is_read' => 0, 'created_at' => date('Y-m-d H:i:s', strtotime('-1 day')), 'cta' => 'View Appointment', 'href' => BASE_URL . '/appointment-booking.php'],
        ['title' => '30% off on Triphala Churna', 'body' => 'Today only. Add it to cart before the offer ends.', 'type' => 'promo', 'is_read' => 1, 'created_at' => date('Y-m-d H:i:s', strtotime('-3 days')), 'cta' => 'Shop Now', 'href' => BASE_URL . '/shop.php'],
    ];
}
$icons = ['order' => ['inventory_2', 'bg-primary'], 'consultation' => ['calendar_month', 'bg-emerald-500'], 'reminder' => ['favorite', 'bg-violet-500'], 'promo' => ['sell', 'bg-amber-500'], 'payment' => ['account_balance_wallet', 'bg-blue-500'], 'system' => ['notifications', 'bg-gray-500']];
require_once __DIR__ . '/includes/header.php';
?>
<section class="pt-32 pb-section-gap px-margin-mobile md:px-margin-desktop max-w-container-max mx-auto">
<div class="mb-8 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
    <div><h1 class="font-display-lg text-display-lg text-primary">Notifications</h1><p class="text-on-surface-variant">Orders, appointments, health reminders, offers and system updates.</p></div>
    <form method="post" class="flex gap-3"><input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>"><button class="text-primary">Mark all as read</button><a href="<?= BASE_URL ?>/profile.php#notifications" class="rounded-lg border border-outline-variant px-3 py-2 text-primary"><span class="material-symbols-outlined">settings</span></a></form>
</div>
<div class="mb-6 flex flex-wrap gap-2"><?php foreach (['All','Unread','Offers','Orders','Health','Appointments'] as $tab): ?><button class="rounded-full border border-outline-variant px-4 py-2 first:bg-primary first:text-on-primary"><?= $tab ?></button><?php endforeach; ?></div>
<div class="space-y-4">
<?php foreach ($notifications as $note): $meta = $icons[$note['type'] ?? 'system'] ?? $icons['system']; ?>
    <article data-note class="rounded-xl border border-outline-variant bg-surface-container-lowest p-5 hover:bg-surface-container">
        <div class="flex gap-4">
            <span class="material-symbols-outlined h-11 w-11 rounded-full <?= $meta[1] ?> p-2.5 text-white"><?= $meta[0] ?></span>
            <div class="flex-1">
                <div class="flex items-start justify-between gap-3"><h2 class="<?= empty($note['is_read']) ? 'font-bold' : 'font-medium' ?> text-on-surface"><?= h($note['title']) ?></h2><?php if (empty($note['is_read'])): ?><span class="mt-2 h-2 w-2 rounded-full bg-blue-500"></span><?php endif; ?></div>
                <p class="mt-1 text-on-surface-variant"><?= h($note['body'] ?? '') ?></p>
                <div class="mt-3 flex flex-wrap items-center gap-3"><span class="text-label-sm text-on-surface-variant"><?= date('d M Y, h:i A', strtotime($note['created_at'] ?? 'now')) ?></span><a class="text-label-lg text-primary" href="<?= h($note['href'] ?? BASE_URL . '/notifications.php') ?>"><?= h($note['cta'] ?? 'View Details') ?></a><button data-delete class="text-label-sm text-error">Delete</button></div>
            </div>
        </div>
    </article>
<?php endforeach; ?>
</div>
</section>
<script>document.querySelectorAll('[data-delete]').forEach(button => button.addEventListener('click', e => e.target.closest('[data-note]').remove()));</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
