<?php

namespace App\Controller;

use App\View;
use PDOException;
use App\Entity\Task;
use App\Entity\User;
use App\Entity\Project;
use App\Entity\TaskUser;

/**
 * Kontroler odpowiedzialny za operacje operatora.
 */
class OperatorController extends BaseController
{
    /**
     * Wyświetla dashboard operatora.
     *
     * @return void
     */
    public function displayDashboard(): void
    {
        if ($this->checkRole('operator')) {
            $taskUserRepository = $this->getRepository(TaskUser::class);
            $userId = $this->auth->getUserId();

            $data = [
                'pageTitle' => 'Dashboard',
                'projectsName' => $taskUserRepository->getProjectsAndTasksByUser($userId),
            ];

            $this->view->render('operator/operator_dashboard', $data);
        } else {
            header('Location: /login');
            exit();
        }
    }

    /**
     * Wyświetla szczegóły projektu.
     *
     * @param int $projectId ID projektu
     * @return void
     */
    public function operatorProject(int $projectId): void
    {
        if ($this->checkRole('operator')) {
            $projectRepository = $this->getRepository(Project::class);
            $projectDetails = $projectRepository->getProjectDetails($projectId, $this->auth->getUserId());

            $taskUserRepository = $this->getRepository(TaskUser::class);
            $userId = $this->auth->getUserId();

            $isUserAssigned = $taskUserRepository->isUserAssignedToProjectTasks($projectId, $userId);

            if (!$isUserAssigned) {
                $this->view->render('404_page');
                return;
            }

            $data = [
                'pageTitle' => 'Szczegóły projektu',
                'tasks' => $projectDetails
            ];

            $this->view->render('operator/operator_project', $data);
        } else {
            header('Location: /login');
            exit();
        }
    }

    /**
     * Wyświetla szczegóły pojedynczego zadania.
     *
     * @param int $taskId ID zadania
     * @return void
     */
    public function singleTask(int $taskId): void
    {
        if ($this->checkRole('operator')) {
            // Używamy TaskRepository do pobrania zadania
            $taskRepository = $this->getRepository(Task::class);
            $task = $taskRepository->findOneByTaskId($taskId);

            if (!$task) {
                $this->view->render('404_page');
                return;
            }

            $userId = $this->auth->getUserId();
            $taskUserRepository = $this->getRepository(TaskUser::class);
            $isUserAssigned = $taskUserRepository->isUserAssignedToTask($userId, $taskId);

            if (!$isUserAssigned) {
                $this->view->render('no_permission');
                return;
            }

            // Przygotowanie danych do przekazania do widoku
            $data = [
                'pageTitle' => 'Szczegóły zadania',
                'task' => [
                    'task_name' => $task->getTaskName(),
                    'task_description' => $task->getTaskDescription(),
                    'task_description_long' => $task->getTaskDescriptionLong(),
                    'task_created_at' => $task->getTaskCreatedAt() ? $task->getTaskCreatedAt()->format('Y-m-d H:i:s') : 'Nieznana data',
                    'project_name' => $task->getProject()->getProjectName(),
                ],
            ];

            // Renderowanie widoku z danymi
            $this->view->render('operator/operator_single_task', ['data' => $data]);
        } else {
            header('Location: /login');
            exit();
        }
    }

    /**
     * Wyświetla ustawienia operatora.
     *
     * @return void
     */
    public function operatorSettings(): void
    {
        if ($this->checkRole('operator')) {
            $userId = $this->auth->getUserId();
            $userRepository = $this->getRepository(User::class);
            $user = $userRepository->find($userId);

            if (!$user) {
                $this->view->render('404_page');
                return;
            }

            $data = [
                'pageTitle' => 'Ustawienia użytkownika',
                'user' => [
                    'username' => $user->getUsername(),
                    'email' => $user->getEmail(),
                ],
            ];

            $this->view->render('operator/operator_settings', $data);
        } else {
            header('Location: /login');
            exit();
        }
    }
}
