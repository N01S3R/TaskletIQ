<?php

namespace App\Helpers;

use App\Service\Auth;

class Template
{
    private static $auth;

    public static function setAuth(Auth $auth)
    {
        self::$auth = $auth;
    }

    public static function partials($name, $data = [])
    {
        $partialPath = __DIR__ . "/../View/partials/{$name}.php";

        if (file_exists($partialPath)) {
            // Jeśli instancja Auth jest ustawiona, możesz teraz uzyskać ID użytkownika
            if (self::$auth) {
                $userId = self::$auth->getUserId(); // Pobieranie ID użytkownika
                $data['user_id'] = $userId; // Dodaj ID użytkownika do danych
            }

            include($partialPath);
            extract($data);
        } else {
            echo "Partial not found: {$name}";
        }
    }
}
