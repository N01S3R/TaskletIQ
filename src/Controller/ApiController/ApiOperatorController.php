<?php

namespace App\Controller\ApiController;

use App\Entity\User;
use App\Entity\Token;
use App\Helpers\AuthHelpers;
use App\Controller\BaseController;

class ApiOperatorController extends BaseController
{
    /**
     * Zmienia hasło użytkownika.
     *
     * @param array $data Dane wejściowe zawierające aktualne i nowe hasło.
     * @return void
     */
    public function changePassword(array $data): void
    {
        if (!$this->checkRole('operator')) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Nie masz uprawnień.'
            ]);
            return;
        }

        if (!isset($data['currentPassword'], $data['newPassword'])) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Błędne dane wejściowe.'
            ]);
            return;
        }

        $userId = $this->auth->getUserId();
        $currentPassword = $data['currentPassword'];
        $newPassword = $data['newPassword'];

        $userRepository = $this->getRepository(User::class);
        $result = $userRepository->changePassword($userId, $currentPassword, $newPassword);
        if ($result) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Hasło zostało zmienione pomyślnie.'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Nie udało się zmienić hasła. Sprawdź swoje aktualne hasło.'
            ]);
        }
    }
}
