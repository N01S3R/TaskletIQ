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
    public function project(int $projectId): void
    {
        if ($this->checkRole('operator')) {
            $projectRepository = $this->getRepository(Project::class);
            $projectDetails = $projectRepository->getProjectDetails($projectId, $this->auth->getUserId());

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
     * Wyświetla status postępu zadań.
     *
     * @param int $id ID statusu
     * @return void
     */
    public function process(int $id): void
    {
        if ($this->checkRole('operator')) {
            if ($id) {
                $colors = [1 => 'danger', 2 => 'warning', 3 => 'success'];
                $data = [
                    'pageTitle' => 'Rozpoczęte Zadania',
                    'tasks' => $this->operatorModel->getTaskProgress($id),
                    'color' => $colors[$id]
                ];
                View::render('operator/operator_tasks_status', $data);
            }
        } else {
            header('Location: /login');
            exit();
        }
    }

    /**
     * Zmienia status zadania na podstawie danych wejściowych.
     *
     * @return void
     */
    public function changeTaskStatus(): void
    {
        if ($this->checkRole('operator')) {
            // Sprawdź, czy żądanie jest metodą POST
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Pobierz dane wejściowe z żądania
                $data = json_decode(file_get_contents('php://input'), true);

                // Walidacja danych wejściowych
                if (isset($data['taskData']) && isset($data['columnId'])) {
                    $taskId = (int)$data['taskData'];
                    $newStatus = (int)$data['columnId'];

                    try {
                        // Zaktualizuj status zadania w bazie danych
                        $this->operatorModel->updateTaskStatus($taskId, $newStatus);

                        // Przygotuj odpowiedź
                        $response = [
                            'status' => 'success',
                            'message' => 'Status zadania został zaktualizowany pomyślnie.'
                        ];
                        echo json_encode($response);
                        return;
                    } catch (PDOException $e) {
                        // Przygotuj odpowiedź w przypadku błędu
                        $response = [
                            'status' => 'error',
                            'message' => 'Nie udało się zaktualizować statusu zadania.',
                            'error' => $e->getMessage()
                        ];
                        echo json_encode($response);
                        return;
                    }
                } else {
                    // Przygotuj odpowiedź w przypadku błędnych danych wejściowych
                    $response = [
                        'status' => 'error',
                        'message' => 'Błędne dane wejściowe.'
                    ];
                    echo json_encode($response);
                    return;
                }
            } else {
                // Przygotuj odpowiedź w przypadku niepoprawnej metody żądania
                $response = [
                    'status' => 'error',
                    'message' => 'Niepoprawna metoda żądania.'
                ];
                echo json_encode($response);
                return;
            }
        } else {
            // Przygotuj odpowiedź w przypadku niepoprawnej metody żądania
            $response = [
                'status' => 'error',
                'message' => 'Nie masz uprawnień'
            ];
            echo json_encode($response);
            return;
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
            $task = $taskRepository->findOneByTaskId($taskId); // Użycie właściwej metody

            if (!$task) {
                $this->view->render('404_page');
                return;
            }

            // Przygotowanie danych do przekazania do widoku
            $data = [
                'pageTitle' => 'Zadanie',
                'task' => [
                    'task_name' => $task->getTaskName(),
                    'task_description' => $task->getTaskDescription(),
                    'task_description_long' => $task->getTaskDescriptionLong(),
                    'task_created_at' => $task->getTaskCreatedAt() ? $task->getTaskCreatedAt()->format('Y-m-d H:i:s') : 'Nieznana data',
                    'project_name' => $task->getProject()->getProjectName(), // Zakładając, że jest metoda getProject()
                ],
            ];

            // Renderowanie widoku z danymi
            $this->view->render('operator/operator_single_task', ['data' => $data]);
        } else {
            header('Location: /login');
            exit();
        }
    }
}
