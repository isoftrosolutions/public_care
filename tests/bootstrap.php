<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Mock HTTP env for code that reads $_SERVER
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en';
$_SERVER['REQUEST_METHOD'] = 'GET';

// Scaffold $_SESSION for tests that read it
if (!isset($_SESSION)) {
    $_SESSION = ['csrf_token' => 'test-token-placeholder'];
}

require_once __DIR__ . '/../vendor/autoload.php';
