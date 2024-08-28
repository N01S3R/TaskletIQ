<?php

namespace App\Controller;

use App\View;

class RegisterController extends BaseController
{
    public function index()
    {
        if ($this->checkLogged()) {
            $userId = $_SESSION['user_id'];
            $role = $this->userModel->getUsersRole((int)$userId);

            $redirectUrl = ($role === 'creator') ? 'creator/dashboard' : 'operator/dashboard';
            header('Location: ' . getenv('BASE_URL') . $redirectUrl);
            exit;
        } else {
            View::render('register_form');
        }
    }

    public function signup()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');
            $requestData = json_decode(file_get_contents('php://input'), true);

            if (!isset($requestData['fullName'], $requestData['email'], $requestData['username'], $requestData['password'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Brak wymaganych danych']);
                return;
            }

            $name = filter_var($requestData['fullName'], FILTER_SANITIZE_STRING);
            $email = filter_var($requestData['email'], FILTER_SANITIZE_EMAIL);
            $username = filter_var($requestData['username'], FILTER_SANITIZE_STRING);
            $password = $requestData['password'];
            $registrationCode = filter_var($requestData['registration_code'], FILTER_SANITIZE_STRING);
            $role = 'creator';

            if ($this->userModel->isRegistrationCodeUnique($registrationCode)) {
                $role = 'operator';
            } else {
                $registrationCode = $this->generateRegistrationCode($username);
            }

            if (strlen($username) > 11) {
                http_response_code(400);
                echo json_encode(['error' => 'Nazwa użytkownika może mieć maksymalnie 11 znaków']);
                return;
            }

            if (!$this->userModel->isEmailUnique($email)) {
                http_response_code(400);
                echo json_encode(['error' => 'Adres email już istnieje']);
                return;
            }

            if (!$this->userModel->isUsernameUnique($username)) {
                http_response_code(400);
                echo json_encode(['error' => 'Nazwa użytkownika już istnieje']);
                return;
            }

            $this->userModel->register($name, $email, $username, $password, $registrationCode, $role);
            echo json_encode(['message' => 'Konto zostało utworzone']);
            return;
        }
    }

    public function validateField()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');
            $requestData = json_decode(file_get_contents('php://input'), true);

            if (!isset($requestData['field'], $requestData['value'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Brak wymaganych danych']);
                return;
            }

            $field = $requestData['field'];
            $value = $requestData['value'];
            $response = ['valid' => true, 'message' => ''];

            switch ($field) {
                case 'fullName':
                    if (!preg_match('/^[a-zA-Z]{3,15}$/', $value)) {
                        $response = [
                            'valid' => false,
                            'message' => 'Imię może zawierać tylko litery od 3 do 15 znaków'
                        ];
                    }
                    break;
                case 'username':
                    if (preg_match('/^.{12,}$/', $value)) {
                        $response = [
                            'valid' => false,
                            'message' => 'Nazwa użytkownika może mieć maksymalnie 11 znaków'
                        ];
                    } elseif (!preg_match('/^[a-zA-Z0-9]+$/', $value)) {
                        $response = [
                            'valid' => false,
                            'message' => 'Nazwa użytkownika może zawierać tylko litery i cyfry'
                        ];
                    } elseif (!$this->userModel->isUsernameUnique($value)) {
                        $response = [
                            'valid' => false,
                            'message' => 'Nazwa użytkownika już istnieje'
                        ];
                    }
                    break;
                case 'email':
                    if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $response = [
                            'valid' => false,
                            'message' => 'Nieprawidłowy adres email'
                        ];
                    } elseif (!$this->userModel->isEmailUnique($value)) {
                        $response = [
                            'valid' => false,
                            'message' => 'Adres email już istnieje'
                        ];
                    }
                    break;
                case 'password':
                    if (!preg_match('/^.{10,}$/', $value)) {
                        $response = [
                            'valid' => false,
                            'message' => 'Hasło musi mieć co najmniej 10 znaków'
                        ];
                    }
                    if (!preg_match('/[A-Z]/', $value)) {
                        $response = [
                            'valid' => false,
                            'message' => 'Hasło musi zawierać co najmniej jedną dużą literę'
                        ];
                    }

                    if (!preg_match('/[0-9]/', $value)) {
                        $response = [
                            'valid' => false,
                            'message' => 'Hasło musi zawierać co najmniej jedną cyfrę'
                        ];
                    }

                    if (!preg_match('/[!@#$%^&*()_+={};:"\'<>,.]/', $value)) {
                        $response = [
                            'valid' => false,
                            'message' => 'Hasło musi zawierać co najmniej jeden znak specjalny'
                        ];
                    }
                    break;
                default:
                    if (empty($value)) {
                        $response = ['valid' => false, 'message' => 'To pole jest wymagane'];
                    }
                    break;
            }
            echo json_encode($response);
            return;
        }
    }

    private function generateRegistrationCode($username)
    {
        $data = $username . microtime(true) * 1000;
        return md5($data);
    }
}
