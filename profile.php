<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/feature_helpers.php';
require_login();

$db = getDB();
$uid = (int)$_SESSION['user_id'];
$site_title = 'Profile Settings';
$user = current_user($db);
$notice = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
    if (($_POST['action'] ?? '') === 'profile') {
        $name = trim($_POST['full_name'] ?? '');
        $mobile = trim($_POST['mobile'] ?? '');
        if ($name !== '') {
            $stmt = $db->prepare('UPDATE users SET full_name = ?, mobile = ? WHERE id = ?');
            $stmt->bind_param('ssi', $name, $mobile, $uid);
            $stmt->execute();
            $_SESSION['user_name'] = $name;
            $notice = 'Profile updated.';
            $user = current_user($db);
        }
    } elseif (($_POST['action'] ?? '') === 'notifications') {
        $email_notifications = isset($_POST['email_notifications']) ? 1 : 0;
        $stmt = $db->prepare('UPDATE users SET email_notifications = ? WHERE id = ?');
        $stmt->bind_param('ii', $email_notifications, $uid);
        $stmt->execute();
        $notice = 'Notification preferences updated.';
        $user = current_user($db);
    } elseif (($_POST['action'] ?? '') === 'password') {
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        if (strlen($new) < 6 || $new !== $confirm) {
            $notice = 'Password must be at least 6 characters and match confirmation.';
        } else {
            $hash = password_hash($new, PASSWORD_DEFAULT);
            $stmt = $db->prepare('UPDATE users SET password = ? WHERE id = ?');
            $stmt->bind_param('si', $hash, $uid);
            $stmt->execute();
            $notice = 'Password updated.';
        }
    }
}

$addresses = [];
if (table_exists($db, 'saved_addresses')) {
    $stmt = $db->prepare('SELECT * FROM saved_addresses WHERE user_id = ? ORDER BY is_default DESC, created_at DESC');
    $stmt->bind_param('i', $uid);
    $stmt->execute();
    $addresses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
$family_count = 0;
if (table_exists($db, 'family_members')) {
    $stmt = $db->prepare('SELECT COUNT(*) FROM family_members WHERE user_id = ?');
    $stmt->bind_param('i', $uid);
    $stmt->execute();
    $family_count = (int)$stmt->get_result()->fetch_row()[0];
}
require_once __DIR__ . '/includes/header.php';
?>
<section class="pt-32 pb-section-gap px-margin-mobile md:px-margin-desktop max-w-container-max mx-auto">
<div class="grid gap-8 lg:grid-cols-[300px_1fr]">
    <aside class="h-fit rounded-xl border border-outline-variant bg-surface-container-lowest p-5 lg:sticky lg:top-28">
        <div class="text-center">
            <div class="relative mx-auto mb-4 h-24 w-24 rounded-full bg-primary-fixed text-primary flex items-center justify-center">
                <span class="material-symbols-outlined text-5xl">account_circle</span>
                <button class="absolute bottom-0 right-0 rounded-full bg-primary p-2 text-on-primary"><span class="material-symbols-outlined text-sm">edit</span></button>
            </div>
            <h1 class="font-headline-md text-headline-md text-primary"><?= h($user['full_name'] ?? 'Account') ?></h1>
            <p class="text-on-surface-variant"><?= h($user['email'] ?? '') ?></p>
            <span class="mt-3 inline-flex rounded-full bg-tertiary-fixed px-3 py-1 text-label-sm text-on-tertiary-fixed">Ayurveda Wellness</span>
        </div>
        <nav class="mt-6 space-y-1">
            <?php foreach (['account' => 'Account', 'addresses' => 'Addresses', 'family' => 'Family Members', 'notifications' => 'Notifications', 'payments' => 'Payment Methods', 'language' => 'Language', 'privacy' => 'Privacy & Security', 'support' => 'Support'] as $id => $label): ?>
            <a class="flex items-center justify-between rounded-lg px-3 py-2 text-on-surface-variant hover:bg-surface-container" href="#<?= $id ?>"><?= $label ?><span class="material-symbols-outlined text-sm">chevron_right</span></a>
            <?php endforeach; ?>
        </nav>
    </aside>
    <div class="space-y-6">
        <?php if ($notice): ?><div class="rounded-lg bg-primary-fixed p-4 text-primary"><?= h($notice) ?></div><?php endif; ?>
        <section id="account" class="rounded-xl border border-outline-variant bg-surface-container-lowest p-6">
            <h2 class="font-headline-md text-headline-md text-primary">Account</h2>
            <form method="post" class="mt-5 grid gap-4 md:grid-cols-2">
                <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
                <input type="hidden" name="action" value="profile">
                <label>Full name<input name="full_name" value="<?= h($user['full_name'] ?? '') ?>" class="mt-1 w-full rounded-lg border-outline-variant focus:border-primary focus:ring-primary"></label>
                <label>Email<input value="<?= h($user['email'] ?? '') ?>" disabled class="mt-1 w-full rounded-lg border-outline-variant bg-surface-container"></label>
                <label>Phone<input name="mobile" value="<?= h($user['mobile'] ?? '') ?>" class="mt-1 w-full rounded-lg border-outline-variant focus:border-primary focus:ring-primary"></label>
                <label>Member since<input value="<?= !empty($user['created_at']) ? date('d M Y', strtotime($user['created_at'])) : 'Today' ?>" disabled class="mt-1 w-full rounded-lg border-outline-variant bg-surface-container"></label>
                <button class="md:col-span-2 rounded-lg bg-primary px-5 py-3 text-on-primary">Save Profile</button>
            </form>
            <form method="post" class="mt-8 grid gap-4 md:grid-cols-3">
                <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
                <input type="hidden" name="action" value="password">
                <input name="new_password" type="password" placeholder="New password" class="rounded-lg border-outline-variant focus:border-primary focus:ring-primary">
                <input name="confirm_password" type="password" placeholder="Confirm password" class="rounded-lg border-outline-variant focus:border-primary focus:ring-primary">
                <button class="rounded-lg border border-primary px-5 py-3 text-primary">Update Password</button>
            </form>
        </section>
        <section id="addresses" class="rounded-xl border border-outline-variant bg-surface-container-lowest p-6">
            <div class="flex justify-between gap-4"><h2 class="font-headline-md text-headline-md text-primary">Saved Addresses</h2><a href="<?= BASE_URL ?>/checkout.php" class="text-primary">Add New Address</a></div>
            <div class="mt-5 grid gap-4 md:grid-cols-2">
                <?php foreach ($addresses as $address): ?>
                <article class="rounded-lg border border-outline-variant p-4">
                    <div class="flex justify-between"><strong><?= h($address['label'] ?: 'Address') ?></strong><?php if ($address['is_default']): ?><span class="rounded-full bg-primary-fixed px-2 text-label-sm text-primary">Default</span><?php endif; ?></div>
                    <p class="mt-2 text-on-surface-variant"><?= h(trim(($address['address_line1'] ?? '') . ' ' . ($address['address_line2'] ?? '') . ', ' . ($address['city'] ?? '') . ' ' . ($address['pincode'] ?? ''))) ?></p>
                </article>
                <?php endforeach; ?>
                <?php if (!$addresses): ?><div class="md:col-span-2"><?= empty_state('home_pin', 'No saved addresses', 'Addresses added during checkout will appear here.', 'Go to Checkout', BASE_URL . '/checkout.php') ?></div><?php endif; ?>
            </div>
        </section>
        <section id="family" class="rounded-xl border border-outline-variant bg-surface-container-lowest p-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div><h2 class="font-headline-md text-headline-md text-primary">Family Members</h2><p class="text-on-surface-variant"><?= $family_count ?> profiles managed in your account.</p></div>
            <a href="<?= BASE_URL ?>/my-family.php" class="rounded-lg bg-primary px-5 py-3 text-on-primary">Manage</a>
        </section>
        <section id="notifications" class="rounded-xl border border-outline-variant bg-surface-container-lowest p-6">
            <h2 class="font-headline-md text-headline-md text-primary">Notifications</h2>
            <form method="post" class="mt-4 grid gap-3 md:grid-cols-2">
                <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
                <input type="hidden" name="action" value="notifications">
                <?php
                $notif_prefs = [
                    'email_notifications' => ['label' => 'Email notifications', 'key' => 'email_notifications'],
                ];
                foreach ($notif_prefs as $col => $pref):
                    $checked = !empty($user[$col]);
                ?>
                <label class="flex items-center justify-between rounded-lg bg-surface-container p-3"><?= h($pref['label']) ?><input type="hidden" name="<?= $col ?>" value="0"><input type="checkbox" name="<?= $col ?>" value="1" <?= $checked ? 'checked' : '' ?> class="rounded text-primary" onchange="this.form.submit()"></label>
                <?php endforeach; ?>
            </form>
        </section>
        <section id="payments" class="rounded-xl border border-outline-variant bg-surface-container-lowest p-6">
            <h2 class="font-headline-md text-headline-md text-primary">Payment Methods</h2>
            <div class="mt-4 grid gap-4 md:grid-cols-3">
                <?= stat_card('account_balance_wallet', 'Wallet Balance', money($user['wallet_balance'] ?? 0), '') ?>
                <a href="<?= BASE_URL ?>/wallet.php" class="rounded-xl border border-outline-variant p-5 text-primary">Open Wallet</a>
                <a href="<?= BASE_URL ?>/payment.php" class="rounded-xl border border-outline-variant p-5 text-primary">Add Payment Method</a>
            </div>
        </section>
        <section id="language" class="rounded-xl border border-outline-variant bg-surface-container-lowest p-6 flex justify-between gap-4">
            <div><h2 class="font-headline-md text-headline-md text-primary">Language</h2><p class="text-on-surface-variant">Current language: <?= h($_SESSION['lang'] ?? 'en') ?></p></div>
            <a href="<?= BASE_URL ?>/language-selection.php" class="rounded-lg border border-primary px-5 py-3 text-primary">Change</a>
        </section>
        <section id="privacy" class="rounded-xl border border-outline-variant bg-surface-container-lowest p-6">
            <h2 class="font-headline-md text-headline-md text-primary">Privacy & Security</h2>
            <div class="mt-4 grid gap-3 md:grid-cols-2"><button class="rounded-lg border border-outline-variant p-3 text-left">Two-factor authentication</button><button class="rounded-lg border border-outline-variant p-3 text-left">Download my data</button><button class="rounded-lg border border-outline-variant p-3 text-left">Manage sessions</button><button class="rounded-lg border border-outline-variant p-3 text-left">Terms & Privacy</button></div>
        </section>
        <section id="support" class="rounded-xl border border-outline-variant bg-surface-container-lowest p-6">
            <h2 class="font-headline-md text-headline-md text-primary">Support</h2>
            <div class="mt-4 flex flex-wrap gap-3"><a href="<?= BASE_URL ?>/contact-us.php" class="rounded-lg border border-outline-variant px-4 py-2">Contact support</a><a href="<?= BASE_URL ?>/about-us.php" class="rounded-lg border border-outline-variant px-4 py-2">About AyurViora v1.0.0</a><a href="<?= BASE_URL ?>/logout.php" class="rounded-lg border border-error px-4 py-2 text-error">Logout</a></div>
        </section>
    </div>
</div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>

