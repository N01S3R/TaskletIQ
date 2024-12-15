<?php

namespace App\Controller\ApiController;

use App\Entity\Task;
use App\Entity\User;
use App\Entity\Project;
use App\Helpers\AuthHelpers;
use App\Controller\BaseController;

class ApiProjectController extends BaseController
{
    /**
     * Tworzy nowy projekt na podstawie danych przesłanych metodą POST.
     *
     * @return void
     */
    public function createProject()
    {
        $responseData = [];
        $postData = json_decode(file_get_contents('php://input'), true);

        // Sprawdzenie CSRF tokena
        if (!isset($postData['csrf_token']) || !AuthHelpers::verifyCSRFToken($postData['csrf_token'])) {
            http_response_code(403);
            $responseData['error'] = 'Nieprawidłowy token CSRF';
            echo json_encode($responseData);
            exit;
        }

        if ($postData !== null && isset($postData['project_name']) && !empty($postData['project_name'])) {
            $projectName = $postData['project_name'];

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

        // Sprawdzenie CSRF tokena
        if (!isset($putData['csrf_token']) || !AuthHelpers::verifyCSRFToken($putData['csrf_token'])) {
            http_response_code(403);
            $responseData['error'] = 'Nieprawidłowy token CSRF';
            echo json_encode($responseData);
            exit;
        }

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
                        // 'created_at' => $project->getCreatedAt()->format('Y-m-d H:i:s'),
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
        $deleteData = json_decode(file_get_contents('php://input'), true);
        if (!isset($deleteData['csrf_token']) || !AuthHelpers::verifyCSRFToken($deleteData['csrf_token'])) {
            http_response_code(403);
            $responseData['error'] = 'Nieprawidłowy token CSRF';
            echo json_encode($responseData);
            exit;
        }

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

        echo json_encode(['success' => $id]);
        exit;
    }
}
