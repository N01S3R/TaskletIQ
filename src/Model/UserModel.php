<?php

namespace App\Model;

use PDO;
use PDOException;
use Exception;

class UserModel extends BaseModel
{
    /**
     * Sprawdza, czy nazwa użytkownika jest unikalna.
     *
     * @param string $username Nazwa użytkownika do sprawdzenia
     * @return bool True, jeśli nazwa użytkownika jest unikalna; false w przeciwnym razie
     * @throws PDOException Błąd PDO podczas wykonania zapytania
     */
    public function isUsernameUnique(string $username): bool
    {
        $query = $this->db->prepare("SELECT COUNT(*) FROM users WHERE user_login = :username");
        $query->bindParam(':username', $username, PDO::PARAM_STR);
        $query->execute();

        $count = (int) $query->fetchColumn();

        return $count === 0;
    }

    /**
     * Rejestruje nowego użytkownika.
     *
     * @param string $name Imię użytkownika
     * @param string $email Adres email użytkownika
     * @param string $username Nazwa użytkownika
     * @param string $password Hasło użytkownika
     * @param string $registrationCode Token rejestracyjny
     * @param string $role Rola użytkownika
     * @return void
     * @throws PDOException Błąd PDO podczas wykonania zapytania
     */
    public function register(string $name, string $email, string $username, string $password, string $registrationCode, string $role): void
    {
        // Sprawdź unikalność nazwy użytkownika
        if ($this->isUsernameUnique($username)) {
            // Kontynuuj proces rejestracji
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            // Przygotowanie zapytania SQL
            $query = $this->db->prepare("INSERT INTO users (user_name, user_email, user_login, user_password, registration_token, user_role) VALUES (:name, :email, :username, :password, :registration_token, :role)");

            // Wiązanie parametrów i wykonanie zapytania
            $query->bindParam(':name', $name, PDO::PARAM_STR);
            $query->bindParam(':email', $email, PDO::PARAM_STR);
            $query->bindParam(':username', $username, PDO::PARAM_STR);
            $query->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
            $query->bindParam(':registration_token', $registrationCode, PDO::PARAM_STR);
            $query->bindParam(':role', $role, PDO::PARAM_STR);
            $query->execute();
        } else {
            // Obsługa, gdy nazwa użytkownika nie jest unikalna
            throw new Exception('Nazwa użytkownika nie jest unikalna');
        }
    }

    /**
     * Loguje użytkownika na podstawie nazwy użytkownika i hasła.
     *
     * @param string $username Nazwa użytkownika
     * @param string $password Hasło użytkownika
     * @return array|false Tablica danych użytkownika lub false, jeśli logowanie nie powiedzie się
     * @throws PDOException Błąd PDO podczas wykonania zapytania
     */
    public function login(string $username, string $password)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE user_login = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['user_password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_name'] = $user['user_name'];
            $_SESSION['user_login'] = $user['user_login'];
            $_SESSION['user_avatar'] = $user['user_avatar'];
            $_SESSION['user_role'] = $user['user_role'];
            $_SESSION['user_status'] = $this->getUserStatus($user['user_id']);
            // Ustawienie statusu użytkownika na 'online'
            $this->setUserOnline($user['user_id']);

            return $user;
        } else {
            return false;
        }
    }

    /**
     * Ustawia token dla użytkownika.
     *
     * @param int $userId ID użytkownika
     * @param string $token Token do ustawienia
     * @return array|null Tablica danych tokena lub null, jeśli nie udało się ustawić tokenu
     * @throws PDOException Błąd PDO podczas wykonania zapytania
     */
    public function setToken(int $userId, string $token): ?array
    {
        try {
            $query = $this->db->prepare("INSERT INTO tokens (user_id, token) VALUES (:user_id, :token)");
            $query->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $query->bindParam(':token', $token, PDO::PARAM_STR);
            $query->execute();

            $lastInsertedId = $this->db->lastInsertId();
            $lastToken = $this->getTokenById($lastInsertedId);

            return $lastToken;
        } catch (PDOException $e) {
            error_log("Błąd: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Pobiera token na podstawie jego ID.
     *
     * @param int $tokenId ID tokenu
     * @return array|null Tablica danych tokenu lub null, jeśli token nie istnieje
     * @throws PDOException Błąd PDO podczas wykonania zapytania
     */
    public function getTokenById(int $tokenId): ?array
    {
        try {
            $query = $this->db->prepare("SELECT * FROM tokens WHERE id = :token_id");
            $query->bindParam(':token_id', $tokenId, PDO::PARAM_INT);
            $query->execute();
            $token = $query->fetch(PDO::FETCH_ASSOC);
            return $token;
        } catch (PDOException $e) {
            error_log("Błąd: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Usuwa token na podstawie jego ID.
     *
     * @param int $tokenId ID tokenu do usunięcia
     * @return bool True, jeśli token został pomyślnie usunięty; false w przeciwnym razie
     * @throws PDOException Błąd PDO podczas wykonania zapytania
     */
    public function deleteToken(int $tokenId): bool
    {
        try {
            $query = $this->db->prepare("DELETE FROM tokens WHERE id = :token_id");
            $query->bindParam(':token_id', $tokenId, PDO::PARAM_INT);
            $query->execute();
            return true;
        } catch (PDOException $e) {
            error_log("Błąd: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Pobiera wszystkie tokeny przypisane do danego użytkownika.
     *
     * @param int $userId ID użytkownika
     * @return array|null Tablica danych tokenów lub null, jeśli użytkownik nie ma przypisanych żadnych tokenów
     * @throws PDOException Błąd PDO podczas wykonania zapytania
     */
    public function getLinks(int $userId): ?array
    {
        try {
            $query = $this->db->prepare("SELECT * FROM tokens WHERE user_id = :user_id");
            $query->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $query->execute();
            $links = $query->fetchAll(PDO::FETCH_ASSOC);
            return $links;
        } catch (PDOException $e) {
            error_log("Błąd: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Pobiera wszystkich użytkowników z bazy danych.
     *
     * @return array Tablica danych wszystkich użytkowników
     */
    public function getAllUsers(): array
    {
        $query = $this->db->query("SELECT * FROM users");
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Pobiera rolę użytkownika na podstawie jego ID.
     *
     * @param int $userId ID użytkownika
     * @return string|null Rola użytkownika lub null, jeśli użytkownik nie istnieje
     * @throws PDOException Błąd PDO podczas wykonania zapytania
     */
    public function getUsersRole(int $userId): ?string
    {
        $query = $this->db->prepare("SELECT user_role FROM users WHERE user_id = :user_id");
        $query->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $query->execute();

        if ($query->rowCount() > 0) {
            $result = $query->fetch(PDO::FETCH_ASSOC);
            return $result['user_role'];
        } else {
            return null;
        }
    }

    /**
     * Pobiera ID zalogowanego użytkownika z sesji.
     *
     * @return int|null ID zalogowanego użytkownika lub null, jeśli użytkownik niezalogowany
     * @throws PDOException Błąd PDO podczas wykonania zapytania
     */
    public function getLoggedInUserId(): ?int
    {
        if (isset($_SESSION['user_id'])) {
            $loggedInUserId = $_SESSION['user_id'];

            $query = $this->db->prepare("SELECT user_id FROM users WHERE user_id = :user_id");
            $query->bindParam(':user_id', $loggedInUserId, PDO::PARAM_INT);
            $query->execute();

            $result = $query->fetch(PDO::FETCH_ASSOC);

            return $result ? (int) $result['user_id'] : null;
        }

        return null;
    }

    /**
     * Pobiera użytkownika na podstawie jego ID.
     *
     * @param int $userId ID użytkownika
     * @return array|null Tablica danych użytkownika lub null, jeśli użytkownik nie istnieje
     * @throws PDOException Błąd PDO podczas wykonania zapytania
     */
    public function getUserById(int $userId): ?array
    {
        $query = $this->db->prepare("SELECT * FROM users WHERE user_id = :user_id");
        $query->bindParam(':user_id', $userId, PDO::PARAM_STR);
        $query->execute();

        return $query->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Pobiera zalogowanego użytkownika na podstawie jego ID z sesji.
     *
     * @return array|null Tablica danych zalogowanego użytkownika lub null, jeśli użytkownik niezalogowany
     * @throws PDOException Błąd PDO podczas wykonania zapytania
     */
    public function getLoginUser(): ?array
    {
        $query = $this->db->prepare("SELECT * FROM users WHERE user_id = :user_id");
        $query->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_STR);
        $query->execute();

        return $query->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Sprawdza, czy kod rejestracyjny jest unikalny.
     *
     * @param string $code Kod rejestracyjny do sprawdzenia
     * @return string|null Token rejestracyjny lub null, jeśli kod nie jest unikalny lub użytkownik nie istnieje
     * @throws PDOException Błąd PDO podczas wykonania zapytania
     */
    public function isRegistrationCodeUnique(string $code): ?string
    {
        // Sprawdzanie czy token znajduje się w tabeli tokens
        $query = $this->db->prepare("SELECT user_id FROM tokens WHERE token = :code");
        $query->bindParam(':code', $code, PDO::PARAM_STR);
        $query->execute();

        // Jeśli token znajduje się w tabeli tokens
        if ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            // Pobieranie ID użytkownika
            $userId = $row['user_id'];

            // Sprawdzanie czy istnieje użytkownik o danym ID
            $userQuery = $this->db->prepare("SELECT registration_token FROM users WHERE user_id = :userId");
            $userQuery->bindParam(':userId', $userId, PDO::PARAM_INT);
            $userQuery->execute();

            // Jeśli użytkownik istnieje
            if ($user = $userQuery->fetch(PDO::FETCH_ASSOC)) {
                // Zwracanie tokena rejestracyjnego użytkownika
                return $user['registration_token'];
            }
        }

        // Jeśli token nie znajduje się w tabeli tokens lub nie istnieje użytkownik o danym ID
        return null;
    }

    /**
     * Sprawdza, czy adres email jest unikalny.
     *
     * @param string $email Adres email do sprawdzenia
     * @return bool True, jeśli adres email jest unikalny; false w przeciwnym razie
     * @throws PDOException Błąd PDO podczas wykonania zapytania
     */
    public function isEmailUnique(string $email): bool
    {
        $query = $this->db->prepare("SELECT user_email FROM users WHERE user_email = :email");
        $query->bindParam(':email', $email, PDO::PARAM_STR);
        $query->execute();

        return !$query->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Pobiera imiona użytkowników posortowane po dacie rejestracji (od najnowszej).
     *
     * @return array|null Tablica imion użytkowników lub null, jeśli brak użytkowników
     * @throws PDOException Błąd PDO podczas wykonania zapytania
     */
    public function getUsersSortedByRegistrationDate(): ?array
    {
        try {
            // Pobierz imiona użytkowników posortowane po dacie rejestracji
            $query = $this->db->prepare("SELECT user_name FROM users ORDER BY registration_date LIMIT 10");
            // $query->bindParam(':latest_date', $latestDate, PDO::PARAM_STR);
            $query->execute();

            $userNames = $query->fetchAll(PDO::FETCH_COLUMN);

            return $userNames ?: null;
        } catch (PDOException $e) {
            error_log("Błąd: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Ustawia status użytkownika na 'online'.
     *
     * @param int $userId ID użytkownika
     * @return void
     * @throws PDOException Błąd PDO podczas wykonania zapytania
     */
    public function setUserOnline(int $userId): void
    {
        $query = $this->db->prepare("INSERT INTO user_status (user_id, status) VALUES (:user_id, 'online')
                                     ON DUPLICATE KEY UPDATE status = 'online', last_activity = NOW()");
        $query->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $query->execute();
    }

    /**
     * Ustawia status użytkownika na 'offline'.
     *
     * @param int $userId ID użytkownika
     * @return void
     * @throws PDOException Błąd PDO podczas wykonania zapytania
     */
    public function setUserOffline(int $userId): void
    {
        $query = $this->db->prepare("INSERT INTO user_status (user_id, status) VALUES (:user_id, 'offline')
                                     ON DUPLICATE KEY UPDATE status = 'offline', last_activity = NOW()");
        $query->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $query->execute();
    }

    /**
     * Pobiera status użytkownika na podstawie jego ID.
     *
     * @param int $userId ID użytkownika
     * @return array|null Tablica z danymi statusu użytkownika lub null, jeśli użytkownik nie istnieje
     * @throws PDOException Błąd PDO podczas wykonania zapytania
     */
    public function getUserStatus(int $userId): ?array
    {
        $query = $this->db->prepare("SELECT status, last_activity FROM user_status WHERE user_id = :user_id");
        $query->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $query->execute();

        return $query->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Aktualizuje dane użytkownika.
     *
     * @param int $userId ID użytkownika
     * @param string $name Imię użytkownika
     * @param string $email Email użytkownika
     * @param string $username Nazwa użytkownika
     *
     * @return bool Zwraca true, jeśli operacja zakończyła się sukcesem, w przeciwnym razie false
     */
    public function updateUser(int $userId, string $name, string $email, string $username): bool
    {
        try {
            $query = $this->db->prepare("UPDATE users SET user_name = :name, user_email = :email, user_login = :username WHERE user_id = :user_id");
            $query->bindParam(':name', $name, PDO::PARAM_STR);
            $query->bindParam(':email', $email, PDO::PARAM_STR);
            $query->bindParam(':username', $username, PDO::PARAM_STR);
            $query->bindParam(':user_id', $userId, PDO::PARAM_INT);
            return $query->execute();
        } catch (Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    /**
     * Usuwa użytkownika na podstawie ID.
     *
     * @param int $userId ID użytkownika
     *
     * @return bool Zwraca true, jeśli operacja zakończyła się sukcesem, w przeciwnym razie false
     */
    public function deleteUser(int $userId): bool
    {
        try {
            $query = $this->db->prepare("DELETE FROM users WHERE user_id = :user_id");
            $query->bindParam(':user_id', $userId, PDO::PARAM_INT);
            return $query->execute();
        } catch (Exception $e) {
            // Logowanie błędów
            error_log($e->getMessage());
            return false;
        }
    }
}
