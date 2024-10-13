<?php

namespace App\Config;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer
{
    private $mailer;

    public function __construct()
    {
        $this->mailer = new PHPMailer(true);

        $mailHost = $_ENV['MAIL_HOST'];
        $mailUsername = $_ENV['MAIL_USERNAME'];
        $mailPassword = $_ENV['MAIL_PASSWORD'];
        $mailPort = $_ENV['MAIL_PORT'];

        $this->mailer->isSMTP();
        $this->mailer->Host = $mailHost;
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = $mailUsername;
        $this->mailer->Password = $mailPassword;
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port = $mailPort;
    }

    public function sendPasswordEmail(string $email, string $password): bool
    {
        try {
            $this->mailer->setFrom($_ENV['MAIL_FROM'], 'TaskletIQ');
            $this->mailer->addAddress($email);
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Twoje nowe hasło';
            $this->mailer->Body = 'Twoje nowe hasło to: <b>' . $password . '</b>';
            $this->mailer->AltBody = 'Twoje nowe hasło to: ' . $password;

            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            error_log("Błąd wysyłania e-maila: " . $e->getMessage());
            return false;
        }
    }
}
