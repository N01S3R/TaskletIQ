<?php

namespace App\Config;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use Dotenv\Dotenv;

class DoctrineConfig
{
    public static function createEntityManager(): EntityManager
    {
        // Wczytywanie zmiennych środowiskowych z pliku .env
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../'); // Ścieżka do katalogu z plikiem .env
        $dotenv->load();

        $isDevMode = getenv('APP_ENV') === 'local'; // Włącz tryb debugowania jeśli środowisko to 'local'

        // Ustawienia Doctrine
        $config = Setup::createAnnotationMetadataConfiguration(
            [__DIR__ . "/Entity"], // Ścieżka do katalogu z encjami
            $isDevMode,
            null,
            null,
            false
        );

        // Konfiguracja połączenia z bazą danych
        $conn = [
            'driver'   => 'pdo_mysql',
            'host'     => $_ENV['DB_HOST'],
            'dbname'   => $_ENV['DB_NAME'],
            'user'     => $_ENV['DB_USER'],
            'password' => $_ENV['DB_PASSWORD'],
        ];

        return EntityManager::create($conn, $config);
    }
}
