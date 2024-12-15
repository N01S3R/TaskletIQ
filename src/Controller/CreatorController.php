<?php

namespace App\Controller;

use App\Entity\Task;
use App\Entity\User;
use App\Entity\Token;
use App\Entity\Project;
use App\Entity\TaskUser;
use App\Helpers\AuthHelpers;

/**
 * Kontroler do zarządzania zadaniami twórcy.
 */
class CreatorController extends BaseController
{
    /**
     * Wyświetla pulpit twórcy z zadaniami.
     * Sprawdza uprawnienia użytkownika i wyświetla odpowiednią stronę.
     */
    public function displayDashboard(): void
    {
        if ($this->checkRole('creator')) {
            $userId = $this->auth->getUserId();
            $taskRepository = $this->getRepository(Task::class);

            // Przygotowanie danych dla pulpit twórcy
            $data = [
                'pageTitle' => 'Pulpit',
                'tasksCount' => count($taskRepository->getAllTasksByUserId($userId)),
                'tasksStart' => count($taskRepository->getTasksByProgress($userId, 1)),
                'tasksInProgress' => count($taskRepository->getTasksByProgress($userId, 2)),
                'tasksDone' => count($taskRepository->getTasksByProgress($userId, 3)),
            ];
            $this->render('creator/creator_dashboard', $data);
        } else {
            header('Location: /login');
            exit();
        }
    }

    /**
     * Wyświetla formularz przypisywania użytkownika do projektu.
     * Sprawdza uprawnienia użytkownika przed wyświetleniem formularza.
     */
    public function displayDelegateForm(): void
    {
        // Sprawdzamy, czy użytkownik jest zalogowany
        if ($this->checkRole('creator')) {
            // Pobieramy repozytorium użytkowników i projektów
            $userRepository = $this->getRepository(User::class);
            $projectRepository = $this->getRepository(Project::class);
            $loggedInUserId = $this->auth->getUserId();

            // Generowanie tokena CSRF
            $csrf = $_SESSION['csrf_token'] = AuthHelpers::generateCSRFToken();

            // Pobieramy zalogowanego użytkownika na podstawie ID
            $loggedInUser = $userRepository->find($loggedInUserId);
            $registrationToken = $loggedInUser ? $loggedInUser->getRegistrationToken() : null;

            // Pobieramy projekty użytkownika wraz z zadaniami i przypisanymi użytkownikami
            $projects = $projectRepository->getProjectWithTasksAndUsers($loggedInUserId);

            // Pobieramy wszystkich użytkowników z tym samym tokenem rejestracyjnym
            $users = $registrationToken ? $userRepository->findUsersByRegistrationToken($registrationToken, $loggedInUserId) : [];

            // Przygotowujemy dane do widoku
            $data = [
                'pageTitle' => 'Przypisz użytkownika',
                'userProjects' => $projects,
                'users' => $users,
                'csrfToken' => $csrf,
            ];

            // Renderujemy widok
            $this->render('creator/creator_delegate', $data);
        } else {
            // Przekierowanie na stronę logowania, jeśli użytkownik nie jest zalogowany
            header('Location: /login');
            exit();
        }
    }

    /**
     * Wyświetla formularz generowania kodu rejestracyjnego.
     * Sprawdza rolę użytkownika przed wyświetleniem formularza.
     */
    public function displayRegistrationCode(): void
    {
        if ($this->checkRole('creator')) {
            $userRepository = $this->getRepository(User::class);
            $user = $userRepository->find($this->auth->getUserId());
            $registrationToken = $user->getRegistrationToken();

            // Generowanie tokena CSRF
            $csrf = $_SESSION['csrf_token'] = AuthHelpers::generateCSRFToken();

            // Znajdź innych użytkowników z tym samym tokenem
            $users = $userRepository->findUsersByRegistrationToken($registrationToken, $user->getUserId());

            // Przekazywanie tokena CSRF do widoku
            $data = [
                'pageTitle' => 'Generuj kod użytkownikowi',
                'token' => $registrationToken,
                'users' => $users,
                'csrfToken' => $csrf,
                'baseUrl' => $_ENV['BASE_URL']
            ];

            $this->render('creator/creator_registration_code', $data);
        } else {
            header('Location: /login');
            exit();
        }
    }
}
