<?php

namespace App\Controller;

use App\View;
use App\Entity\User;
use App\Entity\Token;
use App\Service\Auth;
use App\Helpers\AuthHelpers;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

class BaseController
{
    protected $view;
    protected $entityManager;
    protected $auth;

    /**
     * Konstruktor klasy BaseController.
     * 
     * @param EntityManager $entityManager Obiekt EntityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->view = new View();
        $this->entityManager = $entityManager;
        $this->auth = $this->createAuth();
    }

    /**
     * Tworzy instancję klasy Auth.
     * 
     * @return Auth
     */
    protected function createAuth(): Auth
    {
        return new Auth($this->getRepository(User::class), $this->getRepository(Token::class));
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
     * @return EntityRepository
     */
    protected function getRepository(string $entityClass): EntityRepository
    {
        return $this->entityManager->getRepository($entityClass);
    }


    /**
     * Sprawdza, czy aktualny użytkownik ma określoną rolę.
     *
     * @param string $role Nazwa roli
     * @return bool
     */
    protected function checkRole(string $role): bool
    {
        $userId = $this->auth->getUserId();
        if ($userId) {
            $userRepository = $this->entityManager->getRepository(User::class);
            $user = $userRepository->find($userId);
            return $user && $user->hasRole($role);
        }
        return false;
    }
}
