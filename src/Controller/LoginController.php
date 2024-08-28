<?php

namespace App\Controller;

use App\View;
use App\Helpers\AuthHelpers;

class LoginController extends BaseController
{
    public function index()
    {
        if ($this->checkLogged()) {
            $userId = $_SESSION['user_id'];
            $userRole = $this->userModel->getUsersRole((int)$userId);
            $redirectUrl = getenv('BASE_URL') . $userRole . '/dashboard';

            header('Location: ' . $redirectUrl);
            exit;
        } else {
            View::render('login_form');
        }
    }

    public function login()
    {
        if (!$this->checkLogged()) {
            $username = AuthHelpers::sanitizeInput($_POST['username']);
            $password = AuthHelpers::sanitizeInput($_POST['password']);

            if (AuthHelpers::validatePassword($password)) {
                $userId = $this->userModel->login($username, $password);

                if (!empty($userId)) {
                    $userRole = $this->userModel->getUsersRole((int)$userId['user_id']);
                    AuthHelpers::setSessionSecurityHeaders();

                    $redirectUrl = getenv('BASE_URL') . $userRole . '/dashboard';
                    header('Location: ' . $redirectUrl);

                    // Ustawienie ciasteczka z identyfikatorem użytkownika
                    setcookie('user_id', $userId, time() + (86400 * 30), "/");
                    exit;
                } else {
                    // Obsługa błędnego logowania
                    View::render('login_form', ['error' => 'Niepoprawne dane']);
                }
            } else {
                // Obsługa błędnego hasła
                View::render('login_form', ['error' => 'Hasło musi mieć co najmniej 8 znaków']);
            }
        }
    }
}
