<?php

namespace App\Controller;

use App\View;
use App\Entity\Task;
use App\Entity\User;
use App\Entity\Project;
use App\Entity\TaskUser;

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
        if ($this->checkRole('admin')) {
            $userRepository = $this->getRepository(User::class);
            $user = $userRepository->findLoggedInUserById($_SESSION['user_id']);

            if ($user && $user->getRole() === 'admin') {
                // Pobieranie danych do dashboardu
                $userCount = count($userRepository->getAllUsers());
                $projectCount = count($this->getRepository(Project::class)->getAllProjects());
                $taskCount = count($this->getRepository(Task::class)->getAllTasks());
                $projectsByMonth = $this->getRepository(Project::class)->countProjectsPerMonthThisYear();

                $data = [
                    'pageTitle' => 'Panel administracyjny',
                    'users' => $userCount,
                    'projects' => $projectCount,
                    'tasks' => $taskCount,
                    'projectsByMonth' => $projectsByMonth,
                ];

                // Renderowanie widoku
                View::render('admin/admin_dashboard', $data);
            } else {
                header('Location: /login');
                exit();
            }
        } else {
            header('Location: /logout');
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
        header('Content-Type: application/json');
        if ($this->checkRole('admin')) {
            $userRepository = $this->getRepository(User::class);
            $users = $userRepository->getAllUsers();

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
        if ($this->checkRole('admin')) {

            $data = [
                'pageTitle' => 'Zarządzanie użytkownikami',
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
        if ($this->checkRole('admin')) {
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
     * @param int $userId
     * @param array $data
     * @return void
     */
    public function updateUser(int $userId, array $data): void
    {
        if ($this->checkRole('admin')) {
            $username = $data['username'];
            $email = $data['email'];
            $login = $data['login'];
            $avatar = $data['avatar'];
            $role = $data['role'];

            $userRepository = $this->getRepository(User::class);
            $success = $userRepository->updateUser($userId, $username, $email, $login, $avatar, $role);

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
        if ($this->checkRole('admin')) {
            $taskUserRepository = $this->getRepository(TaskUser::class);
            $userRepository = $this->getRepository(User::class);

            $taskUserRepository->removeUserAssignmentsByUserId($userId);

            $user = $userRepository->find($userId);
            if ($user) {
                $userRepository->delete($user);
                $response = [
                    'success' => true,
                    'message' => 'Użytkownik został usunięty.'
                ];
            } else {
                $response = [
                    'status' => false,
                    'message' => 'Użytkownik nie istnieje.'
                ];
            }
            echo json_encode($response);
        } else {
            $response = [
                'status' => false,
                'message' => 'Nie masz uprawnień'
            ];
            echo json_encode($response);
        }
    }

    /**
     * Dodaje nowego użytkownika.
     *
     * @param array $data
     * @return void
     */
    public function addUser(array $data): void
    {
        if ($this->checkRole('admin')) {
            // Zbieranie danych z formularza
            $name = $data['user_name'];
            $email = $data['user_email'];
            $login = $data['user_login'];
            $password = $this->generateRandomPassword();
            $avatar = $data['user_avatar'];
            $role = $data['user_role'];
            $registrationCode = false;
            if ($name && $email && $login) {

                $success = $this->auth->register($name, $email, $login, $password, $registrationCode, $avatar, $role);

                if ($success) {
                    $response = [
                        'status' => 'success',
                        'message' => 'Użytkownik został dodany pomyślnie.',
                    ];
                } else {
                    $response = [
                        'status' => 'error',
                        'message' => 'Użytkownik o podanym emailu lub loginie już istnieje.'
                    ];
                }
            } else {
                $response = [
                    'status' => 'error',
                    'message' => 'Niepoprawne dane.'
                ];
            }
            echo json_encode($response);
        } else {
            $response = [
                'status' => 'error',
                'message' => 'Nie masz uprawnień.'
            ];
            echo json_encode($response);
        }
    }


    /**
     * Generuje losowe hasło.
     *
     * @param int $length
     * @return string
     */
    private function generateRandomPassword(int $length = 10): string
    {
        return bin2hex(random_bytes($length / 2));
    }
}
