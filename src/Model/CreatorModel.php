<?php

namespace App\Model;

use PDO;
use App\View;
use PDOException;

class CreatorModel extends BaseModel
{
    /**
     * Pobiera wszystkich użytkowników mających ten sam token rejestracyjny co aktualnie zalogowany użytkownik.
     *
     * @return array|null Tablica z danymi użytkowników lub null, jeśli nie ma przypisanego tokenu.
     */
    public function getAllUsersByToken()
    {
        if (isset($_SESSION['user_id'])) {
            $loggedInUserId = $_SESSION['user_id'];

            // Pobierz token dla aktualnie zalogowanego użytkownika
            $queryToken = $this->db->prepare("SELECT registration_token FROM users WHERE user_id = :user_id");
            $queryToken->bindParam(':user_id', $loggedInUserId, PDO::PARAM_INT);
            $queryToken->execute();
            $token = $queryToken->fetchColumn();

            // Jeżeli użytkownik ma przypisany token, to pobierz innych użytkowników z tym tokenem
            if ($token) {
                // Modyfikacja zapytania SQL, dodano warunek WHERE user_id <> :loggedInUserId
                $queryUsers = $this->db->prepare("SELECT user_id,user_login, user_avatar, registration_date FROM users WHERE registration_token = :token AND user_id <> :loggedInUserId");
                $queryUsers->bindParam(':token', $token, PDO::PARAM_STR);
                $queryUsers->bindParam(':loggedInUserId', $loggedInUserId, PDO::PARAM_INT);
                $queryUsers->execute();

                return $queryUsers->fetchAll(PDO::FETCH_ASSOC);
            }
        }

        return null;
    }

    /**
     * Pobiera zadania użytkownika wraz z powiązanymi projektami.
     *
     * @param int $userId ID użytkownika
     * @param array $param Parametry dodatkowe
     * @return array Tablica z grupowanymi zadaniami i projektami
     */
    public function getTasksByUserIdWithProjects($userId)
    {
        try {
            // Przygotowanie zapytania SQL
            $query = $this->db->prepare("
            SELECT projects.project_id, projects.project_name, tasks.task_id, tasks.task_name, tasks.task_description
            FROM tasks
            INNER JOIN projects ON tasks.project_id = projects.project_id
            WHERE projects.user_id = :user_id
        ");

            $query->bindParam(":user_id", $userId, PDO::PARAM_INT);

            $query->execute();

            $result = $query->fetchAll(PDO::FETCH_ASSOC);

            // Inicjalizacja tablicy do grupowania zadań
            $groupedTasks = [];

            foreach ($result as $row) {
                $taskId = $row['task_id'];

                if (!isset($groupedTasks[$taskId])) {
                    $groupedTasks[$taskId] = [
                        'task_id' => $row['task_id'],
                        'task_name' => $row['task_name'],
                        'task_description' => $row['task_description'],
                        'projects' => [],
                    ];
                }

                if (!empty($row['project_id'])) {
                    $groupedTasks[$taskId]['projects'][] = [
                        'project_id' => $row['project_id'],
                        'project_name' => $row['project_name'],
                    ];
                }
            }

            return array_values($groupedTasks);
        } catch (PDOException $e) {
            echo "Błąd pobierania zadań i projektów: " . $e->getMessage();
            return [];
        }
    }

    /**
     * Pobiera liczbę przypisanych użytkowników do danego zadania.
     *
     * @param int $taskId ID zadania
     * @return int Liczba przypisanych użytkowników
     */
    public function getAssignedUsersCount($taskId)
    {
        try {
            $query = $this->db->prepare("
                SELECT COUNT(*) as user_count
                FROM tasks_users
                WHERE task_id = :task_id
            ");
            $query->bindParam(':task_id', $taskId, PDO::PARAM_INT);
            $query->execute();

            $result = $query->fetch(PDO::FETCH_ASSOC);
            return (int) $result['user_count'];
        } catch (PDOException $e) {
            echo "Error fetching assigned users count: " . $e->getMessage();
            return 0;
        }
    }

    /**
     * Pobiera zadania użytkownika według postępu.
     *
     * @param int $progress Postęp zadania
     * @return array Tablica zadaniami pasującymi do kryteriów wyszukiwania
     */
    public function getTasksByProgress($progress)
    {
        $query = $this->db->prepare("
            SELECT tasks.*, projects.project_name as project_name 
            FROM tasks 
            LEFT JOIN projects ON tasks.project_id = projects.project_id 
            WHERE tasks.task_progress = :progress 
            AND tasks.user_id = :user_id
        ");
        $query->bindParam(':progress', $progress, PDO::PARAM_INT);
        $query->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $query->execute();

        $result = $query->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }

    /**
     * Pobiera liczbę tokenów przypisanych do danego użytkownika.
     *
     * @param int $userId ID użytkownika
     * @return int Liczba tokenów
     */
    public function getTokenCountByUserId($userId)
    {
        try {
            $query = $this->db->prepare("
                SELECT COUNT(*) as token_count
                FROM tokens
                WHERE user_id = :user_id
            ");
            $query->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $query->execute();

            $result = $query->fetch(PDO::FETCH_ASSOC);
            return (int) $result['token_count'];
        } catch (PDOException $e) {
            echo "Błąd pobierania liczby tokenów: " . $e->getMessage();
            return 0;
        }
    }
}
