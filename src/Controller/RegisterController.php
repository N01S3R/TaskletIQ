<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\Auth;
use App\Helpers\AuthHelpers;
use Doctrine\ORM\EntityManager;

class RegisterController extends BaseController
{
    public function __construct(EntityManager $entityManager)
    {
        parent::__construct($entityManager);
    }

    /**
     * Wyświetla formularz rejestracji lub przekierowuje zalogowanych użytkowników.
     * 
     * @return void
     */
    public function index(): void
    {
        if ($this->auth->getUserId()) {
            $userRole = $this->auth->getUserRole();
            $redirectUrl = ($userRole === 'creator') ? 'creator/dashboard' : 'operator/dashboard';
            header('Location: ' . getenv('BASE_URL') . $redirectUrl);
            exit;
        } else {
            // Renderujemy formularz rejestracji
            $this->render('register_form');
        }
    }

    /**
     * Przetwarza dane rejestracji użytkownika.
     * 
     * @return void
     */
    public function register(): void
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
            $password = filter_var($requestData['password'], FILTER_SANITIZE_STRING);
            $registrationCode = filter_var($requestData['registration_code'], FILTER_SANITIZE_STRING);

            $userRepository = $this->getRepository(User::class);
            $role = $userRepository->isRegistrationCodeUnique($registrationCode) ? 'operator' : 'creator';
            if ($role === 'creator') {
                $registrationCode = $this->generateRegistrationCode($username);
            }

            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $success = $this->auth->register($name, $email, $username, $hashedPassword, $registrationCode, $role);

            if ($success) {
                echo json_encode(['message' => 'Konto zostało utworzone']);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Błąd podczas rejestracji']);
            }
        }
    }

    /**
     * Waliduje dane rejestracji.
     * 
     * @param array $data Dane rejestracyjne
     * 
     * @return bool
     */
    public function validateSignupData()
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

            // Użycie repozytoriów do sprawdzania unikalności
            $userRepository = $this->getRepository(User::class);

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
                    } elseif ($userRepository->findByUsername($value) !== null) {
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
                    } elseif ($userRepository->findByEmail($value) !== null) {
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

    /**
     * Generuje unikalny kod rejestracyjny na podstawie nazwy użytkownika.
     * 
     * @param string $username Nazwa użytkownika
     * 
     * @return string Kod rejestracyjny
     */
    private function generateRegistrationCode(string $username): string
    {
        $data = $username . microtime(true) * 1000;
        return md5($data);
    }
}
