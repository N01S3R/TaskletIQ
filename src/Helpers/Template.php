<?php

namespace App\Helpers;

class Template
{
    public static function partials($name)
    {
        $partialPath = __DIR__ . "/../View/partials/{$name}.php";

        if (file_exists($partialPath)) {
            include($partialPath);
        } else {
            echo "Partial not found: {$name}";
        }
    }
}
