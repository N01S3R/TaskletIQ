<?php

namespace App\Controller;

use App\Entity\Task;
use App\Entity\User;
use App\Entity\Project;

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

            // Przygotowanie danych do przekazania do widoku
            $data = [
                'pageTitle' => 'Wszystkie projekty',
                'userProjects' => $projectsWithTasks, // Przekazujemy projekty z zadaniami
            ];

            // Renderowanie widoku
            $this->view->render('creator/creator_all_projects', $data);
        } else {
            $this->view->render('home_page');
        }
    }

    /**
     * Tworzy nowy projekt na podstawie danych przesłanych metodą POST.
     *
     * @return void
     */
    public function createProject()
    {
        $responseData = [];
        $postData = json_decode(file_get_contents('php://input'), true);

        if ($postData !== null && isset($postData['projectName']) && !empty($postData['projectName'])) {
            $projectName = $postData['projectName'];

            // Utworzenie nowego projektu
            $project = new Project();
            $project->setProjectName($projectName);
            $project->setUser($this->getRepository(User::class)->find($this->auth->getUserId()));

            $this->entityManager->persist($project);
            $this->entityManager->flush();
            $this->entityManager->clear();

            $responseData['success'] = 'Dodano projekt "' . $projectName . '"';
            $responseData['newProject'] = ['project_id' => $project->getProjectId(), 'project_name' => $projectName];
        } else {
            $responseData['error'] = 'Brak danych projektu';
        }

        echo json_encode($responseData);
        exit;
    }

    public function updateProject($projectId)
    {
        $responseData = [];
        $putData = json_decode(file_get_contents('php://input'), true);

        if ($putData !== null && isset($putData['project_name']) && !empty($putData['project_name'])) {
            $projectName = $putData['project_name'];

            // Pobranie projektu na podstawie ID
            $project = $this->entityManager->getRepository(Project::class)->find($projectId);

            if ($project) {
                if ($project->getUser()->getUserId() === $this->auth->getUserId()) {
                    // Ustawienie nowej nazwy projektu
                    $project->setProjectName($projectName);
                    $this->entityManager->flush();

                    // Pobranie zadań przypisanych do projektu
                    $tasks = $this->entityManager->getRepository(Task::class)->findBy(['project' => $project]);

                    // Przygotowanie zaktualizowanych danych projektu
                    $updatedProject = [
                        'project_id' => $project->getProjectId(),
                        'project_name' => $project->getProjectName(),
                        'created_at' => $project->getCreatedAt()->format('Y-m-d H:i:s'), // lub inny format daty
                        'tasks' => [],  // Dodajemy tablicę na zadania
                    ];

                    // Dodanie zadań do zaktualizowanego projektu
                    foreach ($tasks as $task) {
                        $updatedProject['tasks'][] = [
                            'task_id' => $task->getTaskId(),
                            'task_name' => $task->getTaskName(),
                            'task_description' => $task->getTaskDescription(),
                            'task_progress' => $task->getTaskProgress(),
                        ];
                    }

                    $responseData['success'] = 'Zaktualizowano nazwę projektu "' . $projectName . '"';
                    $responseData['updatedProject'] = $updatedProject;
                } else {
                    $responseData['error'] = 'Nie masz uprawnień do edytowania tego projektu.';
                }
            } else {
                $responseData['error'] = 'Nie znaleziono projektu.';
            }
        } else {
            $responseData['error'] = 'Nieprawidłowe dane projektu.';
        }

        echo json_encode($responseData);
        exit;
    }

    /**
     * Usuwa projekt na podstawie jego ID.
     *
     * @param int $id Identyfikator projektu do usunięcia
     * @return void
     */
    public function deleteProject($id)
    {
        $userId = $this->auth->getUserId();

        // Pobranie projektu na podstawie ID
        $project = $this->entityManager->getRepository(Project::class)->findOneBy([
            'projectId' => $id,
            'user' => $this->entityManager->getRepository(User::class)->find($userId)
        ]);

        if ($project) {
            $this->entityManager->remove($project);
            $this->entityManager->flush();
            $this->entityManager->clear();
            $message = 'Projekt "' . $project->getProjectName() . '" został pomyślnie usunięty.';
        } else {
            $message = 'Nie udało się usunąć projektu lub nie masz do niego uprawnień.';
        }

        echo json_encode(['success' => $message]);
        exit;
    }
}
