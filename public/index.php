<?php

use Dotenv\Dotenv;

require_once '../vendor/autoload.php';

try {
    $dotenvPath = __DIR__ . '/../';
    if (file_exists($dotenvPath . '.env')) {
        $dotenv = Dotenv::createImmutable($dotenvPath);
        $dotenv->safeLoad();
    } else {
        throw new \RuntimeException("Plik .env nie istnieje w ścieżce: $dotenvPath");
    }

    \App\App::run();
} catch (\Throwable $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo "Wystąpił problem z aplikacją. Spróbuj ponownie później.";
}
