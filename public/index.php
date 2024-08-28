<?php

use Dotenv\Dotenv;

require_once '../vendor/autoload.php';
// Ścieżka do katalogu, w którym znajduje się plik .env
$dotenvPath = __DIR__ . '/../';
if (file_exists($dotenvPath . '.env')) {
    $dotenv = Dotenv::createImmutable($dotenvPath);
    $dotenv->safeLoad();
} else {
    die("Plik .env nie istnieje w ścieżce: $dotenvPath");
}
\App\App::run();
