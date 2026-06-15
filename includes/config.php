<?php
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.gc_maxlifetime', 7200);

define('SITE_NAME', 'AyurViora');
define('SITE_TAGLINE', 'Ancient Wisdom for Modern Living');

if (file_exists(__DIR__ . '/config-local.php')) {
    require_once __DIR__ . '/config-local.php';
}

$isLocal = isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'localhost') !== false;
define('BASE_URL', $isLocal ? '/www/public_care_ayurveda' : '');

require_once __DIR__ . '/../config/database.php';

require_once __DIR__ . '/mail-helper.php';

try {
    $mailDb = getDB();
    $mail_settings = get_mail_settings($mailDb);
    apply_mail_settings($mail_settings);
} catch (Throwable $e) {
    define_mail_constants();
}

session_start();

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 7200)) {
    $_SESSION = [];
    session_destroy();
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}
$_SESSION['last_activity'] = time();

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');

require_once __DIR__ . '/language.php';

// Language initialization
if (!isset($_SESSION['lang'])) {
    if (isset($_SESSION['user_id'])) {
        $langDb = getDB();
        $stmt = $langDb->prepare("SELECT preferred_language FROM users WHERE id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $_SESSION['lang'] = $result['preferred_language'] ?? 'hi';
        $stmt->close();
    } else {
        $browserLang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? 'hi', 0, 2);
        $_SESSION['lang'] = in_array($browserLang, ['hi', 'en', 'pa', 'bg', 'bho']) ? $browserLang : 'hi';
    }
}

if (isset($_GET['lang']) && in_array($_GET['lang'], ['hi', 'en', 'pa', 'bg', 'bho'])) {
    $_SESSION['lang'] = $_GET['lang'];
    if (isset($_SESSION['user_id'])) {
        $langDb = getDB();
        $stmt = $langDb->prepare("UPDATE users SET preferred_language = ? WHERE id = ?");
        $stmt->bind_param("si", $_GET['lang'], $_SESSION['user_id']);
        $stmt->execute();
        $stmt->close();
    }
    $redirect = strtok($_SERVER['REQUEST_URI'], '?');
    header('Location: ' . $redirect);
    exit;
}

$site_title = SITE_NAME;
$current_page = basename($_SERVER['SCRIPT_NAME']);
