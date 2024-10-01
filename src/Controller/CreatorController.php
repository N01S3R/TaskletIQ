<?php

namespace App\Controller;

use App\Entity\Task;
use App\Entity\User;
use App\Entity\Token;
use App\Entity\Project;
use App\Entity\TaskUser;

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
        if ($this->auth->getUserId()) {
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
        if ($this->auth->getUserId()) {
            // Pobieramy repozytorium użytkowników i projektów
            $userRepository = $this->getRepository(User::class);
            $projectRepository = $this->getRepository(Project::class);
            $loggedInUserId = $this->auth->getUserId();

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

            // Znajdź innych użytkowników z tym samym tokenem
            $users = $userRepository->findUsersByRegistrationToken($registrationToken, $user->getUserId());

            $data = [
                'pageTitle' => 'Generuj kod użytkownikowi',
                'token' => $registrationToken,
                'users' => $users,
            ];
            $this->render('creator/creator_registration_code', $data);
        } else {
            header('Location: /login');
            exit();
        }
    }

    /**
     * Generuje nowy token dla użytkownika.
     * Sprawdza liczbę istniejących tokenów i generuje nowy token, jeśli to możliwe.
     *
     * @param string $token Wartość tokena
     */
    public function generateToken(): void
    {
        if (!$this->checkRole('creator')) {
            $response = [
                'success' => false,
                'message' => 'Brak odpowiednich uprawnień do wykonania tej operacji.'
            ];
            echo json_encode($response);
            return;
        }

        $userId = $this->auth->getUserId();
        $user = $this->getRepository(User::class)->find($userId);

        if (!$user) {
            $response = [
                'success' => false,
                'message' => 'Użytkownik nie został znaleziony.'
            ];
            echo json_encode($response);
            return;
        }

        $tokenRepository = $this->getRepository(Token::class);
        $tokenCount = $tokenRepository->getTokenCountByUserId($userId);

        if ($tokenCount >= 10) {
            $response = [
                'success' => false,
                'message' => 'Użytkownik ma już maksymalną liczbę tokenów.'
            ];
            echo json_encode($response);
            return;
        }

        $newTokenValue = bin2hex(random_bytes(16));
        $token = $tokenRepository->createTokenForUser($user, $newTokenValue);

        $response = [
            'success' => true,
            'token_id' => $token->getId(),
            'token' => $token->getToken(),
            'expiration' => $token->getExpiration()->format('Y-m-d H:i:s')
        ];
        echo json_encode($response);
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
        if (!$this->checkRole('creator')) {
            $response = [
                'success' => false,
                'message' => 'Brak odpowiednich uprawnień do wykonania tej operacji.'
            ];
            echo json_encode($response);
            return;
        }

        $tokenRepository = $this->getRepository(Token::class);
        $token = $tokenRepository->find($tokenId);

        if (!$token) {
            http_response_code(404);
            echo json_encode(['message' => 'Token nie został znaleziony']);
            return;
        }

        $tokenRepository->remove($token);

        echo json_encode(['message' => 'Token "' . $token->getToken() . '" został pomyślnie usunięty']);
    }
}
