<?php

namespace App\Controller;

use App\View;
use App\Entity\Task;
use App\Entity\User;
use App\Entity\Project;
use App\Entity\TaskUser;
use App\Helpers\AuthHelpers;

/**
 * Kontroler obsługujący operacje na zadaniach.
 */
class TaskController extends BaseController
{
    /**
     * Wyświetla wszystkie zadania użytkownika.
     * Sprawdza czy użytkownik jest zalogowany przed wyświetleniem zadań.
     */
    public function displayAllTasks(): void
    {
        $userId = $this->auth->getUserId();
        if ($userId) {
            $projectRepository = $this->getRepository(Project::class);
            $projectsWithTasks = $projectRepository->getProjectWithTasksAndUsers($userId);

            $data = [
                'pageTitle' => 'Wszystkie zadania',
                'projects' => !empty($projectsWithTasks) ? $projectsWithTasks : []
            ];

            $this->render('creator/creator_all_tasks', $data);
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

            // Pobranie zadań
            $tasks = $taskRepository->getGroupedTasksByProgress($userId, $progressId);

            // Pogrupowanie zadań według projektów
            $groupedTasks = [];
            foreach ($tasks as $task) {
                $projectId = $task['project']['projectId'];
                $projectName = $task['project']['projectName'];
                if (!isset($groupedTasks[$projectName])) {
                    $groupedTasks[$projectName] = [
                        'project_id' => $projectId,
                        'tasks' => []
                    ];
                }
                $groupedTasks[$projectName]['tasks'][] = [
                    'task_id' => $task['taskId'],
                    'task_name' => $task['taskName'],
                    'task_description' => $task['taskDescription'],
                ];
            }

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
     * Wyświetla szczegóły pojedynczego zadania na podstawie jego ID.
     *
     * @param int $taskId Identyfikator zadania do wyświetlenia
     * @return void
     */
    public function singleTask(int $taskId): void
    {
        $userId = $this->auth->getUserId();
        // Sprawdź rolę użytkownika
        if (!$this->checkRole('creator')) {
            $this->view->render('403_page');
            return;
        }
        $taskRepository = $this->getRepository(Task::class);
        // Pobierz szczegóły zadania na podstawie $taskId z modelu TaskModel
        $task = $taskRepository->getTaskById($taskId, $userId);

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
}
