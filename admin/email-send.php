<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/includes/admin-crud.php';

admin_require_auth();

$db = getDB();
$page_title = 'Send Email';
$active_page = 'email-send';
$message = '';
$error = '';
$sent = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
    $to_email = trim($_POST['to_email'] ?? '');
    $to_name = trim($_POST['to_name'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $body = trim($_POST['body'] ?? '');

    if ($to_email === '' || $subject === '' || $body === '') {
        $error = 'Recipient email, subject, and body are required.';
    } else {
        $result = send_email($to_email, $to_name ?: $to_email, $subject, nl2br(htmlspecialchars($body)), $body);

        if ($result['success']) {
            log_email_sent($db, $to_email, $to_name ?: null, $subject, 'manual');
            $message = 'Email sent successfully to ' . htmlspecialchars($to_email);
            $sent = true;
        } else {
            log_email_failed($db, $to_email, $to_name ?: null, $subject, $result['error'], 'manual');
            $error = 'Failed to send email: ' . htmlspecialchars($result['error']);
        }
    }
}

$users = $db->query("SELECT id, full_name, email FROM users WHERE email IS NOT NULL AND email != '' ORDER BY full_name ASC")->fetch_all(MYSQLI_ASSOC);
?>
<?php require_once __DIR__ . '/includes/head.php'; ?>
<body class="bg-surface font-body-md text-on-surface flex min-h-screen">
<?php require_once __DIR__ . '/includes/sidebar.php'; ?>
<main class="flex-grow ml-64 p-margin-desktop bg-surface min-h-screen">

<header class="flex justify-between items-end mb-8">
<div>
<h2 class="text-display-lg font-display-lg text-primary">Send Email</h2>
<p class="text-body-lg text-on-surface-variant mt-2">Compose and send a manual email to any address.</p>
</div>
</header>

<?php if ($message): ?><div class="mb-6 rounded-lg bg-primary-fixed text-on-primary-fixed px-4 py-3 text-body-md"><?= $message ?></div><?php endif; ?>
<?php if ($error): ?><div class="mb-6 rounded-lg bg-error-container text-on-error-container px-4 py-3 text-body-md"><?= $error ?></div><?php endif; ?>

<section class="bg-surface-container-lowest rounded-xl border border-outline-variant p-6">
<form method="POST" class="grid grid-cols-1 gap-5">
<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

<?php if (!$sent): ?>
<div class="grid grid-cols-1 md:grid-cols-2 gap-5">
<div>
<label class="text-label-sm text-on-surface-variant uppercase tracking-widest block mb-1">To <span class="text-error">*</span></label>
<div class="relative">
<span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-sm">mail</span>
<input type="email" name="to_email" required value="<?= htmlspecialchars($_POST['to_email'] ?? '') ?>" class="w-full bg-surface border border-outline-variant text-label-sm rounded-lg focus:ring-primary pl-9 py-2.5" list="user-emails" placeholder="email@example.com">
<datalist id="user-emails">
<?php foreach ($users as $u): ?>
<option value="<?= htmlspecialchars($u['email']) ?>"><?= htmlspecialchars($u['full_name']) ?></option>
<?php endforeach; ?>
</datalist>
</div>
</div>
<div>
<label class="text-label-sm text-on-surface-variant uppercase tracking-widest block mb-1">Recipient Name</label>
<input type="text" name="to_name" value="<?= htmlspecialchars($_POST['to_name'] ?? '') ?>" class="w-full bg-surface border border-outline-variant text-label-sm rounded-lg focus:ring-primary px-3 py-2.5">
</div>
</div>

<div>
<label class="text-label-sm text-on-surface-variant uppercase tracking-widest block mb-1">Subject <span class="text-error">*</span></label>
<input type="text" name="subject" required value="<?= htmlspecialchars($_POST['subject'] ?? '') ?>" class="w-full bg-surface border border-outline-variant text-label-sm rounded-lg focus:ring-primary px-3 py-2.5">
</div>

<div>
<label class="text-label-sm text-on-surface-variant uppercase tracking-widest block mb-1">Body <span class="text-error">*</span></label>
<textarea name="body" rows="12" class="w-full bg-surface border border-outline-variant text-label-sm rounded-lg focus:ring-primary px-3 py-2.5 font-mono"><?= htmlspecialchars($_POST['body'] ?? '') ?></textarea>
<p class="text-label-sm text-on-surface-variant mt-1">Plain text only — line breaks are converted to HTML.</p>
</div>

<div class="flex gap-3 pt-2">
<button type="submit" class="bg-primary text-on-primary px-6 py-2.5 rounded-lg text-label-sm hover:opacity-90 transition-opacity"><span class="material-symbols-outlined text-sm align-middle mr-1">send</span> Send Email</button>
</div>
<?php endif; ?>

<?php if ($sent): ?>
<div class="flex gap-3">
<a href="email-send.php" class="bg-primary text-on-primary px-6 py-2.5 rounded-lg text-label-sm hover:opacity-90 transition-opacity">Send Another</a>
<a href="email-logs.php" class="border border-outline-variant text-on-surface-variant px-6 py-2.5 rounded-lg text-label-sm hover:bg-surface-container-high transition-colors">View Logs</a>
</div>
<?php endif; ?>
</form>
</section>
</main>
</body>
</html>
