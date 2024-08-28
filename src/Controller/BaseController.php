<?php

namespace App\Controller;

use App\View;
use App\Model\TaskModel;
use App\Model\UserModel;
use App\Model\CreatorModel;
use App\Model\ProjectModel;
use App\Helpers\AuthHelpers;
use App\Model\OperatorModel;

class BaseController
{
    protected $view;
    protected $taskModel;
    protected $userModel;
    protected $projectModel;
    protected $creatorModel;
    protected $operatorModel;

    public function __construct()
    {
        $this->view = new View();
        $this->taskModel = new TaskModel();
        $this->userModel = new UserModel();
        $this->projectModel = new ProjectModel();
        $this->creatorModel = new CreatorModel();
        $this->operatorModel = new OperatorModel();
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
     * Sprawdza, czy użytkownik jest zalogowany.
     *
     * @return bool True, jeśli użytkownik jest zalogowany; false w przeciwnym razie
     */
    public function checkLogged(): bool
    {
        if (isset($_SESSION['user_id']) && isset($_COOKIE['PHPSESSID'])) {
            if ($_COOKIE['PHPSESSID'] !== session_id()) {
                session_regenerate_id(true);
            }
            return true;
        }
        return false;
    }

    /**
     * Sprawdza, czy użytkownik ma określoną rolę.
     *
     * @param string $role Rola do sprawdzenia
     * @return bool True, jeśli użytkownik ma określoną rolę; false w przeciwnym razie
     */
    public function checkRole(string $role): bool
    {
        // Sprawdzenie czy użytkownik jest zalogowany
        if (!isset($_SESSION['user_id']) || !isset($_COOKIE['PHPSESSID'])) {
            return false;
        }

        // Jeśli sesja istnieje, sprawdzenie roli użytkownika
        $userRole = $this->userModel->getUsersRole($_SESSION['user_id']);

        if ($userRole === $role) {
            // Dodatkowo, sprawdzenie czy sesja nadal jest zgodna z PHPSESSID
            if ($_COOKIE['PHPSESSID'] !== session_id()) {
                session_regenerate_id(true);
            }
            return true;
        }

        return false;
    }

    /**
     * Przykładowa metoda, która korzysta z klasy AuthHelpers do sanitizacji danych wejściowych.
     */
    public function someMethod()
    {
        $sanitizedInput = AuthHelpers::sanitizeInput($_POST['input']);
    }

    public function getUserId()
    {
        return $this->userModel->getLoggedInUserId();
    }
}
