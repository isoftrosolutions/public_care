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

    public function addAddress($email, $name = '') {
        $this->to[] = ['email' => $email, 'name' => $name];
    }

    public function send() {
        if (empty($this->to)) return false;

        $success = true;
        foreach ($this->to as $recipient) {
            $to = $recipient['name'] ? "{$recipient['name']} <{$recipient['email']}>" : $recipient['email'];

            if ($this->isHTML) {
                $headers = "MIME-Version: 1.0\r\n";
                $headers .= "Content-type: text/html; charset=UTF-8\r\n";
                $headers .= "From: {$this->FromName} <{$this->From}>\r\n";
                $headers .= "X-Mailer: PHP/" . phpversion();

                if (!mail($to, $this->Subject, $this->Body, $headers)) {
                    $success = false;
                }
            } else {
                $headers = "From: {$this->FromName} <{$this->From}>\r\n";
                $headers .= "X-Mailer: PHP/" . phpversion();

                if (!mail($to, $this->Subject, $this->AltBody ?: strip_tags($this->Body), $headers)) {
                    $success = false;
                }
            }
        }
        return $success;
    }

    public function clearAddresses() {
        $this->to = [];
    }
}
