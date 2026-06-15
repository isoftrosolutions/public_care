<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/includes/admin-crud.php';

admin_require_auth();

$db = getDB();
$page_title = 'Test Email';
$active_page = 'email-settings';
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
    $test_email = trim($_POST['test_email'] ?? '');
    if ($test_email === '') {
        $test_email = $_SESSION['user_name'] ? $db->query("SELECT email FROM users WHERE id = " . (int)$_SESSION['user_id'])->fetch_row()[0] : '';
    }

    if ($test_email === '') {
        $error = 'No recipient email available. Enter one manually.';
    } else {
        $result = send_email(
            $test_email,
            'Admin Test',
            'AyurViora — Test Email from Admin',
            '<h2>Test Email</h2><p>If you are reading this, your SMTP configuration is working correctly.</p><p>Sent from AyurViora admin panel.</p>',
            'Test email from AyurViora admin panel. SMTP is working correctly.'
        );

        if ($result['success']) {
            log_email_sent($db, $test_email, 'Admin Test', 'Test email', 'manual');
            $message = 'Test email sent successfully to ' . htmlspecialchars($test_email);
        } else {
            log_email_failed($db, $test_email, 'Admin Test', 'Test email', $result['error'], 'manual');
            $error = 'Test email failed: ' . htmlspecialchars($result['error']);
        }
    }
}

$admin_email = $db->query("SELECT email FROM users WHERE id = " . (int)$_SESSION['user_id'])->fetch_row()[0] ?? '';
?>
<?php require_once __DIR__ . '/includes/head.php'; ?>
<body class="bg-surface font-body-md text-on-surface flex min-h-screen">
<?php require_once __DIR__ . '/includes/sidebar.php'; ?>
<main class="flex-grow ml-64 p-margin-desktop bg-surface min-h-screen">

<header class="flex justify-between items-end mb-8">
<div>
<h2 class="text-display-lg font-display-lg text-primary">Test Email</h2>
<p class="text-body-lg text-on-surface-variant mt-2">Send a test email to verify your SMTP settings.</p>
</div>
<a href="email-settings.php" class="text-label-sm text-on-surface-variant hover:underline flex items-center gap-1"><span class="material-symbols-outlined text-sm">arrow_back</span> Email Settings</a>
</header>

<?php if ($message): ?><div class="mb-6 rounded-lg bg-primary-fixed text-on-primary-fixed px-4 py-3 text-body-md"><?= $message ?></div><?php endif; ?>
<?php if ($error): ?><div class="mb-6 rounded-lg bg-error-container text-on-error-container px-4 py-3 text-body-md"><?= $error ?></div><?php endif; ?>

<section class="bg-surface-container-lowest rounded-xl border border-outline-variant p-6 max-w-lg">
<form method="POST">
<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
<div>
<label class="text-label-sm text-on-surface-variant uppercase tracking-widest block mb-1">Send Test To</label>
<input type="email" name="test_email" value="<?= htmlspecialchars($admin_email) ?>" placeholder="your@email.com" class="w-full bg-surface border border-outline-variant text-label-sm rounded-lg focus:ring-primary px-3 py-2.5">
<p class="text-label-sm text-on-surface-variant mt-1">Leave blank to send to your admin account email.</p>
</div>
<div class="flex gap-3 mt-6">
<button type="submit" class="bg-primary text-on-primary px-6 py-2.5 rounded-lg text-label-sm hover:opacity-90 transition-opacity">Send Test Email</button>
</div>
</form>
</section>
</main>
</body>
</html>

