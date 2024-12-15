<?php

namespace App\Controller;

use App\Entity\Task;
use App\Entity\User;
use App\Entity\Project;
use App\Helpers\AuthHelpers;

class ProjectController extends BaseController
{
    /**
     * Wyświetla szczegóły projektu na podstawie ID.
     *
     * @param int $projectId ID projektu do wyświetlenia.
     */
    public function displayProject($projectId)
    {
        // Sprawdź rolę użytkownika
        if (!$this->checkRole('creator')) {
            header('Location: /login');
            exit();
        }
        $userId = $this->auth->getUserId();
        // Pobranie projektu na podstawie ID
        $projectRepository = $this->getRepository(Project::class);
        $project = $projectRepository->getProjectById($projectId, $userId);

        // Sprawdzenie, czy projekt został znaleziony
        if (!$project) {
            $this->view->render('404_page');
            return;
        }

        // Pobranie zadań jako tablica
        $tasks = $projectRepository->getTasksByProjectId($projectId);
        $totalTasks = count($tasks);

        // Definiowanie mapowania statusów i kolorów
        $statusMap = [
            0 => ['status' => 'Nowy', 'color' => 'bg-primary'],
            1 => ['status' => 'Rozpoczęty', 'color' => 'bg-danger'],
            2 => ['status' => 'W trakcie', 'color' => 'bg-warning'],
            3 => ['status' => 'Ukończony', 'color' => 'bg-success'],
        ];

        // Liczenie liczby ukończonych zadań
        $completedTasksCount = 0;
        $taskList = [];  // Inicjalizacja tablicy do przechowywania danych zadań

        foreach ($tasks as $task) {
            $taskProgress = $task->getTaskProgress();

            if (isset($statusMap[$taskProgress])) {
                $taskStatus = $statusMap[$taskProgress]['status'];
                $taskColor = $statusMap[$taskProgress]['color'];
            } else {
                $taskStatus = 'Nieznany';
                $taskColor = 'bg-primary';
            }

            // Dodanie statusu i koloru do tablicy
            $taskData = [
                'task_id' => $task->getTaskId(),
                'task_name' => $task->getTaskName(),
                'task_description' => $task->getTaskDescription(),
                'task_description_long' => $task->getTaskDescriptionLong(),
                'task_progress' => $taskProgress,
                'task_status' => $taskStatus,
                'task_color' => $taskColor,
            ];

            $taskList[] = $taskData;  // Dodanie do listy zadań

            // Zliczanie ukończonych zadań
            if ($taskProgress == 3) {
                $completedTasksCount++;
            }
        }

        // Obliczenie procentowego postępu projektu
        $projectProgress = ($totalTasks > 0) ? ($completedTasksCount / $totalTasks) * 100 : 0;

        // Przygotowanie danych do przekazania do widoku
        $data = [
            'pageTitle' => 'Szczegóły projektu',
            'project' => [
                'project_id' => $project->getProjectId(),
                'project_name' => $project->getProjectName(),
                'tasks' => $taskList,
            ],
            'projectProgress' => $projectProgress,
            'noTasks' => $totalTasks === 0,  // Informacja o braku zadań
        ];

        // Renderowanie widoku z danymi projektu
        $this->view->render('creator/creator_project', $data);
    }

    /**
     * Wyświetla wszystkie projekty użytkownika.
     *
     * @return void
     */
    public function displayAllProjects()
    {
        if ($this->checkRole('creator')) {
            $userId = $this->auth->getUserId();
            $projectRepository = $this->getRepository(Project::class);
            $projectsWithTasks = $projectRepository->getProjectWithTasksAndUsers($userId);

            // Generowanie tokena CSRF
            $csrf = $_SESSION['csrf_token'] = AuthHelpers::generateCSRFToken();

            // Przygotowanie danych do przekazania do widoku
            $data = [
                'pageTitle' => 'Wszystkie projekty',
                'userProjects' => $projectsWithTasks, // Przekazujemy projekty z zadaniami
                'csrfToken' => $csrf,
            ];

            // Renderowanie widoku
            $this->view->render('creator/creator_all_projects', $data);
        } else {
            $this->view->render('home_page');
        }
    }
}
