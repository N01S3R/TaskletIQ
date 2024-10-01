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
                $role = $user->getRole();
                $redirectUrl = '/' . $role . '/dashboard';
                header('Location: ' . $redirectUrl);
                exit();
            }
        }

        // Pobieranie ostatnio zarejestrowanych użytkowników
        $userRepository = $this->getRepository(User::class);
        $lastRegisteredUsers = $userRepository->findAllOrderedByRegistrationDate();

        // Przygotowanie danych do wysłania
        $processedUsers = array_map(function ($user) {
            return [
                'name' => $user->getUsername(),
                'registrationDate' => $user->getRegistrationDate()->format('Y-m-d H:i:s')
            ];
        }, $lastRegisteredUsers);

        $data = [
            'lastRegisteredUsers' => $processedUsers,
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
