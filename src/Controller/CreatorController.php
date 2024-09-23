<?php

namespace App\Controller;

use App\Entity\Task;
use App\Entity\User;
use App\Entity\Project;
use App\Entity\TaskUser;

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
        if ($this->auth->getUserId()) {
            $userId = $this->auth->getUserId();
            $taskRepository = $this->getRepository(Task::class);

            // Przygotowanie danych dla pulpit twórcy
            $data = [
                'pageTitle' => 'Pulpit',
                'tasksCount' => count($taskRepository->getAllTasksByUserId($userId)),
                'tasksStart' => $taskRepository->getTasksByProgress($userId, 1),
                'tasksInProgress' => $taskRepository->getTasksByProgress($userId, 2),
                'tasksDone' => $taskRepository->getTasksByProgress($userId, 3),
            ];
            $this->render('creator/creator_dashboard', $data);
        } else {
            header('Location: /login');
            exit();
        }
    }

    /**
     * Wyświetla zadania na podstawie identyfikatora postępu.
     * 
     * @param int $progressId Identyfikator postępu zadania.
     */
    public function displayTasksByProgress($progressId): void
    {
        $userId = $this->auth->getUserId();
        if ($userId && in_array($progressId, [1, 2, 3])) {
            $taskRepository = $this->getRepository(Task::class);

            // Pobranie pogrupowanych zadań
            $groupedTasks = $taskRepository->getGroupedTasksByProgress($userId, $progressId);

            $data = [
                'pageTitle' => 'Zadania w postępie',
                'groupedTasks' => $groupedTasks,
                'color' => $this->getProgressColor($progressId)
            ];

            $this->render('creator/creator_tasks_progress', $data);
        } else {
            header('Location: /login');
            exit();
        }
    }

    /**
     * Zwraca kolor na podstawie identyfikatora postępu.
     *
     * @param int $progressId Identyfikator postępu
     * @return string
     */
    private function getProgressColor(int $progressId): string
    {
        $colors = [1 => 'danger', 2 => 'warning', 3 => 'success'];
        return $colors[$progressId] ?? '';
    }

    /**
     * Wyświetla formularz przypisywania użytkownika do projektu.
     * Sprawdza uprawnienia użytkownika przed wyświetleniem formularza.
     */
    public function displayDelegateForm(): void
    {
        // Sprawdzamy, czy użytkownik jest zalogowany
        if ($this->auth->getUserId()) {
            // Pobieramy repozytorium użytkowników i projektów
            $userRepository = $this->getRepository(User::class);
            $projectRepository = $this->getRepository(Project::class);
            $loggedInUserId = $this->auth->getUserId();

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
     * Przypisuje użytkownika do zadania.
     * Obsługuje dane JSON z żądania i sprawdza uprawnienia.
     */
    public function assignUserToTask(): void
    {
        $responseData = [];
        if (!$this->auth->getUserId()) {
            $responseData['error'] = 'Brak odpowiednich uprawnień do wykonania tej operacji.';
            echo json_encode($responseData);
            return;
        }

        $taskRepository = $this->getRepository(Task::class);
        $userRepository = $this->getRepository(User::class);
        $taskUserRepository = $this->getRepository(TaskUser::class);
        $requestData = json_decode(file_get_contents('php://input'), true);
        $taskId = $requestData['taskId'] ?? null;
        $userId = $requestData['userId'] ?? null;

        $task = $taskRepository->find($taskId);
        if (!$task) {
            $responseData['error'] = 'Zadanie nie znalezione.';
            echo json_encode($responseData);
            return;
        }

        $user = $userRepository->find($userId);
        if (!$user) {
            $responseData['error'] = "Użytkownik o ID '{$userId}' nie znaleziony.";
            echo json_encode($responseData);
            return;
        }

        $isUserAssigned = $taskUserRepository->isUserAssignedToTask($taskId, $userId);
        if ($isUserAssigned) {
            $responseData['error'] = "Użytkownik '{$user->getUsername()}' jest już przypisany do zadania " . $task->getTaskName();
            echo json_encode($responseData);
            return;
        }

        $assignedUsersCount = $taskUserRepository->getAssignedUsersCount($taskId);
        if ($assignedUsersCount >= 12) {
            $responseData['error'] = "Osiągnięto maksymalną liczbę przypisanych użytkowników do tego zadania.";
            echo json_encode($responseData);
            return;
        }

        $taskUserRepository->assignTaskToUser($taskId, $userId);

        $responseData['success'] = 'Użytkownik "' . $user->getUsername() . '" został przypisany do zadania "' . $task->getTaskName() . '"';
        $responseData['user'] = [
            'user_id' => $user->getUserId(),
            'user_login' => $user->getLogin(),
            'user_avatar' => $user->getAvatar(),
        ];
        echo json_encode($responseData);
    }

    /**
     * Usuwa przypisanie użytkownika do zadania.
     * Sprawdza uprawnienia i dane wejściowe przed wykonaniem operacji.
     */
    public function unassignUserFromTask(): void
    {
        $responseData = [];
        if (!$this->auth->getUserId()) {
            $responseData['error'] = 'Brak odpowiednich uprawnień do wykonania tej operacji.';
            echo json_encode($responseData);
            return;
        }

        $taskRepository = $this->getRepository(Task::class);
        $userRepository = $this->getRepository(User::class);
        $taskUserRepository = $this->getRepository(TaskUser::class);
        $requestData = json_decode(file_get_contents('php://input'), true);
        $taskId = $requestData['taskId'] ?? null;
        $userId = $requestData['userId'] ?? null;

        $task = $taskRepository->find($taskId);
        if (!$task) {
            $responseData['error'] = 'Zadanie nie znalezione.';
            echo json_encode($responseData);
            return;
        }

        $user = $userRepository->find($userId);
        if (!$user) {
            $responseData['error'] = "Użytkownik o ID '{$userId}' nie znaleziony.";
            echo json_encode($responseData);
            return;
        }

        $isUserAssigned = $taskUserRepository->isUserAssignedToTask($taskId, $userId);
        if (!$isUserAssigned) {
            $responseData['error'] = "Użytkownik '{$user->getUsername()}' nie jest przypisany do zadania " . $task->getTaskName();
            echo json_encode($responseData);
            return;
        }

        $taskUserRepository->removeUserAssignmentToTask($taskId, $userId);

        $responseData['success'] = 'Przypisanie "' . $task->getTaskName() . '" do "' . $user->getUsername() . '" zostało usunięte';
        $responseData['user'] = [
            'user_id' => $user->getUserId(),
            'user_login' => $user->getLogin(),
            'user_avatar' => $user->getAvatar(),
        ];
        echo json_encode($responseData);
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
            $generatedToken = $user->getRegistrationToken();
            $users = $userRepository->findAll();
            $data = [
                'pageTitle' => 'Generuj kod użytkownikowi',
                'token' => $generatedToken,
                'users' => $users,
            ];
            $this->render('creator/creator_registration_code', $data);
        } else {
            header('Location: /login');
            exit();
        }
    }

    /**
     * Generuje nowy token dla użytkownika.
     * Sprawdza liczbę istniejących tokenów i generuje nowy token, jeśli to możliwe.
     *
     * @param string $token Wartość tokena
     */
    public function generateToken(string $token): void
    {
        if (!$this->checkRole('creator')) {
            $response = [
                'success' => false,
                'message' => 'Brak odpowiednich uprawnień do wykonania tej operacji.'
            ];
            echo json_encode($response);
            return;
        }

        $userId = $this->auth->getUserId();
        $tokenCount = $this->getTokenCountByUserId($userId);

        if ($tokenCount >= 10) {
            $response = [
                'success' => false,
                'message' => 'Użytkownik ma już maksymalną liczbę tokenów.'
            ];
            echo json_encode($response);
            return;
        }

        $newToken = md5($token . microtime());
        $this->setToken($userId, $newToken);

        $response = [
            'success' => true,
            'token' => $newToken
        ];
        echo json_encode($response);
    }
}
