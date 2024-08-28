<?php

namespace App\Controller;

use App\View;

class AdminController extends BaseController
{
    /**
     * Wyświetla panel administracyjny.
     *
     * @return void
     */
    public function displayDashboard(): void
    {
        if ($this->checkRole('admin')) {
            $users = $this->userModel->getAllUsers();
            $projects = $this->projectModel->getAllProjects();
            $tasks = $this->taskModel->getAllTasks();
            $projectsByMonth = $this->projectModel->getProjectsByMonth();

            $data = [
                'pageTitle' => 'Panel administracyjny',
                'users' => $users,
                'projects' => $projects,
                'tasks' => $tasks,
                'projectsByMonth' => $projectsByMonth,
            ];

            View::render('admin/admin_dashboard', $data);
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
    function reloadUsers(): void
    {
        // if ($this->checkRole('admin')) {
        $users = $this->userModel->getAllUsers();
        // $response = [
        //     'status' => 'success',
        //     'users' => $users,
        // ];
        echo json_encode($users);
        // } else {
        //     $response = [
        //         'status' => 'error',
        //         'message' => 'Nie masz uprawnień'
        //     ];
        //     echo json_encode($response);
        // }
    }

    /**
     * Wyświetla formularz zarządzania użytkownikami.
     *
     * @return void
     */
    public function manageUsers(): void
    {
        if ($this->checkRole('admin')) {
            $users = $this->userModel->getAllUsers();
            $data = [
                'pageTitle' => 'Użytkownicy',
                'users' => $users,
            ];

            View::render('admin/admin_users', $data);
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
    public function siteSettings(): void
    {
        if ($this->checkRole('admin')) {
            $users = $this->userModel->getAllUsers();
            $data = [
                'pageTitle' => 'Użytkownicy',
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
     * @return void
     */
    public function updateUser($data): void
    {
        if ($this->checkRole('admin')) {
            $userId = $data['user_id'] ?? null;
            $name = $data['user_name'] ?? null;
            $email = $data['user_email'] ?? null;
            $username = $data['user_login'] ?? null;

            if ($userId) {
                $success = $this->userModel->updateUser((int) $userId, $name, $email, $username);

                if ($success) {
                    $updatedUser = $this->userModel->getUserById((int) $userId);

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
     * @return void
     */
    public function deleteUser($userId): void
    {
        if ($this->checkRole('admin')) {
            $this->taskModel->removeUserAssignmentsByUserId($userId);
            $this->userModel->deleteUser($userId);

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
