<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Token;
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
     * Jeśli użytkownik jest zalogowany, następuje przekierowanie do odpowiedniej strony. 
     * Jeśli nie, wyświetlany jest formularz rejestracji z opcjonalnym kodem rejestracyjnym.
     *
     * @param string $registrationCode Opcjonalny kod rejestracyjny.
     * @return void
     */
    public function index($registrationCode): void
    {
        if ($this->auth->getUserId()) {
            $userRole = $this->auth->getUserRole();
            $redirectUrl = ($userRole === 'creator') ? 'creator/dashboard' : 'operator/dashboard';
            header('Location: ' . $_ENV['BASE_URL'] . $redirectUrl);
            exit;
        }

        if ($registrationCode) {
            $tokenRepository = $this->getRepository(Token::class);
            $existingToken = $tokenRepository->existsToken($registrationCode);

            if (!$existingToken) {
                $registrationCode = '';
            }
        } else {
            $registrationCode = '';
        }

        $this->render('register_form', ['registrationCode' => $registrationCode]);
    }

    /**
     * Przetwarza dane rejestracji użytkownika.
     * 
     * @return void
     */
    public function registerUser(array $requestData): void
    {
        $name = $requestData['fullName'];
        $email = $requestData['email'];
        $username = $requestData['username'];
        $password = $requestData['password'];
        $registrationCode = $requestData['registration_code'];
        $avatar = (!empty($requestData['registration_code'])) ? 'operator.png' : 'creator.png';
        $role = (!empty($requestData['registration_code'])) ? 'operator' : 'creator';

        $success = $this->auth->register($name, $email, $username, $password, $avatar, $role, $registrationCode);

        if ($success) {
            echo json_encode(['message' => 'Konto zostało utworzone']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Błąd podczas rejestracji']);
        }
    }

    /**
     * Waliduje dane rejestracji.
     * 
     * @param array $data Dane rejestracyjne
     * 
     * @return bool
     */
    public function validateSignupData(array $data)
    {
        if (!isset($data['field'], $data['value'])) {
            http_response_code(400);
            return false;
        }

        $field = $data['field'];
        $value = $data['value'];
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
                if (preg_match('/^.{11,}$/', $value)) {
                    $response = [
                        'valid' => false,
                        'message' => 'Nazwa użytkownika może mieć maksymalnie 11 znaków'
                    ];
                } elseif (!preg_match('/^[a-zA-Z0-9]+$/', $value)) {
                    $response = [
                        'valid' => false,
                        'message' => 'Nazwa użytkownika może zawierać tylko litery i cyfry'
                    ];
                } elseif ($userRepository->findByLogin($value) !== null) {
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
