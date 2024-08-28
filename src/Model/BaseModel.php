<?php

namespace App\Model;

use App\Database;

/**
 * Klasa bazowa modelu zapewniająca instancję połączenia z bazą danych.
 */
class BaseModel
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }
}
