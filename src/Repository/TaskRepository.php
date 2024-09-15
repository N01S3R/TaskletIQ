<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;
use App\Entity\Task;

class TaskRepository extends EntityRepository
{
    /**
     * Zwraca wszystkie zadania.
     *
     * @return Task[]
     */
    public function getAllTasks(): array
    {
        return $this->findAll();
    }

    /**
     * Znajduje zadanie po ID.
     *
     * @param int $taskId
     * @return Task|null
     */
    public function getTaskById(int $taskId): ?Task
    {
        return $this->find($taskId);
    }

    /**
     * Zwraca wszystkie zadania powiązane z projektem o danym ID.
     *
     * @param int $projectId
     * @return Task[]
     */
    public function getTasksByProjectId(int $projectId): array
    {
        return $this->findBy(['project' => $projectId]);
    }

    /**
     * Zlicza wszystkie zadania.
     *
     * @return int
     */
    public function countAllTasks(): int
    {
        return $this->createQueryBuilder('t')
            ->select('COUNT(t.taskId)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Zwraca zadania przypisane do użytkownika wraz z informacjami o projektach.
     *
     * @param int $userId
     * @return array
     */
    public function getTasksByUserIdWithProjects(int $userId): array
    {
        // Tworzymy zapytanie do pobrania zadań oraz powiązanych projektów
        $qb = $this->createQueryBuilder('t')
            ->innerJoin('t.project', 'p')
            ->innerJoin('p.user', 'u')
            ->where('u.userId = :userId')
            ->setParameter('userId', $userId)
            ->addSelect('p')
            ->getQuery();

        return $qb->getArrayResult();
    }

    /**
     * Zlicza zadania o określonym poziomie postępu dla danego użytkownika.
     *
     * @param int $userId
     * @param int $progress
     * @return int
     */
    public function getTasksByProgress(int $userId, int $progress): int
    {
        return $this->createQueryBuilder('t')
            ->select('COUNT(t.taskId)')
            ->where('t.user = :userId')
            ->andWhere('t.taskProgress = :progress')
            ->setParameter('userId', $userId)
            ->setParameter('progress', $progress)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
