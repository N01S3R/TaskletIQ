<?php

namespace App\Controller;

class SiteController extends BaseController
{
    public function index()
    {
        if ($this->checkLogged()) {
            $role = $this->userModel->getUsersRole($_SESSION['user_id']);
            header('Location: ' . getenv('BASE_URL')  . $role . '/dashboard');
        }
        $lastRegistred = $this->userModel->getUsersSortedByRegistrationDate();
        $data = [
            'lastRegistred' => $lastRegistred,
        ];
        $this->view->render('home_page', $data);
    }
    public function logout()
    {
        $userId = $_SESSION['user_id'] ?? null;

        if ($userId) {
            $this->userModel->setUserOffline($userId);
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
