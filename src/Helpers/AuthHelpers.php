<?php

namespace App\Helpers;

class AuthHelpers
{
    // Funkcja do filtrowania danych wejściowych
    public static function sanitizeInput($input)
    {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    // Funkcja do walidacji hasła
    public static function validatePassword($password)
    {
        // Sprawdź, czy hasło spełnia określone wymagania (np. długość)
        return strlen($password) >= 10;
    }

    // Funkcja do generowania unikalnego tokenu CSRF
    public static function generateCSRFToken()
    {
        return bin2hex(random_bytes(32));
    }

    // Funkcja do weryfikacji tokenu CSRF
    public static function verifyCSRFToken($token)
    {
        // Sprawdź, czy token jest zgodny z tym, co przechowywane jest w sesji
        return $token === $_SESSION['csrf_token'];
    }

    // Funkcja do ustawienia nagłówków bezpieczeństwa sesji
    public static function setSessionSecurityHeaders()
    {
        // Ustaw nagłówki bezpieczeństwa sesji
        ini_set('session.cookie_httponly', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.cookie_secure', '1'); // Ustawia tylko w przypadku używania protokołu HTTPS
    }
}
