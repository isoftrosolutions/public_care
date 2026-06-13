<?php
/**
 * SimpleMailer — lightweight email sender for XAMPP
 * Uses PHP's built-in mail() function with proper headers
 */
class SimpleMailer {
    public $From = '';
    public $FromName = '';
    public $Subject = '';
    public $Body = '';
    public $AltBody = '';
    public $isHTML = false;
    private $to = [];
    private $attachments = [];

    public function addAddress($email, $name = '') {
        $this->to[] = ['email' => $email, 'name' => $name];
    }

    public function addAttachment($path, $name = '') {
        if (is_file($path) && is_readable($path)) {
            $this->attachments[] = [
                'path' => $path,
                'name' => $name ?: basename($path),
                'type' => mime_content_type($path) ?: 'application/octet-stream',
            ];
        }
    }

    public function send() {
        if (empty($this->to)) return false;

        $success = true;
        foreach ($this->to as $recipient) {
            $to = $recipient['name'] ? "{$recipient['name']} <{$recipient['email']}>" : $recipient['email'];

            if (!empty($this->attachments)) {
                $boundary = 'ayurviro_' . bin2hex(random_bytes(12));
                $headers = "MIME-Version: 1.0\r\n";
                $headers .= "From: {$this->FromName} <{$this->From}>\r\n";
                $headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n";
                $headers .= "X-Mailer: PHP/" . phpversion();

                $message = "--$boundary\r\n";
                $message .= "Content-Type: " . ($this->isHTML ? 'text/html' : 'text/plain') . "; charset=UTF-8\r\n";
                $message .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
                $message .= ($this->isHTML ? $this->Body : ($this->AltBody ?: strip_tags($this->Body))) . "\r\n\r\n";

                foreach ($this->attachments as $attachment) {
                    $data = chunk_split(base64_encode(file_get_contents($attachment['path'])));
                    $filename = addcslashes($attachment['name'], '"\\');
                    $message .= "--$boundary\r\n";
                    $message .= "Content-Type: {$attachment['type']}; name=\"$filename\"\r\n";
                    $message .= "Content-Transfer-Encoding: base64\r\n";
                    $message .= "Content-Disposition: attachment; filename=\"$filename\"\r\n\r\n";
                    $message .= $data . "\r\n";
                }
                $message .= "--$boundary--";

                if (!@mail($to, $this->Subject, $message, $headers)) {
                    $success = false;
                }
            } elseif ($this->isHTML) {
                $headers = "MIME-Version: 1.0\r\n";
                $headers .= "Content-type: text/html; charset=UTF-8\r\n";
                $headers .= "From: {$this->FromName} <{$this->From}>\r\n";
                $headers .= "X-Mailer: PHP/" . phpversion();

                if (!@mail($to, $this->Subject, $this->Body, $headers)) {
                    $success = false;
                }
            } else {
                $headers = "From: {$this->FromName} <{$this->From}>\r\n";
                $headers .= "X-Mailer: PHP/" . phpversion();

                if (!@mail($to, $this->Subject, $this->AltBody ?: strip_tags($this->Body), $headers)) {
                    $success = false;
                }
            }
        }
        return $success;
    }

    public function clearAddresses() {
        $this->to = [];
    }

    public function clearAttachments() {
        $this->attachments = [];
    }
}
