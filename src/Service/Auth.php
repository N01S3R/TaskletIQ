<?php

namespace App\Service;

use App\Repository\UserRepository;
use App\Helpers\AuthHelpers;

/**
 * Klasa do zarządzania logowaniem, rejestracją i sesjami użytkowników.
 */
class Auth
{
    private UserRepository $userRepository;

    /**
     * Konstruktor klasy Auth.
     * 
     * @param UserRepository $userRepository Instancja repozytorium użytkowników
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Loguje użytkownika.
     * 
     * @param string $username Nazwa użytkownika
     * @param string $password Hasło użytkownika
     * 
     * @return User|false Zalogowany użytkownik lub false w przypadku niepowodzenia
     */
    public function login(string $username, string $password)
    {
        $username = AuthHelpers::sanitizeInput($username);
        $password = AuthHelpers::sanitizeInput($password);

        if (AuthHelpers::validatePassword($password)) {
            $user = $this->userRepository->findByUsername($username);

            if ($user && password_verify($password, $user->getPassword())) {
                $_SESSION['user_id'] = $user->getUserId();
                $_SESSION['user_name'] = $user->getUsername();
                $_SESSION['user_role'] = $user->getRole();
                $_SESSION['user_avatar'] = $user->getAvatar();
                AuthHelpers::setSessionSecurityHeaders();
                $this->userRepository->setUserLoggedIn($user->getUserId());

                return $user;
            }
        }

        return false;
    }

    /**
     * Wylogowuje użytkownika.
     * 
     * @return void
     */
    public function logout(): void
    {
        $userId = $this->getUserId();

        if ($userId) {
            $this->userRepository->setUserLoggedOut($userId);
        }

        session_unset();
        session_destroy();

        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
    }

    /**
     * Rejestruje nowego użytkownika.
     * 
     * @param string $name Nazwa użytkownika
     * @param string $email Adres email
     * @param string $username Nazwa użytkownika
     * @param string $password Hasło użytkownika
     * @param string $registrationCode Kod rejestracyjny
     * @param string $role Rola użytkownika
     * 
     * @return bool Zwraca true, jeśli rejestracja powiodła się, w przeciwnym razie false
     */
    public function register(string $name, string $email, string $username, string $password, string $registrationCode, string $role): bool
    {
        $name = AuthHelpers::sanitizeInput($name);
        $email = AuthHelpers::sanitizeInput($email);
        $username = AuthHelpers::sanitizeInput($username);
        $password = AuthHelpers::sanitizeInput($password);
        $registrationCode = AuthHelpers::sanitizeInput($registrationCode);

        if (AuthHelpers::validatePassword($password) && $this->isValidUsernameAndEmail($username, $email)) {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $this->userRepository->register($name, $email, $username, $hashedPassword, $registrationCode, $role);

            return true;
        }

        return false;
    }

    /**
     * Pobiera ID aktualnie zalogowanego użytkownika.
     * 
     * @return int|null ID użytkownika lub null, jeśli użytkownik nie jest zalogowany
     */
    public function getUserId(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }

    /**
     * Pobiera rolę aktualnie zalogowanego użytkownika.
     * 
     * @return string|null Rola użytkownika lub null, jeśli użytkownik nie jest zalogowany
     */
    public function getUserRole(): ?string
    {
        return $_SESSION['user_role'] ?? null;
    }

    /**
     * Sprawdza, czy nazwa użytkownika i email są unikalne.
     * 
     * @param string $username Nazwa użytkownika
     * @param string $email Adres email
     * 
     * @return bool Zwraca true, jeśli nazwa użytkownika i email są unikalne, w przeciwnym razie false
     */
    private function isValidUsernameAndEmail(string $username, string $email): bool
    {
        if (strlen($username) > 11) {
            return false;
        }

        if (!$this->userRepository->isEmailUnique($email)) {
            return false;
        }

        if (!$this->userRepository->isUsernameUnique($username)) {
            return false;
        }

        return true;
    }
}
