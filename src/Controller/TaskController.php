<?php

namespace App\Controller;

use App\View;

/**
 * Kontroler obsługujący operacje na zadaniach.
 */
class TaskController extends BaseController
{
    /**
     * Wyświetla stronę główną z listą zadań.
     *
     * @return void
     */
    public function index(): void
    {
        // Implementacja zostanie dodana
    }

    /**
     * Tworzy nowe zadanie na podstawie danych przesłanych metodą POST.
     *
     * @return void
     */
    public function createTask(): void
    {
        // Sprawdź rolę użytkownika
        if (!$this->checkRole('creator')) {
            // Przekierowanie na stronę logowania, jeśli użytkownik nie ma odpowiedniej roli
            header('Location: /login');
            exit();
        }

        // Utwórz tablicę, która będzie przechowywać dane odpowiedzi
        $responseData = [];

        // Pobierz dane z ciała żądania
        $requestData = json_decode(file_get_contents('php://input'), true);

        // Pobierz dane z ciała żądania
        $project_id = $requestData['project_id'] ?? null;
        $title = $requestData['task_name'] ?? null;
        $description = $requestData['task_description'] ?? null;
        $descriptionLong = $requestData['task_description_long'] ?? null;

        // Pobierz identyfikator zalogowanego użytkownika
        $userId = $this->getUserId();

        // Sprawdź, czy wszystkie pola zostały wypełnione
        if (empty($project_id) || empty($title) || empty($description) || empty($descriptionLong)) {
            // Ustaw odpowiedź na błąd
            $responseData['error'] = 'Wszystkie pola muszą być wypełnione.';
        } else {
            // Sprawdź, czy $userId nie jest null i jest integerem
            if ($userId !== null && is_int($userId)) {
                // Sprawdź, czy takie zadanie już istnieje w bazie
                if ($this->taskModel->taskExists($title, $userId)) {
                    // Ustaw odpowiedź na błąd
                    $responseData['error'] = 'Zadanie o podanej nazwie i tytule już istnieje.';
                } else {
                    // Dodaj nowe zadanie
                    $taskId = $this->taskModel->addTask((int)$project_id, $title, $description, $descriptionLong, $userId);

                    // Sprawdź, czy dodanie zadania powiodło się
                    if ($taskId !== false) {
                        // Pobierz szczegóły dodanego zadania
                        $newTask = $this->taskModel->getTaskById($taskId, $userId);

                        // Ustaw status i kolor nowego zadania na podstawie task_progress
                        $taskProgress = $newTask['task_progress'];
                        switch ($taskProgress) {
                            case 0:
                                $newTask['task_status'] = 'Nowy';
                                $newTask['task_color'] = 'bg-primary';
                                break;
                            case 1:
                                $newTask['task_status'] = 'Rozpoczęty';
                                $newTask['task_color'] = 'bg-danger';
                                break;
                            case 2:
                                $newTask['task_status'] = 'W trakcie';
                                $newTask['task_color'] = 'bg-warning';
                                break;
                            case 3:
                                $newTask['task_status'] = 'Ukończony';
                                $newTask['task_color'] = 'bg-success';
                                break;
                            default:
                                $newTask['task_status'] = 'Nieznany';
                                $newTask['task_color'] = 'bg-primary';
                                break;
                        }

                        // Ustaw odpowiedź na sukces i przekaż dane nowego zadania
                        $responseData['success'] = 'Dodano zadanie "' . $title . '"';
                        $responseData['task'] = $newTask;
                    } else {
                        // Ustaw odpowiedź na błąd
                        $responseData['error'] = 'Nie udało się dodać zadania.';
                    }
                }
            } else {
                // Ustaw odpowiedź na błąd
                $responseData['error'] = 'Nieprawidłowy identyfikator użytkownika.';
            }
        }

        // Zwróć odpowiedź w formie JSON
        echo json_encode($responseData);
    }


    /**
     * Aktualizuje zadanie na podstawie danych przesłanych metodą PUT.
     *
     * @param int $taskId Identyfikator zadania do aktualizacji
     * @return void
     */
    public function updateTask(int $taskId): void
    {
        $userId = $this->getUserId();
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
            // Sprawdź, czy zadanie o podanym identyfikatorze istnieje
            $existingTask = $this->taskModel->getTaskById($taskId, $userId);
            if (!$existingTask) {
                // Zadanie o podanym identyfikatorze nie istnieje
                $responseData['error'] = 'Zadanie o podanym identyfikatorze nie istnieje.';
            } else {
                // Aktualizuj zadanie
                $result = $this->taskModel->setTask($taskId, $title, $description, $descriptionLong);
                if ($result) {
                    // Aktualizacja zadania powiodła się
                    // Pobierz zaktualizowane dane zadania
                    $updatedTask = $this->taskModel->getTaskById($taskId, $userId);
                    $responseData['success'] = 'Zaktualizowano zadanie "' . $title . '"';
                    $responseData['task'] = $updatedTask;
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
     * Wyświetla szczegóły pojedynczego zadania na podstawie jego ID.
     *
     * @param int $taskId Identyfikator zadania do wyświetlenia
     * @return void
     */
    public function singleTask(int $taskId): void
    {
        $userId = $this->getUserId();
        // Sprawdź rolę użytkownika
        if (!$this->checkRole('creator')) {
            $this->view->render('403_page');
            return;
        }

        // Pobierz szczegóły zadania na podstawie $taskId z modelu TaskModel
        $task = $this->taskModel->getTaskById($taskId, $userId);

        if (!$task) {
            // Zadanie o podanym identyfikatorze nie istnieje, wyświetl 404
            $this->view->render('404_page');
            return;
        }

        // Przygotuj dane do przekazania do widoku
        $data = [
            'task' => $task
        ];

        // Renderuj widok szczegółów zadania
        $this->view->render('operator/operator_single_task', $data);
    }

    /**
     * Zmienia status zadania na podstawie danych przesłanych z formularza POST.
     *
     * @return void
     */
    public function changeTaskStatus(): void
    {
        // Sprawdź rolę użytkownika
        if (!$this->checkRole('creator')) {
            echo json_encode(['error' => 'Brak uprawnień do wykonania tej operacji.']);
            return;
        }

        // Logika zmiany statusu zadania przez pracownika...

        $taskId = $_POST['task_id'] ?? null;
        $status = $_POST['task_status'] ?? null;

        // Zmiana statusu zadania w modelu TaskModel
        $this->taskModel->changeTaskStatus($taskId, $status);

        // Przekierowanie po zmianie statusu zadania
        $this->view->render('user/index');
    }

    /**
     * Usuwa zadanie na podstawie jego ID.
     *
     * @param int $taskId Identyfikator zadania do usunięcia
     * @return void
     */
    public function deleteTask(int $taskId): void
    {
        $userId = $this->getUserId();
        // Sprawdź rolę użytkownika
        if (!$this->checkRole('creator')) {
            echo json_encode(['success' => false, 'error' => 'Brak uprawnień do usunięcia zadania.']);
            return;
        }

        // Pobierz szczegóły zadania przed jego usunięciem
        $task = $this->taskModel->getTaskById($taskId, $userId);

        if (!$task) {
            echo json_encode(['success' => false, 'error' => 'Zadanie o podanym identyfikatorze nie istnieje.']);
            return;
        }

        // Usuń zadanie z modelu TaskModel
        $result = $this->taskModel->deleteTask($taskId);

        // Jeśli zadanie zostało usunięte poprawnie, zwróć odpowiednią odpowiedź (np. JSON)
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Zadanie "' . $task['task_name'] . '" zostało usunięte']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Wystąpił błąd podczas usuwania zadania.']);
        }
    }
}
