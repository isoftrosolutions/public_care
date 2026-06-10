<?php
/**
 * Email Reminder Sender
 * Run every minute via cron: * * * * * php /path/to/includes/send-reminders.php
 * Or call via HTTP with a secret key: /send-reminders.php?key=your-secret-key
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/PHPMailer.php';
require_once __DIR__ . '/email-templates.php';

$secretKey = 'pca-reminder-secret-key-2024';
if (php_sapi_name() !== 'cli' && (!isset($_GET['key']) || $_GET['key'] !== $secretKey)) {
    http_response_code(403);
    die('Access denied');
}

$conn = getDB();
$now = date('H:i');
$sent = 0;
$failed = 0;

$stmt = $conn->prepare("SELECT hr.*, u.full_name, u.email 
    FROM health_reminders hr 
    JOIN users u ON hr.user_id = u.id 
    WHERE hr.active = 1 AND hr.reminder_time = ? AND u.email IS NOT NULL AND u.email != ''");
$stmt->bind_param("s", $now);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $mail = new SimpleMailer();
    $mail->From = 'noreply@publiccareayurveda.com';
    $mail->FromName = 'Ayurwellness';
    $mail->addAddress($row['email'], $row['full_name']);

    $template = getReminderEmailHtml($row['reminder_type'], $row['full_name']);
    $mail->Subject = $template['subject'];
    $mail->Body = $template['html'];
    $mail->isHTML = true;

    if ($mail->send()) {
        $log = $conn->prepare("INSERT INTO reminder_logs (user_id, reminder_type, status) VALUES (?, ?, 'sent')");
        $log->bind_param("is", $row['user_id'], $row['reminder_type']);
        $log->execute();
        $log->close();
        $sent++;
    } else {
        $log = $conn->prepare("INSERT INTO reminder_logs (user_id, reminder_type, status) VALUES (?, ?, 'failed')");
        $log->bind_param("is", $row['user_id'], $row['reminder_type']);
        $log->execute();
        $log->close();
        $failed++;
    }
}
$stmt->close();

echo "Sent: $sent, Failed: $failed\n";
