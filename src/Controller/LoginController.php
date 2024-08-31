<?php

namespace App\Controller;

use App\Entity\User;
use App\Helpers\AuthHelpers;

class LoginController extends BaseController
{
    public function index()
    {
        // Sprawdzanie, czy użytkownik jest już zalogowany
        if (isset($_SESSION['user_id'])) {
            $userId = (int)$_SESSION['user_id'];
            $userRepository = $this->getRepository(User::class);
            $userRole = $userRepository->find($userId)->getRole(); // Zakładam, że metoda getRole jest dostępna

            $redirectUrl = $_ENV("BASE_URL") . $userRole . '/dashboard';
            header('Location: ' . $redirectUrl);
            exit;
        } else {
            $this->render('login_form');
        }
    }

    public function login()
    {
        // Sprawdzanie, czy użytkownik jest już zalogowany
        if (!isset($_SESSION['user_id'])) {
            $username = AuthHelpers::sanitizeInput($_POST['username'] ?? '');
            $password = AuthHelpers::sanitizeInput($_POST['password'] ?? '');

            if (AuthHelpers::validatePassword($password)) {
                $userRepository = $this->getRepository(User::class);
                $user = $userRepository->findByUsername($username);

                if ($user && password_verify($password, $user->getPassword())) {
                    $userId = $user->getUserId();
                    $userRole = $user->getRole();
                    AuthHelpers::setSessionSecurityHeaders();

                    // Ustawienie sesji użytkownika
                    $_SESSION['user_id'] = $userId;

                    // Ustawienie kolumny user_logged na 1 (online)
                    $userRepository->setUserLoggedIn($userId);

                    $redirectUrl = getenv('BASE_URL') . $userRole . '/dashboard';
                    header('Location: ' . $redirectUrl);
                    exit;
                } else {
                    // Obsługa błędnego logowania
                    $this->render('login_form', ['error' => 'Niepoprawne dane']);
                }
            } else {
                // Obsługa błędnego hasła
                $this->render('login_form', ['error' => 'Hasło musi mieć co najmniej 8 znaków']);
            }
        } else {
            // Użytkownik już zalogowany, przekieruj na stronę główną
            $this->index();
        }
    }

    public function logout()
    {
        $userId = $_SESSION['user_id'] ?? null;

        if ($userId) {
            $userRepository = $this->getRepository(User::class);
            $userRepository->setUserLoggedOut($userId); // Ustawienie kolumny user_logged na 0 (offline)
        }

        session_unset();
        session_destroy();

        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        unset($_COOKIE['user_id']); // Usunięcie ciasteczka user_id

        header('Location: /login');
        exit();
    }
}
