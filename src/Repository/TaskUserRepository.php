<?php

namespace App\Repository;

use App\Entity\Task;
use App\Entity\User;
use App\Entity\TaskUser;
use Doctrine\ORM\EntityRepository;

class TaskUserRepository extends EntityRepository
{
    /**
     * Pobiera użytkowników przypisanych do konkretnego zadania.
     *
     * @param int $taskId
     * @return array
     */
    public function findUsersByTaskId(int $taskId): array
    {
        $qb = $this->createQueryBuilder('tu')
            ->select('u')
            ->join('tu.user', 'u')
            ->where('tu.task = :taskId')
            ->setParameter('taskId', $taskId);

        return $qb->getQuery()->getResult();
    }

    /**
     * Sprawdza, czy użytkownik jest przypisany do zadania.
     *
     * @param int $taskId
     * @param int $userId
     * @return bool
     */
    public function isUserAssignedToTask(int $taskId, int $userId): bool
    {
        $qb = $this->createQueryBuilder('tu')
            ->select('COUNT(tu.id)')
            ->where('tu.task = :taskId')
            ->andWhere('tu.user = :userId')
            ->setParameter('taskId', $taskId)
            ->setParameter('userId', $userId);

        $count = (int) $qb->getQuery()->getSingleScalarResult();

        return $count > 0;
    }

    /**
     * Zwraca liczbę użytkowników przypisanych do zadania.
     *
     * @param int $taskId
     * @return int
     */
    public function getAssignedUsersCount(int $taskId): int
    {
        $qb = $this->createQueryBuilder('tu')
            ->select('COUNT(tu.id)')
            ->where('tu.task = :taskId')
            ->setParameter('taskId', $taskId);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Przypisuje użytkownika do zadania.
     *
     * @param int $taskId
     * @param int $userId
     * @return void
     */
    public function assignTaskToUser(int $taskId, int $userId): void
    {
        $taskUser = new TaskUser();
        $taskUser->setTask($this->getEntityManager()->getRepository(Task::class)->find($taskId));
        $taskUser->setUser($this->getEntityManager()->getRepository(User::class)->find($userId));

        $this->_em->persist($taskUser);
        $this->_em->flush();
    }

    /**
     * Usuwa przypisanie użytkownika do zadania.
     *
     * @param int $taskId
     * @param int $userId
     * @return void
     */
    public function removeUserAssignmentToTask(int $taskId, int $userId): void
    {
        $qb = $this->createQueryBuilder('tu')
            ->delete()
            ->where('tu.task = :taskId')
            ->andWhere('tu.user = :userId')
            ->setParameter('taskId', $taskId)
            ->setParameter('userId', $userId);

        $qb->getQuery()->execute();
    }
}
