<?php

namespace App\Controller;

use App\Entity\User;

class SiteController extends BaseController
{
    public function displaySite()
    {
        // Sprawdzanie, czy użytkownik jest zalogowany
        if ($this->auth->getUserId()) {
            $userRepository = $this->getRepository(User::class);
            $user = $userRepository->findLoggedInUserById($this->auth->getUserId());

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
}
