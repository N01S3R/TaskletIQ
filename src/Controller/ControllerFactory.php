<?php

namespace App\Controller;

use Doctrine\ORM\EntityManager;

class ControllerFactory
{
    /**
     * Tworzy instancję kontrolera na podstawie jego nazwy.
     *
     * @param string $controllerName Nazwa kontrolera
     * @param EntityManager $entityManager EntityManager do przekazania
     * @return mixed Instancja kontrolera
     * @throws \Exception Jeśli kontroler o podanej nazwie nie istnieje
     */
    public static function create($controllerName, EntityManager $entityManager)
    {
        $controllerClass = 'App\\Controller\\' . $controllerName;
        if (class_exists($controllerClass)) {
            return new $controllerClass($entityManager);
        }
        throw new \Exception("Controller not found: " . $controllerClass);
    }
}
