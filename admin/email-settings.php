<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/includes/admin-crud.php';

admin_require_auth();

$db = getDB();
$page_title = 'Email Settings';
$active_page = 'email-settings';
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
    $fields = [
        'mail_smtp_host' => trim($_POST['mail_smtp_host'] ?? ''),
        'mail_smtp_port' => (int)($_POST['mail_smtp_port'] ?? 587),
        'mail_smtp_auth' => isset($_POST['mail_smtp_auth']) ? '1' : '0',
        'mail_smtp_user' => trim($_POST['mail_smtp_user'] ?? ''),
        'mail_smtp_pass' => trim($_POST['mail_smtp_pass'] ?? ''),
        'mail_smtp_secure' => trim($_POST['mail_smtp_secure'] ?? 'tls'),
        'mail_from_email' => trim($_POST['mail_from_email'] ?? ''),
        'mail_from_name' => trim($_POST['mail_from_name'] ?? ''),
    ];

    foreach ($fields as $key => $value) {
        $stmt = $db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        $stmt->bind_param('ss', $key, $value);
        $stmt->execute();
        $stmt->close();
    }
    $message = 'Email settings saved successfully.';
}

$settings = get_mail_settings($db);
?>
<?php require_once __DIR__ . '/includes/head.php'; ?>
<body class="bg-surface font-body-md text-on-surface flex min-h-screen">
<?php require_once __DIR__ . '/includes/sidebar.php'; ?>
<main class="flex-grow ml-64 p-margin-desktop bg-surface min-h-screen">

<header class="flex justify-between items-end mb-8">
<div>
<h2 class="text-display-lg font-display-lg text-primary">Email Settings</h2>
<p class="text-body-lg text-on-surface-variant mt-2">Configure SMTP server and sender details for all outgoing mail.</p>
</div>
</header>

<?php if ($message): ?><div class="mb-6 rounded-lg bg-primary-fixed text-on-primary-fixed px-4 py-3 text-body-md"><?= htmlspecialchars($message) ?></div><?php endif; ?>
<?php if ($error): ?><div class="mb-6 rounded-lg bg-error-container text-on-error-container px-4 py-3 text-body-md"><?= htmlspecialchars($error) ?></div><?php endif; ?>

<section class="bg-surface-container-lowest rounded-xl border border-outline-variant p-6">
<form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

<div class="md:col-span-2">
<h3 class="text-headline-sm text-primary mb-2">SMTP Configuration</h3>
<hr class="border-outline-variant mb-4">
</div>

<div>
<label class="text-label-sm text-on-surface-variant uppercase tracking-widest block mb-1">SMTP Host</label>
<input type="text" name="mail_smtp_host" value="<?= htmlspecialchars($settings['mail_smtp_host'] ?? '') ?>" placeholder="smtp.gmail.com" class="w-full bg-surface border border-outline-variant text-label-sm rounded-lg focus:ring-primary px-3 py-2.5">
</div>

<div>
<label class="text-label-sm text-on-surface-variant uppercase tracking-widest block mb-1">SMTP Port</label>
<input type="number" name="mail_smtp_port" value="<?= htmlspecialchars($settings['mail_smtp_port'] ?? '587') ?>" class="w-full bg-surface border border-outline-variant text-label-sm rounded-lg focus:ring-primary px-3 py-2.5">
</div>

<div>
<label class="text-label-sm text-on-surface-variant uppercase tracking-widest block mb-1">SMTP Username</label>
<input type="text" name="mail_smtp_user" value="<?= htmlspecialchars($settings['mail_smtp_user'] ?? '') ?>" class="w-full bg-surface border border-outline-variant text-label-sm rounded-lg focus:ring-primary px-3 py-2.5">
</div>

<div>
<label class="text-label-sm text-on-surface-variant uppercase tracking-widest block mb-1">SMTP Password</label>
<input type="password" name="mail_smtp_pass" value="<?= htmlspecialchars($settings['mail_smtp_pass'] ?? '') ?>" class="w-full bg-surface border border-outline-variant text-label-sm rounded-lg focus:ring-primary px-3 py-2.5">
</div>

<div>
<label class="text-label-sm text-on-surface-variant uppercase tracking-widest block mb-1">Encryption</label>
<select name="mail_smtp_secure" class="w-full bg-surface border border-outline-variant text-label-sm rounded-lg focus:ring-primary px-3 py-2.5">
<option value="tls" <?= ($settings['mail_smtp_secure'] ?? 'tls') === 'tls' ? 'selected' : '' ?>>TLS</option>
<option value="ssl" <?= ($settings['mail_smtp_secure'] ?? '') === 'ssl' ? 'selected' : '' ?>>SSL</option>
<option value="" <?= ($settings['mail_smtp_secure'] ?? '') === '' ? 'selected' : '' ?>>None</option>
</select>
</div>

<div class="flex items-center gap-3 pt-6">
<input type="hidden" name="mail_smtp_auth" value="0">
<input type="checkbox" name="mail_smtp_auth" value="1" id="mail_smtp_auth" <?= ($settings['mail_smtp_auth'] ?? '1') === '1' ? 'checked' : '' ?> class="rounded border-outline-variant text-primary focus:ring-primary">
<label for="mail_smtp_auth" class="text-label-sm text-on-surface-variant">Use SMTP Authentication</label>
</div>

<div class="md:col-span-2 mt-4">
<h3 class="text-headline-sm text-primary mb-2">Sender Details</h3>
<hr class="border-outline-variant mb-4">
</div>

<div>
<label class="text-label-sm text-on-surface-variant uppercase tracking-widest block mb-1">From Email</label>
<input type="email" name="mail_from_email" value="<?= htmlspecialchars($settings['mail_from_email'] ?? '') ?>" placeholder="noreply@ayurviro.com" class="w-full bg-surface border border-outline-variant text-label-sm rounded-lg focus:ring-primary px-3 py-2.5">
</div>

<div>
<label class="text-label-sm text-on-surface-variant uppercase tracking-widest block mb-1">From Name</label>
<input type="text" name="mail_from_name" value="<?= htmlspecialchars($settings['mail_from_name'] ?? '') ?>" placeholder="Ayurviro" class="w-full bg-surface border border-outline-variant text-label-sm rounded-lg focus:ring-primary px-3 py-2.5">
</div>

<div class="md:col-span-2 flex gap-3 pt-4">
<button type="submit" class="bg-primary text-on-primary px-6 py-2.5 rounded-lg text-label-sm hover:opacity-90 transition-opacity">Save Settings</button>
<a href="email-test.php" class="border border-outline-variant text-on-surface-variant px-6 py-2.5 rounded-lg text-label-sm hover:bg-surface-container-high transition-colors">Send Test Email</a>
</div>
</form>
</section>
</main>
</body>
</html>
