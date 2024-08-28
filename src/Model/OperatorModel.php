<?php

namespace App\Model;

use PDO;
use PDOException;

/**
 * Model Operator obsługujący operacje związane z projektami i zadaniami.
 */
class OperatorModel extends BaseModel
{
    /**
     * Pobiera projekty przypisane do danego użytkownika wraz z liczbą zadań w każdym projekcie.
     *
     * @param int $userId Identyfikator użytkownika
     * @return array Tablica z danymi projektów i liczbą zadań
     * @throws PDOException Błąd PDO podczas pobierania danych
     */
    public function getProjectsByUserId(int $userId): array
    {
        try {
            $query = $this->db->prepare("
            SELECT projects.project_id, 
                   projects.project_name, 
                   COUNT(tasks.task_id) as task_count,
                   SUM(CASE WHEN tasks.task_progress = 3 THEN 1 ELSE 0 END) AS completed_task_count,
    SUM(CASE WHEN tasks.task_progress = 2 THEN 1 ELSE 0 END) AS inprogress_task_count,
    SUM(CASE WHEN tasks.task_progress = 1 THEN 1 ELSE 0 END) AS remaining_task_count
            FROM projects
            LEFT JOIN tasks ON projects.project_id = tasks.project_id
            INNER JOIN tasks_users ON tasks.task_id = tasks_users.task_id
            WHERE tasks_users.user_id = :user_id
            GROUP BY projects.project_id, projects.project_name
        ");
            $query->bindParam(":user_id", $userId, PDO::PARAM_INT);
            $query->execute();

            $result = $query->fetchAll(PDO::FETCH_ASSOC);

            return $result;
        } catch (PDOException $e) {
            echo "Błąd pobierania nazw projektów: " . $e->getMessage();
            return [];
        }
    }

    /**
     * Pobiera zadania przypisane do danego projektu.
     *
     * @param string $projectName Nazwa projektu
     * @return array Tablica zadaniami przypisanymi do danego projektu
     * @throws PDOException Błąd PDO podczas pobierania danych
     */
    public function getTasksByProjectName(string $projectName): array
    {
        try {
            // Pobranie ID zalogowanego użytkownika z sesji
            $userId = $_SESSION['user_id'];

            $query = $this->db->prepare("
                SELECT tasks.*, projects.project_name
                FROM tasks
                INNER JOIN projects ON tasks.project_id = projects.project_id
                INNER JOIN tasks_users ON tasks.task_id = tasks_users.task_id
                WHERE projects.project_name = :project_name
                AND tasks_users.user_id = :user_id
            ");
            $query->bindParam(':project_name', $projectName, PDO::PARAM_STR);
            $query->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $query->execute();

            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Błąd pobierania zadań: " . $e->getMessage();
            return [];
        }
    }

    /**
     * Pobiera zadania użytkownika na podstawie postępu.
     *
     * @param int $progress Postęp zadania
     * @return array Tablica zadaniami użytkownika na podstawie postępu
     * @throws PDOException Błąd PDO podczas pobierania danych
     */
    public function getProgress(int $progress): array
    {
        try {
            $query = $this->db->prepare("
                SELECT tasks.*, projects.project_name as project_name 
                FROM tasks 
                LEFT JOIN projects ON tasks.project_id = projects.project_id 
                INNER JOIN tasks_users ON tasks.task_id = tasks_users.task_id
                WHERE tasks.task_progress = :progress 
                AND tasks_users.user_id = :user_id
            ");
            $query->bindParam(':progress', $progress, PDO::PARAM_INT);
            $query->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
            $query->execute();

            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Błąd pobierania zadań: " . $e->getMessage();
            return [];
        }
    }

    /**
     * Pobiera szczegóły zadania na podstawie jego ID.
     *
     * @param int $taskId Identyfikator zadania
     * @return array|false Tablica z danymi zadania lub false w przypadku błędu
     * @throws PDOException Błąd PDO podczas pobierania danych
     */
    public function getTaskById(int $taskId)
    {
        try {
            $query = $this->db->prepare("
                SELECT tasks.*, projects.project_name
                FROM tasks
                INNER JOIN projects ON tasks.project_id = projects.project_id
                INNER JOIN tasks_users ON tasks.task_id = tasks_users.task_id
                WHERE tasks.task_id = :task_id
            ");
            $query->bindParam(':task_id', $taskId, PDO::PARAM_INT);
            $query->execute();

            return $query->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Obsługa błędu
            echo "Błąd pobierania zadania: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Pobiera szczegóły projektu na podstawie jego ID wraz z zadaniami przypisanymi do danego użytkownika.
     *
     * @param int $projectId Identyfikator projektu
     * @param int $userId Identyfikator użytkownika
     * @return array Tablica z danymi projektu i zadaniami
     * @throws PDOException Błąd PDO podczas pobierania danych
     */
    public function getProjectDetails(int $projectId, int $userId): array
    {
        try {
            $query = $this->db->prepare("
            SELECT projects.project_name, tasks.*
            FROM projects
            INNER JOIN tasks ON projects.project_id = tasks.project_id
            INNER JOIN tasks_users ON tasks.task_id = tasks_users.task_id
            WHERE projects.project_id = :project_id
            AND tasks_users.user_id = :user_id
            GROUP BY tasks.task_id
        ");
            $query->bindParam(":project_id", $projectId, PDO::PARAM_INT);
            $query->bindParam(":user_id", $userId, PDO::PARAM_INT);
            $query->execute();

            $result = $query->fetchAll(PDO::FETCH_ASSOC);

            return $result;
        } catch (PDOException $e) {
            echo "Błąd pobierania szczegółów projektu: " . $e->getMessage();
            return [];
        }
    }

    /**
     * Pobiera zadania użytkownika na podstawie postępu.
     *
     * @param int $progress Postęp zadania
     * @return array Tablica zadaniami użytkownika na podstawie postępu
     * @throws PDOException Błąd PDO podczas pobierania danych
     */
    public function getTaskProgress(int $progress): array
    {
        try {
            $query = $this->db->prepare("
                SELECT projects.project_name as project_name, 
                       tasks.task_name as task_name, 
                       tasks.task_description as task_description
                FROM tasks 
                LEFT JOIN projects ON tasks.project_id = projects.project_id 
                LEFT JOIN tasks_users ON tasks.task_id = tasks_users.task_id
                WHERE tasks.task_progress = :progress
                GROUP BY projects.project_name, tasks.task_name, tasks.task_description
            ");
            $query->bindParam(':progress', $progress, PDO::PARAM_INT);
            $query->execute();

            $result = $query->fetchAll(PDO::FETCH_ASSOC);

            return $result;
        } catch (PDOException $e) {
            echo "Błąd pobierania zadań: " . $e->getMessage();
            return [];
        }
    }

    /**
     * Aktualizuje status zadania na nowy status.
     *
     * @param int $taskId Identyfikator zadania
     * @param int $newStatus Nowy status zadania
     * @return bool True jeśli aktualizacja powiodła się, w przeciwnym razie false
     * @throws PDOException Błąd PDO podczas aktualizacji danych
     */
    public function updateTaskStatus(int $taskId, int $newStatus): bool
    {
        try {
            $query = $this->db->prepare("
                UPDATE tasks 
                SET task_progress = :new_status 
                WHERE task_id = :task_id
            ");
            $query->bindParam(':new_status', $newStatus, PDO::PARAM_INT);
            $query->bindParam(':task_id', $taskId, PDO::PARAM_INT);
            $query->execute();

            return true;
        } catch (PDOException $e) {
            echo "Błąd aktualizacji statusu zadania: " . $e->getMessage();
            return false;
        }
    }
}
