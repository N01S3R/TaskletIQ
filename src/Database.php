<?php

namespace App;

use PDOException;
use PDO;

class Database
{
    private static $db;

    public static function getInstance()
    {
        if (!isset(self::$db)) {
            try {
                self::$db = new PDO('mysql:host=' . $_ENV['DB_HOST'] . ';dbname=' . $_ENV['DB_NAME'], $_ENV['DB_USER'], $_ENV['DB_PASSWORD']);
                self::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                die("Connection failed: " . $e->getMessage());
            }
        }

        return self::$db;
    }
}
