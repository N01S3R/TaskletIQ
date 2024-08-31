<?php

namespace App\Controller;

use App\View;
use App\Config\DoctrineConfig;
use App\Helpers\AuthHelpers;
use Doctrine\ORM\EntityManager;

class BaseController
{
    protected $view;
    protected $entityManager;

    public function __construct()
    {
        $this->view = new View();
        $this->entityManager = DoctrineConfig::createEntityManager();
    }

    /**
     * Renderuje widok z przekazanymi danymi.
     *
     * @param string $viewName Nazwa widoku
     * @param array $data Tablica z danymi do przekazania do widoku (opcjonalnie)
     */
    public function render(string $viewName, array $data = [])
    {
        $this->view->render($viewName, $data);
    }

    /**
     * Funkcja do debugowania - wyświetla zmienną i przerywa działanie skryptu.
     *
     * @param mixed $item Zmienna do wyświetlenia
     */
    public function dd($item)
    {
        echo '<pre>';
        var_dump($item);
        echo '</pre>';
        die();
    }

    /**
     * Przykładowa metoda, która korzysta z klasy AuthHelpers do sanitizacji danych wejściowych.
     */
    public function someMethod()
    {
        $sanitizedInput = AuthHelpers::sanitizeInput($_POST['input']);
    }

    /**
     * Zwraca repozytorium dla określonej encji.
     *
     * @param string $entityClass Nazwa klasy encji
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getRepository(string $entityClass)
    {
        return $this->entityManager->getRepository($entityClass);
    }
}
