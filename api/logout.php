<?php
require_once __DIR__ . '/helpers.php';

$_SESSION = [];
session_destroy();

jsonResponse(['success' => true, 'message' => 'Logged out successfully.']);
