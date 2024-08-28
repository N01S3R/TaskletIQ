<?php

namespace App\Model;

use PDO;
use Exception;
use PDOException;

class TaskModel extends BaseModel
{
    public function getAllTasks()
    {
        $query = $this->db->query("SELECT * FROM tasks");
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addTask($project_id, $title, $description, $descriptionLong, $userId)
    {
        $query = $this->db->prepare("INSERT INTO tasks (project_id, task_name, task_description, task_description_long, user_id) VALUES (:project_id, :task_name, :task_description, :task_description_long, :user_id)");
        $query->bindParam(':project_id', $project_id, PDO::PARAM_STR);
        $query->bindParam(':task_name', $title, PDO::PARAM_STR);
        $query->bindParam(':task_description', $description, PDO::PARAM_STR);
        $query->bindParam(':task_description_long', $descriptionLong, PDO::PARAM_STR);
        $query->bindParam(':user_id', $userId, PDO::PARAM_INT);

        $query->execute();

        return $this->db->lastInsertId();
    }

    public function setTask($taskId, $title, $description, $descriptionLong)
    {
        try {
            $query = $this->db->prepare("UPDATE tasks SET task_name = :task_name, task_description = :task_description, task_description_long = :task_description_long WHERE task_id = :task_id");
            $query->bindParam(':task_name', $title, PDO::PARAM_STR);
            $query->bindParam(':task_description', $description, PDO::PARAM_STR);
            $query->bindParam(':task_description_long', $descriptionLong, PDO::PARAM_STR);
            $query->bindParam(':task_id', $taskId, PDO::PARAM_INT);
            $query->execute();

            return true;
        } catch (PDOException $e) {
            // Obsługa błędu
            echo "Błąd aktualizacji zadania: " . $e->getMessage();
            return false;
        }
    }

    public function deleteTask($taskId)
    {
        try {
            $query = $this->db->prepare("DELETE FROM tasks_users WHERE task_id = :task_id");
            $query->bindParam(":task_id", $taskId, PDO::PARAM_INT);
            $query->execute();

            $query = $this->db->prepare("DELETE FROM tasks WHERE task_id = :task_id");
            $query->bindParam(":task_id", $taskId, PDO::PARAM_INT);
            $query->execute();

            return true;
        } catch (PDOException $e) {
            echo "Błąd usuwania zadania: " . $e->getMessage();
            return false;
        }
    }

    public function changeTaskStatus($taskId, $status)
    {
        // Zakładamy, że status jest przekazywany jako string (np. 'completed', 'in_progress', 'pending')
        $validStatuses = ['completed', 'in_progress', 'pending'];

        if (!in_array($status, $validStatuses)) {
            // Nieprawidłowy status
            return false;
        }

        $query = $this->db->prepare("UPDATE tasks SET task_status = :status WHERE id = :id");
        $query->bindParam(':status', $status, PDO::PARAM_STR);
        $query->bindParam(':id', $taskId, PDO::PARAM_INT);

        return $query->execute();
    }

    public function getTasksById($taskId)
    {
        $query = $this->db->prepare("
        SELECT tasks.*, projects.project_name
        FROM tasks
        INNER JOIN projects ON tasks.project_id = projects.project_id
        WHERE tasks.task_id = :task_id AND tasks.user_id = :user_id
    ");
        $query->bindParam(':task_id', $taskId, PDO::PARAM_INT);
        $query->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $query->execute();

        $task = $query->fetch(PDO::FETCH_ASSOC);

        return $task ? $task : null;
    }

    public function getTaskByStatus($status)
    {
        $query = $this->db->prepare("
            SELECT tasks.*, projects.project_name
            FROM tasks
            INNER JOIN projects ON tasks.project_id = projects.project_id
            WHERE tasks.task_status = :status AND tasks.user_id = :user_id
        ");
        $query->bindParam(':status', $status, PDO::PARAM_INT);
        $query->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $query->execute();

        return $query->fetch(PDO::FETCH_ASSOC);
    }

    public function getTasksByProgress($progress)
    {
        $query = $this->db->prepare("
            SELECT tasks.*, projects.project_name
            FROM tasks
            INNER JOIN projects ON tasks.project_id = projects.project_id
            WHERE tasks.task_progress = :progress AND tasks.user_id = :user_id
        ");
        $query->bindParam(':progress', $progress, PDO::PARAM_INT);
        $query->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $query->execute();

        return $query->fetch(PDO::FETCH_ASSOC);
    }

    public function getTaskById($taskId, $userId)
    {
        try {
            // Przygotuj zapytanie SQL
            $query = $this->db->prepare("
            SELECT * FROM tasks WHERE task_id = :task_id AND user_id = :user_id
        ");
            // Zwiąż parametry
            $query->bindParam(':task_id', $taskId, PDO::PARAM_INT);
            $query->bindParam(':user_id', $userId, PDO::PARAM_INT);
            // Wykonaj zapytanie
            $query->execute();

            // Pobierz wynik
            return $query->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Logowanie błędu zamiast wyświetlania
            error_log("Błąd pobierania zadania: " . $e->getMessage());
            return false;
        }
    }

    public function assignTaskToUser($taskId, $userId)
    {
        try {
            $query = $this->db->prepare("INSERT INTO tasks_users (task_id, user_id) VALUES (:task_id, :user_id)");
            $query->bindParam(':task_id', $taskId, PDO::PARAM_INT);
            $query->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $query->execute();
        } catch (PDOException $e) {
            // Obsługa błędów
            echo "Error: " . $e->getMessage();
        }
    }

    public function getAssignedUsersForTasks($taskId)
    {
        $query = "SELECT u.user_id, u.username, u.name, u.user_avatar
              FROM users u
              INNER JOIN tasks_users tu ON u.user_id = tu.user_id
              WHERE tu.task_id = :taskId";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':taskId', $taskId, PDO::PARAM_INT);
            $stmt->execute();

            $assignedUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $assignedUsers;
        } catch (PDOException $e) {
            return [];
        }
    }

    public function isUserAssignedToTask($taskId, $userId)
    {
        $query = "SELECT COUNT(*) as count FROM tasks_users WHERE task_id = :taskId AND user_id = :userId";
        $statement = $this->db->prepare($query);
        $statement->bindParam(':taskId', $taskId);
        $statement->bindParam(':userId', $userId);
        $statement->execute();

        $result = $statement->fetch(PDO::FETCH_ASSOC);

        return ($result['count'] > 0);
    }

    public function removeUserAssignmentToTask($taskId, $userId)
    {
        $query = "DELETE FROM tasks_users WHERE task_id = :taskId AND user_id = :userId";
        $statement = $this->db->prepare($query);
        $statement->bindParam(':taskId', $taskId);
        $statement->bindParam(':userId', $userId);
        $statement->execute();

        return $statement->rowCount();
    }

    public function taskExists($title, $userId)
    {
        $query = $this->db->prepare("SELECT COUNT(*) FROM tasks WHERE task_name = :task_name AND user_id = :user_id");
        $query->bindParam(':task_name', $title, PDO::PARAM_STR);
        $query->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $query->execute();

        $count = $query->fetchColumn();

        return $count > 0;
    }

    /**
     * Usuwa przypisania użytkownika do wszystkich zadań.
     *
     * @param int $userId ID użytkownika.
     * @return bool Zwraca true, jeśli operacja zakończona powodzeniem, w przeciwnym razie false.
     */
    public function removeUserAssignmentsByUserId(int $userId): bool
    {
        try {
            $query = $this->db->prepare("DELETE FROM tasks_users WHERE user_id = :user_id");
            $query->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $query->execute();

            return true;
        } catch (PDOException $e) {
            error_log("Błąd usuwania przypisań użytkownika do zadań: " . $e->getMessage());
            return false;
        }
    }
}
