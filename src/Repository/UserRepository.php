<?php

namespace App\Repository;

use App\Entity\Task;
use App\Entity\User;
use App\Entity\Project;
use Doctrine\ORM\EntityRepository;
use App\Config\Mailer;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

class UserRepository extends EntityRepository
{
    /**
     * Zwraca wszystkich użytkowników w formacie tablicy asocjacyjnej.
     *
     * @return array Tablica użytkowników.
     */
    public function getAllUsers(): array
    {
        $users = $this->findAll();
        $userArray = [];

        // Konwersja obiektów User na tablicę
        foreach ($users as $user) {
            $userArray[] = [
                'userId' => $user->getUserId(),
                'login' => $user->getLogin(),
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
                'avatar' => $user->getAvatar(),
                'registrationDate' => $user->getRegistrationDate()->format('Y-m-d H:i:s'),
                'logged' => $user->isLogged(),
                'role' => $user->getRole(),
            ];
        }

        return $userArray;
    }

    /**
     * Znajduje użytkownika po loginie.
     *
     * @param string $login
     * @return User|null
     */
    public function findByLogin(string $login): ?User
    {
        return $this->findOneBy(['login' => $login]);
    }

    /**
     * Znajduje użytkownika po adresie e-mail.
     *
     * @param string $email
     * @return User|null
     */
    public function findByEmail(string $email): ?User
    {
        return $this->findOneBy(['email' => $email]);
    }

    /**
     * Znajduje wszystkich użytkowników posortowanych według nazwy użytkownika.
     *
     * @return User[]
     */
    public function findAllSortedByUsername(): array
    {
        return $this->findBy([], ['username' => 'ASC']);
    }

    /**
     * Dodaje nowego użytkownika do bazy danych.
     *
     * @param User $user
     * @return void
     */
    public function add(User $user): void
    {
        $this->_em->persist($user);
        $this->_em->flush();
    }

    /**
     * Usuwa użytkownika z bazy danych.
     *
     * @param User $user
     * @return void
     */
    public function delete(User $user): void
    {
        $this->_em->remove($user);
        $this->_em->flush();
    }

    /**
     * Znajduje zalogowanego użytkownika po ID.
     *
     * @param int $userId
     * @return User|null
     */
    public function findLoggedInUserById(int $userId): ?User
    {
        return $this->findOneBy(['userId' => $userId, 'logged' => true]);
    }

    /**
     * Ustawia użytkownika jako zalogowanego.
     *
     * @param int $userId
     * @return void
     */
    public function setUserLoggedIn(int $userId): void
    {
        $user = $this->find($userId);
        if ($user) {
            $user->setLogged(true);
            $this->_em->flush();
        }
    }

    /**
     * Ustawia użytkownika jako wylogowanego.
     *
     * @param int $userId
     * @return void
     */
    public function setUserLoggedOut(int $userId): void
    {
        $user = $this->find($userId);
        if ($user) {
            $user->setLogged(false);
            $this->_em->flush();
        }
    }

    /**
     * Znajduje wszystkich użytkowników posortowanych według daty rejestracji.
     *
     * @return User[]
     */
    public function findAllOrderedByRegistrationDate(): array
    {
        return $this->createQueryBuilder('u')
            ->orderBy('u.registrationDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Zlicza wszystkich użytkowników.
     *
     * @return int
     */
    public function countAllUsers(): int
    {
        return $this->createQueryBuilder('u')
            ->select('COUNT(u.userId)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Dodaje projekt do użytkownika.
     *
     * @param User $user
     * @param Project $project
     * @return void
     */
    public function addProjectToUser(User $user, Project $project): void
    {
        if (!$user->getProjects()->contains($project)) {
            $user->getProjects()->add($project);
            $this->_em->persist($user);
            $this->_em->flush();
        }
    }

    /**
     * Usuwa projekt od użytkownika.
     *
     * @param User $user
     * @param Project $project
     * @return void
     */
    public function removeProjectFromUser(User $user, Project $project): void
    {
        if ($user->getProjects()->contains($project)) {
            $user->getProjects()->removeElement($project);
            $this->_em->persist($user);
            $this->_em->flush();
        }
    }

    /**
     * Dodaje zadanie do użytkownika.
     *
     * @param User $user
     * @param Task $task
     * @return void
     */
    public function addTaskToUser(User $user, Task $task): void
    {
        if (!$user->getTasks()->contains($task)) {
            $user->getTasks()->add($task);
            $this->_em->persist($user);
            $this->_em->flush();
        }
    }

    /**
     * Usuwa zadanie od użytkownika.
     *
     * @param User $user
     * @param Task $task
     * @return void
     */
    public function removeTaskFromUser(User $user, Task $task): void
    {
        if ($user->getTasks()->contains($task)) {
            $user->getTasks()->removeElement($task);
            $this->_em->persist($user);
            $this->_em->flush();
        }
    }

    /**
     * Znajduje użytkowników z tym samym tokenem rejestracyjnym, oprócz aktualnie zalogowanego użytkownika.
     *
     * @param string $registrationToken
     * @param int $loggedInUserId
     * @return array
     */
    public function findUsersByRegistrationToken(string $registrationToken, int $loggedInUserId): array
    {
        $qb = $this->createQueryBuilder('u')
            ->select('u.userId AS user_id, u.login AS user_login, u.avatar AS user_avatar')
            ->where('u.registrationToken = :registrationToken')
            ->andWhere('u.userId != :loggedInUserId')
            ->setParameter('registrationToken', $registrationToken)
            ->setParameter('loggedInUserId', $loggedInUserId);

        return $qb->getQuery()->getArrayResult();
    }

    /**
     * Zlicza tokeny przypisane do użytkownika.
     *
     * @param int $userId
     * @return int
     */
    public function getTokenCountByUserId(int $userId): int
    {
        return $this->createQueryBuilder('u')
            ->select('COUNT(t.id)')
            ->join('u.tokens', 't')
            ->where('u.userId = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Dodaje nowego użytkownika do bazy danych i wysyła e-mail z hasłem.
     *
     * @param string $name
     * @param string $email
     * @param string $username
     * @param string $avatar
     * @param string $role
     * @return User|null
     */
    public function createUser(string $name, string $email, string $username, string $avatar, string $role, string $registrationCode): ?User
    {
        // Sprawdzanie, czy użytkownik o podanym emailu lub loginie już istnieje
        if ($this->findByEmail($email) || $this->findByLogin($username)) {
            return null; // Użytkownik już istnieje
        }

        // Tworzenie nowego użytkownika
        $user = new User();
        $user->setUsername($name);
        $user->setEmail($email);
        $user->setLogin($username);

        // Generowanie losowego hasła
        $password = $this->generateRandomPassword(10);
        $user->setPassword(password_hash($password, PASSWORD_BCRYPT));
        $user->setLogged(0);
        $user->setRegistrationToken($registrationCode);
        $user->setAvatar($avatar);
        $user->setRole($role);
        $user->setRegistrationDate(new \DateTime());

        // Zapisanie użytkownika w bazie danych
        $this->_em->persist($user);
        $this->_em->flush();

        // Wysyłanie e-maila z hasłem
        $mailer = new Mailer();
        $mailer->sendPasswordEmail($email, $password);

        return $user;
    }

    /**
     * Generuje losowe hasło.
     *
     * @param int $length
     * @return string
     */
    private function generateRandomPassword(int $length = 10): string
    {
        return bin2hex(random_bytes($length / 2)); // Wygeneruje hasło o podanej długości
    }

    /**
     * Aktualizuje dane użytkownika w bazie danych.
     *
     * @param int $userId
     * @param string|null $username
     * @param string|null $email
     * @param string|null $login
     * @param string|null $avatar
     * @param string|null $role
     * @return bool
     */
    public function updateUser(int $userId, ?string $username, ?string $email, ?string $login, ?string $avatar, ?string $role): bool
    {
        $user = $this->find($userId); // Znajdujemy użytkownika po ID

        if (!$user) {
            return false; // Jeśli użytkownik nie istnieje, zwracamy false
        }

        // Aktualizujemy dane użytkownika tylko wtedy, gdy nowe wartości są różne
        if ($username !== null) {
            $user->setUsername($username);
        }
        if ($email !== null) {
            $user->setEmail($email);
        }
        if ($login !== null) {
            $user->setLogin($login);
        }
        if ($avatar !== null) {
            $user->setAvatar($avatar);
        }
        if ($role !== null) {
            $user->setRole($role);
        }

        // Zapisujemy zmiany w bazie danych
        $this->_em->flush();

        return true; // Zwracamy true, jeśli aktualizacja powiodła się
    }
}
