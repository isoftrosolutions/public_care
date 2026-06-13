<?php
if (!isset($_SESSION)) {
    require_once __DIR__ . '/../includes/config.php';
}

$_SESSION = [];
session_destroy();

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']
    );
}

header('Location: ' . BASE_URL . '/login.php');
exit;
