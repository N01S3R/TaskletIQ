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
}
