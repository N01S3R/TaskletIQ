<?php

namespace App;

class View
{

    public static function render($view, $data = [])
    {
        extract($data);

        $viewFile = __DIR__ . '/View/' . $view . '.php';

        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            // Obsługa braku pliku widoku
            die('404 - View not found');
        }
    }
}
