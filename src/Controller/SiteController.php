<?php

namespace App\Controller;

use App\Entity\User;

class SiteController extends BaseController
{
    public function index()
    {
        // Sprawdzanie, czy użytkownik jest zalogowany
        if (isset($_SESSION['user_id'])) {
            $userRepository = $this->getRepository(User::class);
            $user = $userRepository->findLoggedInUserById($_SESSION['user_id']);

            if ($user) {
                $role = $user->getRole();  // Zakładam, że masz metodę getRole w encji User
                header('Location: ' . $_ENV("BASE_URL") . $role . '/dashboard');
                exit();
            }
        }

        // Pobieranie ostatnio zarejestrowanych użytkowników
        $userRepository = $this->getRepository(User::class);
        $lastRegisteredUsers = $userRepository->findAllOrderedByRegistrationDate();

        $data = [
            'lastRegisteredUsers' => $lastRegisteredUsers,
        ];
        $this->render('home_page', $data);
    }

    public function logout()
    {
        $userId = $_SESSION['user_id'] ?? null;

        if ($userId) {
            $userRepository = $this->getRepository(User::class);
            $userRepository->setUserLoggedOut($userId);
        }

        session_unset();
        session_destroy();

        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        unset($_COOKIE['users_login']);

        header('Location: /login');
        exit();
    }
}
