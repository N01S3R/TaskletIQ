<?php

namespace App\Controller;

use App\View;
use App\Entity\Task;
use App\Entity\User;
use App\Entity\Project;

class AdminController extends BaseController
{
    /**
     * Wyświetla panel administracyjny.
     *
     * @return void
     */
    public function displayDashboard(): void
    {
        // Sprawdzanie, czy użytkownik jest zalogowany
        if (isset($_SESSION['user_id'])) {
            $userRepository = $this->getRepository(User::class);
            $user = $userRepository->findLoggedInUserById($_SESSION['user_id']);

            if ($user && $user->getRole() === 'admin') {
                // Pobieranie danych do dashboardu
                $userCount = $userRepository->countAllUsers();
                $projectCount = $this->getRepository(Project::class)->countAllProjects();
                $taskCount = $this->getRepository(Task::class)->countAllTasks();
                // $projectsByMonth = $this->getRepository(Project::class)->findProjectsByMonth();

                $data = [
                    'pageTitle' => 'Panel administracyjny',
                    'users' => $userCount,
                    'projects' => $projectCount,
                    'tasks' => $taskCount,
                    // 'projectsByMonth' => $projectsByMonth,
                ];

                // Renderowanie widoku
                View::render('admin/admin_dashboard', $data);
            } else {
                header('Location: /login');
                exit();
            }
        } else {
            header('Location: /login');
            exit();
        }
    }

    /**
     * Wyświetla formularz zarządzania użytkownikami.
     *
     * @return void
     */
    public function reloadUsers(): void
    {
        if (isset($_SESSION['user_id'])) {
            $userRepository = $this->getRepository(User::class);
            $users = $userRepository->findAllOrderedByRegistrationDate();

            $response = [
                'status' => 'success',
                'users' => $users
            ];
            echo json_encode($response);
        } else {
            $response = [
                'status' => 'error',
                'message' => 'Nie masz uprawnień'
            ];
            echo json_encode($response);
        }
    }

    /**
     * Wyświetla formularz zarządzania użytkownikami.
     *
     * @return void
     */
    public function manageUsers(): void
    {
        if (isset($_SESSION['user_id'])) {
            $userRepository = $this->getRepository(User::class);
            $users = $userRepository->findAllOrderedByRegistrationDate();

            $data = [
                'pageTitle' => 'Zarządzanie użytkownikami',
                'users' => $users,
            ];

            View::render('admin/admin_users', $data);
        } else {
            header('Location: /login');
            exit();
        }
    }

    /**
     * Wyświetla ustawienia witryny.
     *
     * @return void
     */
    public function siteSettings(): void
    {
        if (isset($_SESSION['user_id'])) {
            $userRepository = $this->getRepository(User::class);
            $users = $userRepository->findAllOrderedByRegistrationDate();

            $data = [
                'pageTitle' => 'Ustawienia witryny',
                'users' => $users,
            ];

            View::render('admin/admin_settings', $data);
        } else {
            header('Location: /login');
            exit();
        }
    }

    /**
     * Aktualizuje dane użytkownika.
     *
     * @param array $data
     * @return void
     */
    public function updateUser(array $data): void
    {
        if (isset($_SESSION['user_id'])) {
            $userId = $data['user_id'] ?? null;
            $name = $data['user_name'] ?? null;
            $email = $data['user_email'] ?? null;
            $username = $data['user_login'] ?? null;

            if ($userId) {
                $userRepository = $this->getRepository(User::class);
                $success = $userRepository->updateUser((int) $userId, $name, $email, $username);

                if ($success) {
                    $updatedUser = $userRepository->find($userId);

                    $response = [
                        'status' => 'success',
                        'message' => 'Dane zostały zaktualizowane',
                        'data' => $updatedUser
                    ];
                } else {
                    $response = [
                        'status' => 'error',
                        'message' => 'Błąd podczas aktualizacji danych'
                    ];
                }
                echo json_encode($response);
            } else {
                $response = [
                    'status' => 'error',
                    'message' => 'Niepoprawne dane'
                ];
                echo json_encode($response);
            }
        } else {
            $response = [
                'status' => 'error',
                'message' => 'Nie masz uprawnień'
            ];
            echo json_encode($response);
        }
    }

    /**
     * Usuwa użytkownika oraz jego przypisania do zadań.
     *
     * @param int $userId
     * @return void
     */
    public function deleteUser(int $userId): void
    {
        if (isset($_SESSION['user_id'])) {
            $taskRepository = $this->getRepository(Task::class);
            $userRepository = $this->getRepository(User::class);

            $taskRepository->removeUserAssignmentsByUserId($userId);
            $userRepository->deleteUser($userId);

            $response = [
                'success' => true,
                'message' => 'Użytkownik został usunięty.'
            ];
            echo json_encode($response);
        } else {
            $response = [
                'status' => 'error',
                'message' => 'Nie masz uprawnień'
            ];
            echo json_encode($response);
        }
    }
}
