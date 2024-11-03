<?php

namespace App\Config;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use Dotenv\Dotenv;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class DoctrineConfig
{
    public static function createEntityManager(): EntityManager
    {
        // Wczytywanie zmiennych środowiskowych z pliku .env
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../'); // Ścieżka do katalogu z plikiem .env
        $dotenv->load();

        $isDevMode = $_ENV['APP_ENV'] === 'local'; // Włącz tryb debugowania jeśli środowisko to 'local'

        // Ustawienia Doctrine
        $config = Setup::createAnnotationMetadataConfiguration(
            [__DIR__ . "/Entity"], // Ścieżka do katalogu z encjami
            $isDevMode,
            null,
            null,
            false
        );
        $config->setAutoGenerateProxyClasses(true);

        // Ustawienia pamięci podręcznej
        $cache = new FilesystemAdapter(); // Używamy FilesystemAdapter jako pamięci podręcznej
        $config->setMetadataCache($cache);

        // Konfiguracja połączenia z bazą danych
        $conn = [
            'driver'   => 'pdo_mysql',
            'host'     => $_ENV['DB_HOST'],
            'dbname'   => $_ENV['DB_NAME'],
            'user'     => $_ENV['DB_USER'],
            'password' => $_ENV['DB_PASSWORD'],
            'charset'  => 'utf8mb4',
        ];

        return EntityManager::create($conn, $config);
    }
}
