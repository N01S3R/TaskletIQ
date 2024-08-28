<?php

namespace App\Controller;

class ControllerFactory
{
    /**
     * Tworzy instancję kontrolera na podstawie jego nazwy.
     *
     * @param string $controllerName Nazwa kontrolera
     * @return mixed Instancja kontrolera
     * @throws \Exception Jeśli kontroler o podanej nazwie nie istnieje
     */
    public static function create($controllerName)
    {
        $controllerClass = 'App\\Controller\\' . $controllerName;
        if (class_exists($controllerClass)) {
            return new $controllerClass();
        }
        throw new \Exception("Controller not found: " . $controllerClass);
    }
}
