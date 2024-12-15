<?php

namespace App\Controller;

use App\Entity\Task;
use App\Entity\User;
use App\Entity\Token;
use App\Entity\Project;
use App\Entity\TaskUser;
use App\Helpers\AuthHelpers;

/**
 * Kontroler do zarządzania zadaniami twórcy.
 */
class CreatorController extends BaseController
{
    /**
     * Wyświetla pulpit twórcy z zadaniami.
     * Sprawdza uprawnienia użytkownika i wyświetla odpowiednią stronę.
     */
    public function displayDashboard(): void
    {
        if ($this->checkRole('creator')) {
            $userId = $this->auth->getUserId();
            $taskRepository = $this->getRepository(Task::class);

            // Przygotowanie danych dla pulpit twórcy
            $data = [
                'pageTitle' => 'Pulpit',
                'tasksCount' => count($taskRepository->getAllTasksByUserId($userId)),
                'tasksStart' => count($taskRepository->getTasksByProgress($userId, 1)),
                'tasksInProgress' => count($taskRepository->getTasksByProgress($userId, 2)),
                'tasksDone' => count($taskRepository->getTasksByProgress($userId, 3)),
            ];
            $this->render('creator/creator_dashboard', $data);
        } else {
            header('Location: /login');
            exit();
        }
    }

    /**
     * Wyświetla formularz przypisywania użytkownika do projektu.
     * Sprawdza uprawnienia użytkownika przed wyświetleniem formularza.
     */
    public function displayDelegateForm(): void
    {
        // Sprawdzamy, czy użytkownik jest zalogowany
        if ($this->checkRole('creator')) {
            // Pobieramy repozytorium użytkowników i projektów
            $userRepository = $this->getRepository(User::class);
            $projectRepository = $this->getRepository(Project::class);
            $loggedInUserId = $this->auth->getUserId();

            // Generowanie tokena CSRF
            $csrf = $_SESSION['csrf_token'] = AuthHelpers::generateCSRFToken();

            // Pobieramy zalogowanego użytkownika na podstawie ID
            $loggedInUser = $userRepository->find($loggedInUserId);
            $registrationToken = $loggedInUser ? $loggedInUser->getRegistrationToken() : null;

            // Pobieramy projekty użytkownika wraz z zadaniami i przypisanymi użytkownikami
            $projects = $projectRepository->getProjectWithTasksAndUsers($loggedInUserId);

            // Pobieramy wszystkich użytkowników z tym samym tokenem rejestracyjnym
            $users = $registrationToken ? $userRepository->findUsersByRegistrationToken($registrationToken, $loggedInUserId) : [];

            // Przygotowujemy dane do widoku
            $data = [
                'pageTitle' => 'Przypisz użytkownika',
                'userProjects' => $projects,
                'users' => $users,
                'csrfToken' => $csrf,
            ];

            // Renderujemy widok
            $this->render('creator/creator_delegate', $data);
        } else {
            // Przekierowanie na stronę logowania, jeśli użytkownik nie jest zalogowany
            header('Location: /login');
            exit();
        }
    }

    /**
     * Wyświetla formularz generowania kodu rejestracyjnego.
     * Sprawdza rolę użytkownika przed wyświetleniem formularza.
     */
    public function displayRegistrationCode(): void
    {
        if ($this->checkRole('creator')) {
            $userRepository = $this->getRepository(User::class);
            $user = $userRepository->find($this->auth->getUserId());
            $registrationToken = $user->getRegistrationToken();

            // Generowanie tokena CSRF
            $csrf = $_SESSION['csrf_token'] = AuthHelpers::generateCSRFToken();

            // Znajdź innych użytkowników z tym samym tokenem
            $users = $userRepository->findUsersByRegistrationToken($registrationToken, $user->getUserId());

            // Przekazywanie tokena CSRF do widoku
            $data = [
                'pageTitle' => 'Generuj kod użytkownikowi',
                'token' => $registrationToken,
                'users' => $users,
                'csrfToken' => $csrf,
                'baseUrl' => $_ENV['BASE_URL']
            ];

            $this->render('creator/creator_registration_code', $data);
        } else {
            header('Location: /login');
            exit();
        }
    }

    public function generateToken(): void
    {
        $responseData = [];

        // Pobranie danych z żądania
        $postData = json_decode(file_get_contents('php://input'), true);

        // Sprawdzenie tokena CSRF
        if (!isset($postData['csrf_token']) || !AuthHelpers::verifyCSRFToken($postData['csrf_token'])) {
            http_response_code(403); // Forbidden
            $responseData['error'] = 'Nieprawidłowy token CSRF';
            echo json_encode($responseData);
            return;
        }

        // Sprawdzenie uprawnień użytkownika
        if (!$this->checkRole('creator')) {
            http_response_code(403); // Forbidden
            $responseData['error'] = 'Brak odpowiednich uprawnień do wykonania tej operacji.';
            echo json_encode($responseData);
            return;
        }

        // Pobranie ID zalogowanego użytkownika
        $userId = $this->auth->getUserId();
        $user = $this->getRepository(User::class)->find($userId);

        if (!$user) {
            http_response_code(404); // Not Found
            $responseData['error'] = 'Użytkownik nie został znaleziony.';
            echo json_encode($responseData);
            return;
        }

        // Sprawdzenie liczby istniejących tokenów
        $tokenRepository = $this->getRepository(Token::class);
        $tokenCount = $tokenRepository->getTokenCountByUserId($userId);

        if ($tokenCount >= 10) {
            http_response_code(400); // Bad Request
            $responseData['error'] = 'Użytkownik ma już maksymalną liczbę tokenów.';
            echo json_encode($responseData);
            return;
        }

        // Generowanie nowego tokena
        $newTokenValue = bin2hex(random_bytes(16));
        $token = $tokenRepository->createTokenForUser($user, $newTokenValue);

        $responseData['success'] = true;
        $responseData['token_id'] = $token->getId();
        $responseData['token'] = $token->getToken();
        $responseData['expiration'] = $token->getExpiration()->format('Y-m-d H:i:s');
        http_response_code(200);

        echo json_encode($responseData);
    }


    /**
     * Pobiera linki powiązane z zalogowanym użytkownikiem i zwraca je jako dane JSON.
     * Pobiera linki na podstawie id użytkownika z sesji.
     */
    public function getLinks(): void
    {
        // Sprawdzenie uprawnień użytkownika
        if (!$this->checkRole('creator')) {
            $response = [
                'success' => false,
                'message' => 'Brak odpowiednich uprawnień do wykonania tej operacji.'
            ];
            echo json_encode($response);
            return;
        }
        $tokenRepository = $this->getRepository(Token::class);
        $userId = $this->auth->getUserId();
        $tokens = $tokenRepository->getTokensByUserId($userId);
        $formattedTokens = array_map(function ($token) {
            return [
                'token_id' => $token['id'],
                'token' => $token['token'],
                'expiration' => $token['expiration']->format('Y-m-d H:i:s')
            ];
        }, $tokens);

        echo json_encode(['links' => $formattedTokens]);
    }

    /**
     * Usuwa token rejestracyjny na podstawie jego identyfikatora.
     * Pobiera token, sprawdza jego istnienie i usuwa go z bazy danych użytkownika.
     *
     * @param int $tokenId Identyfikator tokena do usunięcia
     */
    public function deleteToken(int $tokenId): void
    {
        $responseData = [];

        // Pobieranie danych z ciała żądania
        $postData = json_decode(file_get_contents('php://input'), true);

        // Sprawdzenie tokena CSRF
        if (!isset($postData['csrf_token']) || !AuthHelpers::verifyCSRFToken($postData['csrf_token'])) {
            http_response_code(403);
            $responseData['error'] = 'Nieprawidłowy token CSRF';
            echo json_encode($responseData);
            return;
        }

        // Sprawdzenie uprawnień użytkownika
        if (!$this->checkRole('creator')) {
            $responseData['success'] = false;
            $responseData['message'] = 'Brak odpowiednich uprawnień do wykonania tej operacji.';
            echo json_encode($responseData);
            return;
        }

        // Pobranie tokena z bazy danych
        $tokenRepository = $this->getRepository(Token::class);
        $token = $tokenRepository->find($tokenId);

        if (!$token) {
            http_response_code(404);
            echo json_encode(['message' => 'Token nie został znaleziony']);
            return;
        }

        // Usunięcie tokena
        $tokenRepository->remove($token);

        // Zwrócenie odpowiedzi
        echo json_encode(['message' => 'Token "' . $token->getToken() . '" został pomyślnie usunięty']);
    }
}
