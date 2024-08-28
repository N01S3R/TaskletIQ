<?php

namespace App;

use Dotenv\Dotenv;

class App
{
    public static function run()
    {
        // Rozpoczęcie sesji
        session_start();

        // Inicjalizacja połączenia z bazą danych i ładowanie tras
        require_once 'Database.php';
        require_once 'Routes.php';
    }
}
