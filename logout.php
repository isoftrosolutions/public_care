<?php
require_once __DIR__ . '/includes/config.php';
$_SESSION = [];
session_regenerate_id(true);
session_destroy();
header('Location: ' . BASE_URL . '/index.php');
exit;
