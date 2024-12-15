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
}
