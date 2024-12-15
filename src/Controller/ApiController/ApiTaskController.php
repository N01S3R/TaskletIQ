<?php

namespace App\Controller\ApiController;

use PDOException;
use App\Entity\Task;
use App\Entity\User;
use App\Entity\TaskUser;
use App\Helpers\AuthHelpers;
use App\Controller\BaseController;

class ApiTaskController extends BaseController
{
    /**
     * Tworzy nowe zadanie na podstawie danych przesłanych metodą POST.
     *
     * @return void
     */
    public function createTask(): void
    {
        // Sprawdź rolę użytkownika
        if (!$this->checkRole('creator')) {
            header('Location: /login');
            exit();
        }

        $responseData = [];
        $requestData = json_decode(file_get_contents('php://input'), true);

        // Używaj operatora ?? do przypisania wartości domyślnych
        $projectId = $requestData['project_id'] ?? null;
        $title = $requestData['task_name'] ?? null;
        $description = $requestData['task_description'] ?? null;
        $descriptionLong = $requestData['task_description_long'] ?? null;
        $userId = $this->auth->getUserId();

        // Sprawdź, czy wszystkie wymagane dane są dostępne
        if (empty($projectId) || empty($title) || empty($description) || empty($descriptionLong)) {
            $responseData['error'] = 'Wszystkie pola muszą być wypełnione.';
        } else {
            // Upewnij się, że userId jest prawidłowy
            if ($userId !== null && is_int($userId)) {
                $taskRepository = $this->getRepository(Task::class);

                // Sprawdź, czy zadanie o tej samej nazwie już istnieje
                if ($taskRepository->taskExists($title, $userId)) {
                    $responseData['error'] = 'Zadanie o podanej nazwie już istnieje.';
                } else {
                    // Dodaj nowe zadanie
                    $newTask = $taskRepository->addTask($projectId, $title, $description, $descriptionLong, $userId);
                    $responseData['success'] = 'Dodano zadanie "' . htmlspecialchars($title) . '"';

                    // Pobierz status i kolor na podstawie postępu
                    $taskStatusData = $this->getTaskStatus($newTask->getTaskProgress());

                    $responseData['task'] = [
                        'task_id' => $newTask->getTaskId(),
                        'task_name' => $newTask->getTaskName(),
                        'task_description' => $newTask->getTaskDescription(),
                        'task_description_long' => $newTask->getTaskDescriptionLong(),
                        'task_progress' => $newTask->getTaskProgress(),
                        'task_status' => $taskStatusData['status'],
                        'task_color' => $taskStatusData['color'],
                    ];
                }
            } else {
                $responseData['error'] = 'Nieprawidłowy identyfikator użytkownika.';
            }
        }

        // Zwróć dane w formacie JSON
        echo json_encode($responseData);
    }

    /**
     * Zwraca status i kolor na podstawie postępu zadania.
     *
     * @param int $taskProgress
     * @return array
     */
    private function getTaskStatus(int $taskProgress): array
    {
        switch ($taskProgress) {
            case 0:
                return ['status' => 'Nowy', 'color' => 'bg-primary'];
            case 1:
                return ['status' => 'Rozpoczęty', 'color' => 'bg-danger'];
            case 2:
                return ['status' => 'W trakcie', 'color' => 'bg-warning'];
            case 3:
                return ['status' => 'Ukończony', 'color' => 'bg-success'];
            default:
                return ['status' => 'Nieznany', 'color' => 'bg-primary'];
        }
    }

    /**
     * Aktualizuje zadanie na podstawie danych przesłanych metodą PUT.
     *
     * @param int $taskId Identyfikator zadania do aktualizacji
     * @return void
     */
    public function updateTask(int $taskId): void
    {
        $userId = $this->auth->getUserId();
        // Sprawdź rolę użytkownika
        if (!$this->checkRole('creator')) {
            echo json_encode(['error' => 'Brak uprawnień do wykonania tej operacji.']);
            return;
        }

        // Utwórz tablicę, która będzie przechowywać dane odpowiedzi
        $responseData = [];

        // Pobierz dane z ciała żądania
        $requestData = json_decode(file_get_contents('php://input'), true);

        // Pobierz dane z ciała żądania
        $title = $requestData['task_name'] ?? null;
        $description = $requestData['task_description'] ?? null;
        $descriptionLong = $requestData['task_description_long'] ?? null;

        // Sprawdź, czy wszystkie pola zostały wypełnione
        if (empty($title) || empty($description) || empty($descriptionLong)) {
            // Ustaw odpowiedź na błąd
            $responseData['error'] = 'Wszystkie pola muszą być wypełnione.';
        } else {
            $taskRepository = $this->getRepository(Task::class);
            // Sprawdź, czy zadanie o podanym identyfikatorze istnieje
            $existingTask = $taskRepository->getTaskById($taskId, $userId);
            if (!$existingTask) {
                // Zadanie o podanym identyfikatorze nie istnieje
                $responseData['error'] = 'Zadanie o podanym identyfikatorze nie istnieje.';
            } else {
                // Aktualizuj zadanie
                $result = $taskRepository->setTask($taskId, $title, $description, $descriptionLong);
                if ($result) {
                    // Aktualizacja zadania powiodła się
                    // Pobierz zaktualizowane dane zadania
                    $responseData['success'] = 'Zaktualizowano zadanie "' . $title . '"';
                    $responseData['task'] = $existingTask;
                } else {
                    // Aktualizacja zadania nie powiodła się
                    $responseData['error'] = 'Nie udało się zaktualizować zadania.';
                }
            }
        }

        // Zwróć odpowiedź w formie JSON
        echo json_encode($responseData);
    }

    /**
     * Zmienia status zadania na podstawie danych wejściowych.
     *
     * @return void
     */
    public function changeTaskStatus(array $data): void
    {
        if (!$this->checkRole('operator')) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Nie masz uprawnień'
            ]);
            return;
        }

        if (!isset($data['taskData'], $data['columnId'])) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Błędne dane wejściowe.'
            ]);
            return;
        }

        $taskId = (int)$data['taskData'];
        $newProcess = (int)$data['columnId'];

        $statusMap = [
            0 => 'Nowy',
            1 => 'Rozpoczęty',
            2 => 'W trakcie',
            3 => 'Ukończony',
        ];

        $newStatus = $statusMap[$newProcess] ?? 'Nieznany';

        try {
            // Pobierz repozytorium zadań
            $taskRepository = $this->getRepository(Task::class);

            // Zaktualizuj status zadania w bazie danych
            $taskRepository->updateTaskProgress($taskId, $newStatus, $newProcess);

            echo json_encode([
                'status' => 'success',
                'message' => 'Status zadania został zaktualizowany pomyślnie.'
            ]);
        } catch (PDOException $e) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Nie udało się zaktualizować statusu zadania.',
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Przypisuje użytkownika do zadania.
     * Obsługuje dane JSON z żądania, sprawdza uprawnienia oraz waliduje dane.
     */
    public function assignUserToTask(): void
    {
        $responseData = [];

        // Sprawdzenie, czy użytkownik jest zalogowany
        if (!$this->auth->getUserId()) {
            http_response_code(401); // Unauthorized
            $responseData['error'] = 'Brak odpowiednich uprawnień do wykonania tej operacji.';
            echo json_encode($responseData);
            return;
        }

        // Pobranie danych z żądania
        $postData = json_decode(file_get_contents('php://input'), true);
        if (!$postData) {
            http_response_code(400); // Bad Request
            $responseData['error'] = 'Nieprawidłowy format danych.';
            echo json_encode($responseData);
            return;
        }

        // Sprawdzenie tokena CSRF
        if (empty($postData['csrf_token']) || !AuthHelpers::verifyCSRFToken($postData['csrf_token'])) {
            http_response_code(403); // Forbidden
            $responseData['error'] = 'Nieprawidłowy token CSRF.';
            echo json_encode($responseData);
            return;
        }

        // Walidacja danych wejściowych
        $taskId = $postData['taskId'] ?? null;
        $userId = $postData['userId'] ?? null;

        if (!$taskId || !$userId) {
            http_response_code(400); // Bad Request
            $responseData['error'] = 'Nie podano prawidłowego ID zadania lub użytkownika.';
            echo json_encode($responseData);
            return;
        }

        // Pobranie repozytoriów
        $taskRepository = $this->getRepository(Task::class);
        $userRepository = $this->getRepository(User::class);
        $taskUserRepository = $this->getRepository(TaskUser::class);

        // Pobranie zadania
        $task = $taskRepository->find($taskId);
        if (!$task) {
            http_response_code(404); // Not Found
            $responseData['error'] = 'Zadanie nie znalezione.';
            echo json_encode($responseData);
            return;
        }

        // Pobranie użytkownika
        $user = $userRepository->find($userId);
        if (!$user) {
            http_response_code(404); // Not Found
            $responseData['error'] = "Użytkownik o ID '{$userId}' nie znaleziony.";
            echo json_encode($responseData);
            return;
        }

        // Sprawdzenie, czy użytkownik jest już przypisany do zadania
        if ($taskUserRepository->isUserAssignedToTask($userId, $taskId)) {
            http_response_code(409); // Conflict
            $responseData['error'] = "Użytkownik '{$user->getUsername()}' jest już przypisany do zadania '{$task->getTaskName()}'.";
            echo json_encode($responseData);
            return;
        }

        // Sprawdzenie limitu użytkowników w zadaniu
        $assignedUsersCount = $taskUserRepository->getAssignedUsersCount($taskId);
        if ($assignedUsersCount >= 12) {
            http_response_code(409); // Conflict
            $responseData['error'] = 'Osiągnięto maksymalną liczbę przypisanych użytkowników do tego zadania.';
            echo json_encode($responseData);
            return;
        }

        // Przypisanie użytkownika do zadania
        $taskUserRepository->assignTaskToUser($task, $user);

        // Przygotowanie odpowiedzi
        http_response_code(200); // OK
        $responseData['success'] = "Użytkownik '{$user->getUsername()}' został przypisany do zadania '{$task->getTaskName()}'.";
        $responseData['user'] = [
            'user_id' => $user->getUserId(),
            'user_login' => $user->getLogin(),
            'user_avatar' => $user->getAvatar(),
        ];
        echo json_encode($responseData);
    }

    /**
     * Usuwa przypisanie użytkownika do zadania.
     * Sprawdza uprawnienia, dane wejściowe i token CSRF przed wykonaniem operacji.
     */
    public function unassignUserFromTask(): void
    {
        $responseData = [];

        // Sprawdzenie, czy użytkownik jest zalogowany
        if (!$this->auth->getUserId()) {
            http_response_code(401); // Unauthorized
            $responseData['error'] = 'Brak odpowiednich uprawnień do wykonania tej operacji.';
            echo json_encode($responseData);
            return;
        }

        // Pobranie danych z żądania
        $requestData = json_decode(file_get_contents('php://input'), true);
        if (!$requestData) {
            http_response_code(400); // Bad Request
            $responseData['error'] = 'Nieprawidłowy format danych.';
            echo json_encode($responseData);
            return;
        }

        // Sprawdzenie tokena CSRF
        if (empty($requestData['csrf_token']) || !AuthHelpers::verifyCSRFToken($requestData['csrf_token'])) {
            http_response_code(403); // Forbidden
            $responseData['error'] = 'Nieprawidłowy token CSRF.';
            echo json_encode($responseData);
            return;
        }

        // Walidacja danych wejściowych
        $taskId = $requestData['taskId'] ?? null;
        $userId = $requestData['userId'] ?? null;

        if (!$taskId || !$userId) {
            http_response_code(400); // Bad Request
            $responseData['error'] = 'Nie podano prawidłowego ID zadania lub użytkownika.';
            echo json_encode($responseData);
            return;
        }

        // Pobranie repozytoriów
        $taskRepository = $this->getRepository(Task::class);
        $userRepository = $this->getRepository(User::class);
        $taskUserRepository = $this->getRepository(TaskUser::class);

        // Pobranie zadania
        $task = $taskRepository->find($taskId);
        if (!$task) {
            http_response_code(404); // Not Found
            $responseData['error'] = 'Zadanie nie znalezione.';
            echo json_encode($responseData);
            return;
        }

        // Pobranie użytkownika
        $user = $userRepository->find($userId);
        if (!$user) {
            http_response_code(404); // Not Found
            $responseData['error'] = "Użytkownik o ID '{$userId}' nie znaleziony.";
            echo json_encode($responseData);
            return;
        }

        // Sprawdzenie, czy użytkownik jest przypisany do zadania
        if (!$taskUserRepository->isUserAssignedToTask($userId, $taskId)) {
            http_response_code(409); // Conflict
            $responseData['error'] = "Użytkownik '{$user->getUsername()}' nie jest przypisany do zadania '{$task->getTaskName()}'.";
            echo json_encode($responseData);
            return;
        }

        // Usunięcie przypisania
        $taskUserRepository->removeUserAssignment($taskId, $userId);

        // Przygotowanie odpowiedzi
        http_response_code(200); // OK
        $responseData['success'] = "Przypisanie użytkownika '{$user->getUsername()}' do zadania '{$task->getTaskName()}' zostało usunięte.";
        $responseData['user'] = [
            'user_id' => $user->getUserId(),
            'user_login' => $user->getLogin(),
            'user_avatar' => $user->getAvatar(),
        ];
        echo json_encode($responseData);
    }

    /**
     * Usuwa zadanie na podstawie jego ID.
     *
     * @param int $taskId Identyfikator zadania do usunięcia.
     * @return void
     */
    public function deleteTask(int $taskId): void
    {
        $responseData = [];

        // Pobranie ID zalogowanego użytkownika
        $userId = $this->auth->getUserId();
        if (!$userId) {
            http_response_code(401); // Unauthorized
            $responseData['error'] = 'Brak odpowiednich uprawnień do wykonania tej operacji.';
            echo json_encode($responseData);
            return;
        }

        // Sprawdzenie roli użytkownika
        if (!$this->checkRole('creator')) {
            http_response_code(403); // Forbidden
            $responseData['error'] = 'Brak uprawnień do usunięcia zadania.';
            echo json_encode($responseData);
            return;
        }

        // Pobranie danych żądania
        $requestData = json_decode(file_get_contents('php://input'), true);
        if (!$requestData) {
            http_response_code(400); // Bad Request
            $responseData['error'] = 'Nieprawidłowy format danych.';
            echo json_encode($responseData);
            return;
        }

        // Weryfikacja tokena CSRF
        if (empty($requestData['csrf_token']) || !AuthHelpers::verifyCSRFToken($requestData['csrf_token'])) {
            http_response_code(403); // Forbidden
            $responseData['error'] = 'Nieprawidłowy token CSRF.';
            echo json_encode($responseData);
            return;
        }

        // Pobranie repozytorium zadania
        $taskRepository = $this->getRepository(Task::class);

        // Pobranie szczegółów zadania
        $task = $taskRepository->getTaskById($taskId, $userId);
        if (!$task) {
            http_response_code(404); // Not Found
            $responseData['error'] = 'Zadanie o podanym identyfikatorze nie istnieje.';
            echo json_encode($responseData);
            return;
        }

        // Usunięcie zadania
        $result = $taskRepository->deleteTask($taskId, $userId);
        if (!$result) {
            http_response_code(500); // Internal Server Error
            $responseData['error'] = 'Wystąpił błąd podczas usuwania zadania.';
            echo json_encode($responseData);
            return;
        }

        // Przygotowanie odpowiedzi
        http_response_code(200); // OK
        $responseData['success'] = true;
        $responseData['message'] = 'Zadanie "' . $task->getTaskName() . '" zostało usunięte.';
        echo json_encode($responseData);
    }
}
