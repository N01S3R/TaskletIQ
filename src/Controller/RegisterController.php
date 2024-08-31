<?php

namespace App\Controller;

use App\Entity\User;
use App\Helpers\AuthHelpers;
use App\View;

class RegisterController extends BaseController
{
    public function index()
    {
        if (isset($_SESSION['user_id'])) {
            $userId = (int)$_SESSION['user_id'];
            $userRepository = $this->getRepository(User::class);
            $userRole = $userRepository->find($userId)->getRole();

            $redirectUrl = ($userRole === 'creator') ? 'creator/dashboard' : 'operator/dashboard';
            header('Location: ' . getenv('BASE_URL') . $redirectUrl);
            exit;
        } else {
            $this->render('register_form');
        }
    }

    public function signup()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');
            $requestData = json_decode(file_get_contents('php://input'), true);

            if (!$this->validateSignupData($requestData)) {
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

            $userRepository = $this->getRepository(User::class);

            if ($this->isValidUsernameAndEmail($username, $email, $userRepository)) {
                if ($userRepository->isRegistrationCodeUnique($registrationCode)) {
                    $role = 'operator';
                } else {
                    $registrationCode = $this->generateRegistrationCode($username);
                }

                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                $userRepository->register($name, $email, $username, $hashedPassword, $registrationCode, $role);
                echo json_encode(['message' => 'Konto zostało utworzone']);
                return;
            }
        }
    }

    public function validateField()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');
            $requestData = json_decode(file_get_contents('php://input'), true);

            if (!$this->validateValidationData($requestData)) {
                http_response_code(400);
                echo json_encode(['error' => 'Brak wymaganych danych']);
                return;
            }

            $field = $requestData['field'];
            $value = $requestData['value'];
            $response = ['valid' => true, 'message' => ''];

            $response = $this->validateFieldValue($field, $value);

            echo json_encode($response);
            return;
        }
    }

    private function validateSignupData(array $data): bool
    {
        return isset($data['fullName'], $data['email'], $data['username'], $data['password']);
    }

    private function isValidUsernameAndEmail(string $username, string $email, $userRepository): bool
    {
        if (strlen($username) > 11) {
            http_response_code(400);
            echo json_encode(['error' => 'Nazwa użytkownika może mieć maksymalnie 11 znaków']);
            return false;
        }

        if (!$userRepository->isEmailUnique($email)) {
            http_response_code(400);
            echo json_encode(['error' => 'Adres email już istnieje']);
            return false;
        }

        if (!$userRepository->isUsernameUnique($username)) {
            http_response_code(400);
            echo json_encode(['error' => 'Nazwa użytkownika już istnieje']);
            return false;
        }

        return true;
    }

    private function validateValidationData(array $data): bool
    {
        return isset($data['field'], $data['value']);
    }

    private function validateFieldValue(string $field, string $value): array
    {
        switch ($field) {
            case 'fullName':
                if (!preg_match('/^[a-zA-Z]{3,15}$/', $value)) {
                    return [
                        'valid' => false,
                        'message' => 'Imię może zawierać tylko litery od 3 do 15 znaków'
                    ];
                }
                break;
            case 'username':
                if (preg_match('/^.{12,}$/', $value)) {
                    return [
                        'valid' => false,
                        'message' => 'Nazwa użytkownika może mieć maksymalnie 11 znaków'
                    ];
                } elseif (!preg_match('/^[a-zA-Z0-9]+$/', $value)) {
                    return [
                        'valid' => false,
                        'message' => 'Nazwa użytkownika może zawierać tylko litery i cyfry'
                    ];
                } elseif (!$this->getRepository(User::class)->isUsernameUnique($value)) {
                    return [
                        'valid' => false,
                        'message' => 'Nazwa użytkownika już istnieje'
                    ];
                }
                break;
            case 'email':
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    return [
                        'valid' => false,
                        'message' => 'Nieprawidłowy adres email'
                    ];
                } elseif (!$this->getRepository(User::class)->isEmailUnique($value)) {
                    return [
                        'valid' => false,
                        'message' => 'Adres email już istnieje'
                    ];
                }
                break;
            case 'password':
                if (!preg_match('/^.{10,}$/', $value)) {
                    return [
                        'valid' => false,
                        'message' => 'Hasło musi mieć co najmniej 10 znaków'
                    ];
                }
                if (!preg_match('/[A-Z]/', $value)) {
                    return [
                        'valid' => false,
                        'message' => 'Hasło musi zawierać co najmniej jedną dużą literę'
                    ];
                }
                if (!preg_match('/[0-9]/', $value)) {
                    return [
                        'valid' => false,
                        'message' => 'Hasło musi zawierać co najmniej jedną cyfrę'
                    ];
                }
                if (!preg_match('/[!@#$%^&*()_+={};:"\'<>,.]/', $value)) {
                    return [
                        'valid' => false,
                        'message' => 'Hasło musi zawierać co najmniej jeden znak specjalny'
                    ];
                }
                break;
            default:
                if (empty($value)) {
                    return ['valid' => false, 'message' => 'To pole jest wymagane'];
                }
                break;
        }
        return ['valid' => true, 'message' => ''];
    }

    private function generateRegistrationCode(string $username): string
    {
        $data = $username . microtime(true) * 1000;
        return md5($data);
    }
}
