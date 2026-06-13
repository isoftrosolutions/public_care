<?php
/**
 * Web-accessible entry point for health reminder emails.
 *
 * Usage (browser):
 *   https://yourdomain.com/cron-reminders.php?key=your-secret-key
 *
 * Usage (cron / Task Scheduler):
 *   * * * * * php /path/to/cron-reminders.php
 *
 * Set CRON_SECRET_KEY in config-local.php:
 *   define('CRON_SECRET_KEY', 'your-random-secret-here');
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/send-reminders.php';
