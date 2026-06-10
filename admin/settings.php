<?php
require_once __DIR__ . '/../includes/config.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$db = getDB();
$page_title = 'Settings';
$active_page = 'settings';
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = 'Invalid CSRF token.';
    } else {
        $groqKey = trim($_POST['groq_api_key'] ?? '');
        $stmt = $db->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'groq_api_key'");
        $stmt->bind_param('s', $groqKey);
        if ($stmt->execute()) {
            $message = 'Settings saved successfully.';
        } else {
            $error = 'Failed to save settings.';
        }
        $stmt->close();
    }
}

$stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = 'groq_api_key' LIMIT 1");
$stmt->execute();
$groqApiKey = $stmt->get_result()->fetch_row()[0] ?? '';
$stmt->close();
?>
<?php require_once __DIR__ . '/includes/head.php'; ?>
<body class="bg-surface font-body-md text-on-surface flex min-h-screen">
<?php require_once __DIR__ . '/includes/sidebar.php'; ?>
<main class="flex-grow ml-64 p-margin-desktop bg-surface min-h-screen">
<header class="flex justify-between items-end mb-8">
<div>
<h2 class="text-display-lg font-display-lg text-primary">Settings</h2>
<p class="text-body-lg text-on-surface-variant mt-2">Manage API keys and site configuration.</p>
</div>
</header>

<?php if ($message): ?>
<div class="bg-primary-container text-on-primary-container px-5 py-3 rounded-xl mb-6 flex items-center gap-3">
<span class="material-symbols-outlined">check_circle</span>
<?= htmlspecialchars($message) ?>
</div>
<?php endif; ?>

<?php if ($error): ?>
<div class="bg-error-container text-on-error-container px-5 py-3 rounded-xl mb-6 flex items-center gap-3">
<span class="material-symbols-outlined">error</span>
<?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>

<form method="POST" class="bg-surface-container-lowest p-6 rounded-xl border border-outline-variant max-w-2xl">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

    <div class="mb-6">
        <label class="text-label-md text-on-surface block mb-2">GROQ API Key</label>
        <input type="password" name="groq_api_key" value="<?= htmlspecialchars($groqApiKey) ?>"
               class="w-full px-4 py-3 rounded-lg border border-outline bg-surface text-on-surface focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition-all"
               placeholder="gsk_...">
        <p class="text-label-sm text-on-surface-variant mt-2">
            Used by AyurBot AI chatbot and Dosha AI recommendations.
            Get your key at <a href="https://console.groq.com" target="_blank" class="text-primary underline">console.groq.com</a>.
        </p>
    </div>

    <button type="submit" class="bg-primary text-on-primary px-6 py-3 rounded-lg text-label-md hover:opacity-90 transition-all flex items-center gap-2">
        <span class="material-symbols-outlined">save</span> Save Settings
    </button>
</form>
</main>
</body>
</html>
