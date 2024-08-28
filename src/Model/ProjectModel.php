<?php

namespace App\Model;

use PDO;
use PDOException;
use App\Model\BaseModel;

class ProjectModel extends BaseModel
{

    public function getAllProjects()
    {
        try {
            $query = $this->db->query("SELECT * FROM projects");
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Błąd pobierania projektów: " . $e->getMessage();
            return [];
        }
    }

    public function setProjectName($projectId, $projectName)
    {
        try {
            // Aktualizacja nazwy projektu w bazie danych
            $query = $this->db->prepare("UPDATE projects SET project_name = :project_name WHERE project_id = :project_id");
            $query->bindParam(":project_id", $projectId, PDO::PARAM_INT);
            $query->bindParam(":project_name", $projectName, PDO::PARAM_STR);
            $query->execute();

            return true;
        } catch (PDOException $e) {
            // Obsługa błędu
            echo "Błąd aktualizacji nazwy projektu: " . $e->getMessage();
            return false;
        }
    }

    public function getProjectById($projectId)
    {
        try {
            $query = $this->db->prepare("
            SELECT projects.project_id, projects.project_name, tasks.task_id, tasks.task_name, tasks.task_description, tasks.task_description_long, tasks.task_progress, tasks.task_status
            FROM projects
            LEFT JOIN tasks ON projects.project_id = tasks.project_id
            WHERE projects.project_id = :project_id
        ");
            $query->bindParam(":project_id", $projectId, PDO::PARAM_INT);
            $query->execute();

            $result = $query->fetchAll(PDO::FETCH_ASSOC);

            // Inicjalizacja danych projektu
            $project = [
                'project_id' => null,
                'project_name' => null,
                'tasks' => [],
            ];

            // Przetwarzanie wyników zapytania
            foreach ($result as $row) {
                // Ustawienie danych projektu, jeśli nie zostały jeszcze ustawione
                if ($project['project_id'] === null) {
                    $project['project_id'] = $row['project_id'];
                    $project['project_name'] = $row['project_name'];
                }

                // Dodanie zadania do projektu, jeśli istnieje
                if ($row['task_id'] !== null) {
                    $project['tasks'][] = [
                        'task_id' => (int)$row['task_id'],
                        'task_name' => $row['task_name'],
                        'task_description' => $row['task_description'],
                        'task_description_long' => $row['task_description_long'],
                        'task_progress' => (int)$row['task_progress'],
                        'task_status' => $row['task_status'],
                    ];
                }
            }

            // Sprawdzenie, czy projekt istnieje
            if ($project['project_id'] === null) {
                return null;
            }

            return $project;
        } catch (PDOException $e) {
            echo "Błąd pobierania projektu: " . $e->getMessage();
            return null;
        }
    }

    public function getProjectsByUserIdWithTasks($userId)
    {
        try {
            $query = $this->db->prepare("
            SELECT projects.project_id, projects.project_name, tasks.task_id, tasks.task_name, tasks.task_description, tasks.task_progress,
           users.user_id, users.user_login, users.user_avatar
            FROM projects
            LEFT JOIN tasks ON projects.project_id = tasks.project_id
            LEFT JOIN tasks_users ON tasks.task_id = tasks_users.task_id
            LEFT JOIN users ON tasks_users.user_id = users.user_id
            WHERE projects.user_id = :user_id
        ");
            $query->bindParam(":user_id", $userId, PDO::PARAM_INT);
            $query->execute();

            $result = $query->fetchAll(PDO::FETCH_ASSOC);

            // Grupuj projekty według identyfikatora projektu
            $groupedProjects = [];
            foreach ($result as $row) {
                $projectId = $row['project_id'];
                if (!isset($groupedProjects[$projectId])) {
                    $groupedProjects[$projectId] = [
                        'project_id' => $row['project_id'],
                        'project_name' => $row['project_name'],
                        // Dodaj inne pola projektu, jeśli istnieją
                        'tasks' => [],
                    ];
                }

                // Sprawdź, czy istnieje zadanie przed dodaniem
                if (!empty($row['task_id'])) {
                    // Sprawdź, czy zadanie już istnieje w projekcie
                    $taskExists = false;
                    foreach ($groupedProjects[$projectId]['tasks'] as &$task) {
                        if ($task['task_id'] === $row['task_id']) {
                            $taskExists = true;
                            break;
                        }
                    }

                    if (!$taskExists) {
                        $groupedProjects[$projectId]['tasks'][] = [
                            'task_id' => $row['task_id'],
                            'task_name' => $row['task_name'],
                            'task_description' => $row['task_description'],
                            'task_progress' => $row['task_progress'],
                            // Dodaj inne pola zadania, jeśli istnieją
                            'users' => [],
                        ];
                    }

                    // Dodaj użytkownika do zadania
                    foreach ($groupedProjects[$projectId]['tasks'] as &$task) {
                        if ($task['task_id'] === $row['task_id']) {
                            if (!empty($row['user_id'])) {
                                $task['users'][] = [
                                    'user_id' => $row['user_id'],
                                    'user_login' => $row['user_login'],
                                    'user_avatar' => $row['user_avatar']
                                ];
                            }
                            break;
                        }
                    }
                }
            }

            // Zamień indeksy na numery
            return array_values($groupedProjects);
        } catch (PDOException $e) {
            echo "Błąd pobierania projektów i zadań: " . $e->getMessage();
            return [];
        }
    }


    public function getProjectWithTasksAndUsers($userId)
    {
        try {
            $query = $this->db->prepare("
            SELECT projects.project_id AS project_id, projects.project_name AS project_name, 
            tasks.task_id AS task_id, tasks.task_name AS task_name, tasks.task_description AS task_description, 
            tasks.created_at AS task_created_at, users.*,
            tasks_users.id AS tasks_users_id
            FROM projects
            LEFT JOIN tasks ON projects.project_id = tasks.project_id
            LEFT JOIN tasks_users ON tasks.task_id = tasks_users.task_id
            LEFT JOIN users ON tasks_users.user_id = users.user_id
            WHERE projects.user_id = :user_id
        ");
            $query->bindParam(":user_id", $userId, PDO::PARAM_INT);
            $query->execute();

            $result = $query->fetchAll(PDO::FETCH_ASSOC);

            // Grupuj projekty według identyfikatora projektu
            $groupedProjects = [];
            $taskModel = new TaskModel();
            foreach ($result as $row) {
                $projectId = $row['project_id'];
                if (!isset($groupedProjects[$projectId])) {
                    $groupedProjects[$projectId] = [
                        'project_id' => $row['project_id'],
                        'project_name' => $row['project_name'],
                        'tasks' => [],
                    ];
                }

                $taskId = (int)$row['task_id'];
                $users = $taskModel->getAssignedUsersForTasks($taskId);
                $task = [
                    'task_id' => $taskId,
                    'task_name' => $row['task_name'],
                    'task_description' => $row['task_description'],
                    'created_at' => $row['task_created_at'],
                    'users' => $users,
                ];

                // Sprawdź, czy klucz 'created_at' istnieje w danym wierszu
                if (isset($row['task_created_at'])) {
                    $task['created_at'] = $row['task_created_at'];
                }

                $groupedProjects[$projectId]['tasks'][$taskId] = $task;
            }
            // Zamień indeksy na numery
            return array_values($groupedProjects);
        } catch (PDOException $e) {
            echo "Błąd pobierania projektów, zadań i użytkowników: " . $e->getMessage();
            return [];
        }
    }

    public function getProjectsByUserId($userId)
    {
        try {
            $query = $this->db->prepare("SELECT * FROM projects WHERE user_id = :user_id");
            $query->bindParam(":user_id", $userId, PDO::PARAM_INT);
            $query->execute();

            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Błąd pobierania projektów: " . $e->getMessage(), 0);
            return [];
        }
    }

    public function getProjectsByName($projectName)
    {
        try {
            $query = $this->db->prepare("SELECT project_name FROM projects WHERE project_name AND user_id = :user_id");
            $query->bindParam(":project_name", $projectName, PDO::PARAM_STR);
            $query->bindParam(":user_id", $_SESSION['user_id'], PDO::PARAM_STR);
            $query->execute();

            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Błąd pobierania projektów: " . $e->getMessage(), 0);
            return [];
        }
    }

    public function addProject($data)
    {
        if ($this->projectExists($data['project_name'])) {
            return false;
        }

        try {
            $query = $this->db->prepare("INSERT INTO projects (project_name, user_id) VALUES (:project_name, :userId)");
            $query->bindParam(":project_name", $data['project_name'], PDO::PARAM_STR);
            $query->bindParam(":userId", $_SESSION['user_id'], PDO::PARAM_INT);
            $query->execute();

            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            echo "Błąd dodawania projektu: " . $e->getMessage();
            return false;
        }
    }

    private function projectExists($projectName)
    {
        $query = $this->db->prepare("SELECT COUNT(*) FROM projects WHERE project_name = :project_name");
        $query->bindParam(":project_name", $projectName, PDO::PARAM_STR);
        $query->execute();
        $count = $query->fetchColumn();

        return $count > 0;
    }

    public function updateProject($projectId, $data)
    {
        try {
            // Załóżmy, że $data zawiera dane do aktualizacji, np. array('name' => 'Nowa nazwa projektu')
            $query = $this->db->prepare("UPDATE projects SET project_name = :project_name WHERE project_id = :project_id");
            $query->bindParam(":project_id", $projectId, PDO::PARAM_INT);
            $query->bindParam(":project_name", $data['project_name'], PDO::PARAM_STR);
            $query->execute();

            return true;
        } catch (PDOException $e) {
            echo "Błąd aktualizacji projektu: " . $e->getMessage();
            return false;
        }
    }

    public function getProjectsCreatedPerMonth()
    {
        try {
            $query = $this->db->query("
                SELECT DATE_FORMAT(created_at, '%Y-%m') AS month, COUNT(*) AS project_count
                FROM projects
                GROUP BY month
                ORDER BY month DESC
            ");
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Błąd pobierania danych projektów: " . $e->getMessage();
            return [];
        }
    }

    public function deleteProject($projectId)
    {
        try {
            // Usuwanie przypisań użytkowników do zadań powiązanych z projektem
            $query = $this->db->prepare("DELETE FROM tasks_users WHERE task_id IN (SELECT task_id FROM tasks WHERE project_id = :project_id)");
            $query->bindParam(":project_id", $projectId, PDO::PARAM_INT);
            $query->execute();

            // Usuwanie zadań powiązanych z projektem
            $query = $this->db->prepare("DELETE FROM tasks WHERE project_id = :project_id");
            $query->bindParam(":project_id", $projectId, PDO::PARAM_INT);
            $query->execute();

            // Usuwanie samego projektu
            $query = $this->db->prepare("DELETE FROM projects WHERE project_id = :project_id");
            $query->bindParam(":project_id", $projectId, PDO::PARAM_INT);
            $query->execute();

            return true;
        } catch (PDOException $e) {
            echo "Błąd usuwania projektu: " . $e->getMessage();
            return false;
        }
    }

    public function getProjectsByMonth()
    {
        try {
            $query = $this->db->prepare("
            SELECT DATE_FORMAT(created_at, '%Y-%m') AS month, COUNT(*) AS project_count
            FROM projects
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            ORDER BY DATE_FORMAT(created_at, '%Y-%m') DESC
        ");
            $query->execute();

            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Błąd pobierania projektów: " . $e->getMessage();
            return [];
        }
    }
}
