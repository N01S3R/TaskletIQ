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
    public function displayRegister($registrationCode): void
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
}
