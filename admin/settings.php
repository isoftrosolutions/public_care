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
        $settings = [
            'openai_api_key' => trim($_POST['openai_api_key'] ?? ''),
            'openai_model' => trim($_POST['openai_model'] ?? 'gpt-5.2'),
        ];

        $ok = true;
        foreach ($settings as $key => $value) {
            $stmt = $db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
            $stmt->bind_param('ss', $key, $value);
            if (!$stmt->execute()) {
                $ok = false;
            }
            $stmt->close();
        }

        $message = $ok ? 'OpenAI settings saved successfully.' : '';
        $error = $ok ? '' : 'Failed to save one or more settings.';
    }
}

$settings = [];
$result = $db->query("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('openai_api_key', 'openai_model')");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
}
$openaiApiKey = $settings['openai_api_key'] ?? '';
$openaiModel = $settings['openai_model'] ?? 'gpt-5.2';
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
        <label class="text-label-md text-on-surface block mb-2">OpenAI API Key</label>
        <input type="password" name="openai_api_key" value="<?= htmlspecialchars($openaiApiKey) ?>"
               class="w-full px-4 py-3 rounded-lg border border-outline bg-surface text-on-surface focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition-all"
               placeholder="sk-...">
        <p class="text-label-sm text-on-surface-variant mt-2">
            Used by AyurBot chatbot, AI Health Assistant, and Dosha AI recommendations.
            You can also define <code>OPENAI_API_KEY</code> in <code>includes/config-local.php</code> for local development.
        </p>
    </div>

    <div class="mb-6">
        <label class="text-label-md text-on-surface block mb-2">OpenAI Model</label>
        <input type="text" name="openai_model" value="<?= htmlspecialchars($openaiModel) ?>"
               class="w-full px-4 py-3 rounded-lg border border-outline bg-surface text-on-surface focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition-all"
               placeholder="gpt-5.2">
        <p class="text-label-sm text-on-surface-variant mt-2">
            Default model for text health assistant responses. Override with <code>OPENAI_MODEL</code> in local config if needed.
        </p>
    </div>

    <button type="submit" class="bg-primary text-on-primary px-6 py-3 rounded-lg text-label-md hover:opacity-90 transition-all flex items-center gap-2">
        <span class="material-symbols-outlined">save</span> Save Settings
    </button>
</form>
</main>
</body>
</html>
