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

            $this->mailer->Body = '
        <!DOCTYPE html>
        <html lang="pl">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Twoje nowe hasło</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
        </head>
        <body>
            <div class="container mt-5">
                <h1 class="text-center">Witaj!</h1>
                <div class="alert alert-success" role="alert">
                    Twoje nowe hasło to: <strong>' . htmlspecialchars($password) . '</strong>
                </div>
                <p class="text-center">
                    <a href="login.php" class="btn btn-primary">Zaloguj się</a>
                </p>
            </div>
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
        </body>
        </html>
        ';

            $this->mailer->AltBody = 'Twoje nowe hasło to: ' . $password;

            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            error_log("Błąd wysyłania e-maila: " . $e->getMessage());
            return false;
        }
    }
}
