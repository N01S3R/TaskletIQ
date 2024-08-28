<?php

namespace App\Controller;

use App\View;

/**
 * Kontroler obsługujący operacje na projektach.
 */
class ProjectController extends BaseController
{
    /**
     * Wyświetla szczegóły projektu na podstawie jego ID.
     *
     * @param int $projectId Identyfikator projektu
     * @return void
     */
    public function displayProject($projectId)
    {
        // Sprawdź rolę użytkownika
        if (!$this->checkRole('creator')) {
            // Przekierowanie na stronę logowania, jeśli użytkownik nie ma odpowiedniej roli
            header('Location: /login');
            exit();
        }

        // Pobranie danych projektu na podstawie ID
        $project = $this->projectModel->getProjectById($projectId);

        // Sprawdzenie, czy projekt został znaleziony
        if (!$project) {
            $this->view->render('404_page');
            return;
        }

        // Obliczenie całkowitej liczby zadań w projekcie
        $totalTasks = isset($project['tasks']) ? count($project['tasks']) : 0;

        // Definiowanie mapowania statusów i kolorów
        $statusMap = [
            0 => ['status' => 'Nowy', 'color' => 'bg-primary'],
            1 => ['status' => 'Rozpoczęty', 'color' => 'bg-danger'],
            2 => ['status' => 'W trakcie', 'color' => 'bg-warning'],
            3 => ['status' => 'Ukończony', 'color' => 'bg-success'],
        ];

        // Liczenie liczby ukończonych zadań (task_progress = 3) oraz ustawienie statusu zadania i koloru
        $completedTasksCount = 0;
        foreach ($project['tasks'] as &$task) { // Używamy referencji (&) aby móc zmodyfikować elementy w tablicy
            $taskProgress = $task['task_progress'];
            if (isset($statusMap[$taskProgress])) {
                $task['task_status'] = $statusMap[$taskProgress]['status'];
                $task['task_color'] = $statusMap[$taskProgress]['color'];
            } else {
                $task['task_status'] = 'Nieznany';
                $task['task_color'] = 'bg-primary';
            }
            if ($taskProgress == 3) {
                $completedTasksCount++;
            }
        }

        // Obliczenie procentowego postępu projektu
        $projectProgress = ($totalTasks > 0) ? ($completedTasksCount / $totalTasks) * 100 : 0;

        // Przygotowanie danych do przekazania do widoku
        $data = [
            'pageTitle' => 'Szczegóły projektu',
            'project' => $project,
            'projectProgress' => $projectProgress,
        ];

        // Renderowanie widoku
        View::render('creator/creator_project', $data);
    }



    /**
     * Tworzy nowy projekt na podstawie danych przesłanych metodą POST.
     *
     * @return void
     */
    public function createProject()
    {
        // Inicjalizacja danych odpowiedzi
        $responseData = [];

        // Odczytanie danych wejściowych jako JSON
        $postData = json_decode(file_get_contents('php://input'), true);

        // Sprawdzenie, czy dane zostały przesłane i czy są poprawne
        if ($postData !== null && isset($postData['projectName']) && !empty($postData['projectName'])) {
            // Pobranie nazwy projektu z danych wejściowych
            $projectName = $postData['projectName'];

            // Utworzenie nowego projektu za pomocą modelu
            $defaultData['project_name'] = $projectName;
            $projectId = $this->projectModel->addProject($defaultData);

            // Sprawdzenie, czy dodanie projektu się powiodło
            if ($projectId) {
                // Przygotowanie odpowiedzi sukcesu
                $responseData['success'] = 'Dodano projekt "' . $projectName . '"';
                $responseData['newProject'] = ['project_id' => $projectId, 'project_name' => $projectName];
            } else {
                // Przygotowanie odpowiedzi błędu
                $responseData['error'] = 'Projekt o tej nazwie już istnieje';
            }
        } else {
            // Przygotowanie odpowiedzi błędu, jeśli dane są niepoprawne lub brak danych
            $responseData['error'] = 'Brak danych projektu';
        }

        // Zwrócenie odpowiedzi w formacie JSON
        echo json_encode($responseData);

        // Zakończenie działania skryptu, aby uniknąć dalszego renderowania strony
        exit;
    }

    /**
     * Wyświetla wszystkie projekty użytkownika.
     *
     * @return void
     */
    public function displayAllProjects()
    {
        // Sprawdzenie, czy użytkownik jest zalogowany
        if ($this->checkLogged()) {
            // Pobranie ID zalogowanego użytkownika
            $loggedInUserId = $this->userModel->getLoggedInUserId();

            // Pobranie wszystkich projektów użytkownika wraz z zadaniami
            $projects = $this->projectModel->getProjectsByUserIdWithTasks($loggedInUserId);

            // Przygotowanie danych do przekazania do widoku
            $data = [
                'pageTitle' => 'Wszystkie projekty',
                'userProjects' => $projects
            ];

            // Renderowanie widoku z listą projektów
            $this->view->render('creator/creator_all_projects', $data);
        } else {
            // Renderowanie strony głównej, jeśli użytkownik nie jest zalogowany
            $this->view->render('home_page');
        }
    }

    /**
     * Aktualizuje nazwę projektu na podstawie danych przesłanych metodą PUT.
     *
     * @param int $projectId Identyfikator projektu
     * @return void
     */
    public function updateProject($projectId)
    {
        // Inicjalizacja danych odpowiedzi
        $responseData = [];

        // Odczytanie danych wejściowych jako JSON
        $putData = json_decode(file_get_contents('php://input'), true);

        // Sprawdzenie, czy dane zostały przesłane i czy są poprawne
        if ($putData !== null && isset($putData['project_name']) && !empty($putData['project_name'])) {
            // Pobranie nowej nazwy projektu z danych wejściowych
            $projectName = $putData['project_name'];

            // Aktualizacja nazwy projektu w bazie danych
            $updated = $this->projectModel->setProjectName($projectId, $projectName);

            // Pobranie zaktualizowanych danych projektu
            $updatedProject = $this->projectModel->getProjectById($projectId);

            // Sprawdzenie, czy aktualizacja się powiodła
            if ($updated && $updatedProject) {
                // Przygotowanie odpowiedzi sukcesu
                $responseData['success'] = 'Zaktualizowano nazwę projektu "' . $projectName . '"';
                $responseData['updatedProject'] = $updatedProject;
            } else {
                // Przygotowanie odpowiedzi błędu
                $responseData['error'] = 'Nie udało się zaktualizować nazwy projektu';
            }
        } else {
            // Przygotowanie odpowiedzi błędu, jeśli dane są niepoprawne lub brak danych
            $responseData['error'] = 'Nieprawidłowe dane projektu';
        }

        // Zwrócenie odpowiedzi w formacie JSON
        echo json_encode($responseData);

        // Zakończenie działania skryptu
        exit;
    }

    /**
     * Obsługuje autouzupełnianie zapytań dotyczących projektów.
     *
     * @return void
     */
    public function autocompleteProjects()
    {
        // Odczytanie zapytania GET
        $query = reset($_GET['params']);

        // Pobranie projektów pasujących do zapytania
        $projects = $this->projectModel->getProjectsByName($query);

        // Sprawdzenie, czy znaleziono jakiekolwiek projekty
        $exists = !empty($projects);

        // Zwrócenie odpowiedzi jako JSON
        header('Content-Type: application/json');
        echo json_encode(['exists' => $exists]);
    }

    /**
     * Usuwa projekt na podstawie jego ID.
     *
     * @param int $id Identyfikator projektu do usunięcia
     * @return void
     */
    public function deleteProject($id)
    {
        // Pobranie informacji o projekcie przed usunięciem
        $project = $this->projectModel->getProjectById($id);

        // Usunięcie projektu z bazy danych
        $deleted = $this->projectModel->deleteProject($id);

        // Przygotowanie komunikatu o wyniku operacji
        $message = $deleted ? 'Projekt "' . $project['project_name'] . '" został pomyślnie usunięty.'
            : 'Nie udało się usunąć projektu "' . $project['project_name'] . '".';

        // Zwrócenie odpowiedzi jako JSON
        echo json_encode([
            $deleted ? 'success' : 'error' => $message,
        ]);

        // Zakończenie działania skryptu
        exit;
    }
}
