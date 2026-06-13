<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/mail-helper.php';
require_once __DIR__ . '/email-templates.php';

$secretKey = defined('CRON_SECRET_KEY') ? CRON_SECRET_KEY : (getenv('CRON_SECRET_KEY') ?: '');
if (php_sapi_name() !== 'cli' && (!$secretKey || !isset($_GET['key']) || $_GET['key'] !== $secretKey)) {
    http_response_code(403);
    die('Access denied');
}

$conn = getDB();
$now = date('H:i');
$sent = 0;
$failed = 0;

define_mail_constants();

$stmt = $conn->prepare("SELECT hr.*, u.full_name, u.email, u.email_notifications
    FROM health_reminders hr
    JOIN users u ON hr.user_id = u.id
    WHERE hr.active = 1 AND hr.reminder_time = ? AND u.email IS NOT NULL AND u.email != '' AND (u.email_notifications = 1 OR u.email_notifications IS NULL)");
$stmt->bind_param("s", $now);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $template = getReminderEmailHtml($row['reminder_type'], $row['full_name']);

    $mail_result = send_email(
        $row['email'],
        $row['full_name'],
        $template['subject'],
        $template['html'],
        strip_tags($template['html'])
    );

    if ($mail_result['success']) {
        $log = $conn->prepare("INSERT INTO reminder_logs (user_id, reminder_type, status) VALUES (?, ?, 'sent')");
        $log->bind_param("is", $row['user_id'], $row['reminder_type']);
        $log->execute();
        $log->close();
        log_email_sent($conn, $row['email'], $row['full_name'], $template['subject'], 'reminder', 'reminder', (int)$row['id']);
        $sent++;
    } else {
        $log = $conn->prepare("INSERT INTO reminder_logs (user_id, reminder_type, status) VALUES (?, ?, 'failed')");
        $log->bind_param("is", $row['user_id'], $row['reminder_type']);
        $log->execute();
        $log->close();
        log_email_failed($conn, $row['email'], $row['full_name'], $template['subject'], $mail_result['error'] ?? 'Unknown', 'reminder', 'reminder', (int)$row['id']);
        $failed++;
    }
}
$stmt->close();

echo date('Y-m-d H:i:s') . " — Sent: $sent, Failed: $failed\n";
