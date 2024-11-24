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
        return strlen($password) >= 10 &&
            preg_match('/[A-Z]/', $password) &&
            preg_match('/[0-9]/', $password) &&
            preg_match('/[!@#$%^&*()_+={};:"\'<>,.]/', $password);
    }

    // Funkcja do walidacji pełnego imienia
    public static function validateFullName($fullName)
    {
        return preg_match('/^[a-zA-Z]{3,15}$/', $fullName);
    }

    // Funkcja do walidacji nazwy użytkownika
    public static function validateUsername($username, $userRepository)
    {
        if (strlen($username) > 11 || !preg_match('/^[a-zA-Z0-9]+$/', $username)) {
            return false;
        }
        return $userRepository->findByLogin($username) === null;
    }

    // Funkcja do walidacji adresu email
    public static function validateEmail($email, $userRepository)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        return $userRepository->findByEmail($email) === null;
    }

    // Funkcja do generowania unikalnego tokenu CSRF
    public static function generateCSRFToken()
    {
        return bin2hex(random_bytes(32));
    }

    // Funkcja do weryfikacji tokenu CSRF
    public static function verifyCSRFToken($token)
    {
        return $token === $_SESSION['csrf_token'];
    }

    // Funkcja do ustawienia nagłówków bezpieczeństwa sesji
    public static function setSessionSecurityHeaders()
    {
        ini_set('session.cookie_httponly', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.cookie_secure', '1');
    }
}
