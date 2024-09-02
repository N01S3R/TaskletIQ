<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\Auth;
use App\Helpers\AuthHelpers;
use App\Repository\UserRepository;

class RegisterController extends BaseController
{
    private UserRepository $userRepository;

    /**
     * Konstruktor klasy RegisterController.
     * 
     * @param Auth $auth Instancja serwisu Auth
     * @param UserRepository $userRepository Instancja repozytorium użytkowników
     */
    public function __construct(Auth $auth, UserRepository $userRepository)
    {
        parent::__construct($auth);
        $this->userRepository = $userRepository;
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
    private function validateSignupData(array $data): bool
    {
        return isset($data['fullName'], $data['email'], $data['username'], $data['password']);
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
