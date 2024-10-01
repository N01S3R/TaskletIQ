<?php

namespace App\Repository;

use App\Entity\Task;
use App\Entity\User;
use App\Entity\Project;
use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

class UserRepository extends EntityRepository
{
    /**
     * Znajduje użytkownika po nazwie użytkownika.
     *
     * @param string $username
     * @return User|null
     */
    public function findByUsername(string $username): ?User
    {
        return $this->findOneBy(['username' => $username]);
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
}
