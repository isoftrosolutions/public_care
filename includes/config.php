<?php
session_start();
require_once __DIR__ . '/../config/database.php';

define('SITE_NAME', 'Public Care Ayurveda');
define('SITE_TAGLINE', 'Ancient Wisdom for Modern Living');
define('BASE_URL', '/www/public_care_ayurveda');

$site_title = SITE_NAME;
$current_page = basename($_SERVER['SCRIPT_NAME']);
