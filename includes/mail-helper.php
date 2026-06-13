<?php
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function get_mailer(): PHPMailer
{
    $mail = new PHPMailer(true);

    $smtp_host = defined('MAIL_SMTP_HOST') ? MAIL_SMTP_HOST : '';
    if ($smtp_host) {
        $mail->isSMTP();
        $mail->Host = $smtp_host;
        $mail->SMTPAuth = defined('MAIL_SMTP_AUTH') ? MAIL_SMTP_AUTH : true;
        $mail->Username = defined('MAIL_SMTP_USER') ? MAIL_SMTP_USER : '';
        $mail->Password = defined('MAIL_SMTP_PASS') ? MAIL_SMTP_PASS : '';
        $mail->SMTPSecure = defined('MAIL_SMTP_SECURE') ? MAIL_SMTP_SECURE : PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = defined('MAIL_SMTP_PORT') ? (int)MAIL_SMTP_PORT : 587;
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
            ],
        ];
    }

    $mail->CharSet = 'UTF-8';
    $mail->Encoding = 'base64';
    $mail->XMailer = 'Ayurviro';
    $mail->From = defined('MAIL_FROM_EMAIL') ? MAIL_FROM_EMAIL : 'noreply@ayurviro.com';
    $mail->FromName = defined('MAIL_FROM_NAME') ? MAIL_FROM_NAME : 'Ayurviro';

    return $mail;
}

function email_log_table(mysqli $db): void
{
    $db->query("CREATE TABLE IF NOT EXISTS email_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        recipient VARCHAR(255) NOT NULL,
        recipient_name VARCHAR(255) DEFAULT NULL,
        subject VARCHAR(500) NOT NULL,
        body TEXT DEFAULT NULL,
        status ENUM('sent','failed') NOT NULL DEFAULT 'sent',
        error_message TEXT DEFAULT NULL,
        email_type VARCHAR(100) DEFAULT NULL,
        reference_type VARCHAR(50) DEFAULT NULL,
        reference_id INT DEFAULT NULL,
        sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_status (status),
        INDEX idx_type (email_type),
        INDEX idx_sent_at (sent_at)
    )");
}

function log_email_sent(mysqli $db, string $recipient, ?string $recipient_name, string $subject, ?string $email_type = null, ?string $reference_type = null, ?int $reference_id = null): int
{
    email_log_table($db);
    $stmt = $db->prepare("INSERT INTO email_log (recipient, recipient_name, subject, status, email_type, reference_type, reference_id) VALUES (?, ?, ?, 'sent', ?, ?, ?)");
    $stmt->bind_param('sssssi', $recipient, $recipient_name, $subject, $email_type, $reference_type, $reference_id);
    $stmt->execute();
    $id = $stmt->insert_id;
    $stmt->close();
    return $id;
}

function log_email_failed(mysqli $db, string $recipient, ?string $recipient_name, string $subject, string $error_message, ?string $email_type = null, ?string $reference_type = null, ?int $reference_id = null): int
{
    email_log_table($db);
    $stmt = $db->prepare("INSERT INTO email_log (recipient, recipient_name, subject, status, error_message, email_type, reference_type, reference_id) VALUES (?, ?, ?, 'failed', ?, ?, ?, ?)");
    $stmt->bind_param('ssssssi', $recipient, $recipient_name, $subject, $error_message, $email_type, $reference_type, $reference_id);
    $stmt->execute();
    $id = $stmt->insert_id;
    $stmt->close();
    return $id;
}

function get_mail_settings(mysqli $db): array
{
    $settings = [];
    $keys = ['mail_smtp_host', 'mail_smtp_port', 'mail_smtp_auth', 'mail_smtp_user', 'mail_smtp_pass', 'mail_smtp_secure', 'mail_from_email', 'mail_from_name'];
    foreach ($keys as $key) {
        $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
        $stmt->bind_param('s', $key);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $settings[$key] = $row['setting_value'] ?? null;
        $stmt->close();
    }
    return $settings;
}

function apply_mail_settings(array $settings): void
{
    if (!empty($settings['mail_smtp_host'])) {
        define('MAIL_SMTP_HOST', $settings['mail_smtp_host']);
        define('MAIL_SMTP_PORT', (int)($settings['mail_smtp_port'] ?? 587));
        define('MAIL_SMTP_AUTH', (bool)($settings['mail_smtp_auth'] ?? true));
        define('MAIL_SMTP_USER', $settings['mail_smtp_user'] ?? '');
        define('MAIL_SMTP_PASS', $settings['mail_smtp_pass'] ?? '');
        define('MAIL_SMTP_SECURE', $settings['mail_smtp_secure'] ?? 'tls');
    }
    if (!empty($settings['mail_from_email'])) {
        define('MAIL_FROM_EMAIL', $settings['mail_from_email']);
        define('MAIL_FROM_NAME', $settings['mail_from_name'] ?? 'Ayurviro');
    }
}

function define_mail_constants(): void
{
    if (!defined('MAIL_FROM_EMAIL')) {
        define('MAIL_FROM_EMAIL', 'noreply@ayurviro.com');
        define('MAIL_FROM_NAME', 'Ayurviro');
    }
    if (!defined('MAIL_SMTP_HOST')) {
        define('MAIL_SMTP_HOST', '');
    }
}

function send_email(string $to, string $to_name, string $subject, string $html_body, string $alt_body = '', ?string $attachment_path = null, ?string $attachment_name = null): array
{
    try {
        $mail = get_mailer();
        $mail->addAddress($to, $to_name);
        $mail->Subject = $subject;
        $mail->isHTML(true);
        $mail->Body = $html_body;
        $mail->AltBody = $alt_body ?: strip_tags($html_body);

        if ($attachment_path && is_file($attachment_path)) {
            $mail->addAttachment($attachment_path, $attachment_name ?: basename($attachment_path));
        }

        $mail->send();
        return ['success' => true, 'error' => null];
    } catch (Exception $e) {
        $error = $mail->ErrorInfo ?? $e->getMessage();
        return ['success' => false, 'error' => $error];
    }
}
