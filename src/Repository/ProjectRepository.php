<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;
use App\Entity\Project;

class ProjectRepository extends EntityRepository
{
    /**
     * Zwraca wszystkie projekty.
     *
     * @return Project[]
     */
    public function getAllProjects(): array
    {
        return $this->findAll();
    }

    /**
     * Znajduje projekt po ID.
     *
     * @param int $projectId
     * @return Project|null
     */
    public function getProjectById(int $projectId, int $userId): ?Project
    {
        return $this->createQueryBuilder('p')
            ->join('p.user', 'u')
            ->where('p.projectId = :projectId')
            ->andWhere('u.userId = :userId')
            ->setParameter('projectId', $projectId)
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Zwraca projekty powiązane z użytkownikiem o danym ID.
     *
     * @param int $userId
     * @return Project[]
     */
    public function getProjectsByUserId(int $userId): array
    {
        return $this->createQueryBuilder('p')
            ->join('p.user', 'u')
            ->where('u.userId = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getResult();
    }

    /**
     * Zwraca wszystkie projekty z wybranymi polami.
     *
     * @return array
     */
    public function findAllProjects(): array
    {
        $qb = $this->createQueryBuilder('p')
            ->select('p.projectId', 'p.createdAt');

        return $qb->getQuery()->getArrayResult();
    }

    /**
     * Zlicza projekty utworzone w danym miesiącu i roku.
     *
     * @param int $year
     * @param int $month
     * @return int
     */
    public function countProjectsByMonth(int $year, int $month): int
    {
        $qb = $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('YEAR(p.createdAt) = :year')
            ->andWhere('MONTH(p.createdAt) = :month')
            ->setParameter('year', $year)
            ->setParameter('month', $month);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Zlicza wszystkie projekty.
     *
     * @return int
     */
    public function countAllProjects(): int
    {
        return $this->createQueryBuilder('p')
            ->select('COUNT(p.projectId)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Pobiera zadania przypisane do projektu na podstawie jego ID.
     *
     * @param int $projectId ID projektu.
     * @return array Tablica zadań przypisanych do projektu.
     */
    public function getTasksByProjectId($projectId): array
    {
        $project = $this->find($projectId);
        return $project ? $project->getTasks()->toArray() : [];
    }

    /**
     * Pobiera projekty przypisane do danego użytkownika wraz z zadaniami w uproszczonej formie.
     *
     * @param int $userId Identyfikator użytkownika, którego projekty mają zostać pobrane.
     * @return array Tablica projektów z przypisanymi zadaniami jako tablice.
     */
    public function getProjectsWithTasksByUserId(int $userId): array
    {
        $projects = $this->createQueryBuilder('p')
            ->leftJoin('p.tasks', 't')
            ->join('p.user', 'u')
            ->where('u.userId = :userId')
            ->setParameter('userId', $userId)
            ->addSelect('t')
            ->getQuery()
            ->getResult();

        $cleanProjects = [];
        foreach ($projects as $project) {
            $cleanProjects[] = [
                'project_id' => $project->getProjectId(),
                'project_name' => $project->getProjectName(),
                'tasks' => array_map(function ($task) {
                    return [
                        'task_id' => $task->getTaskId(),
                        'task_name' => $task->getTaskName(),
                        'task_progress' => $task->getTaskProgress(),
                        'task_description' => $task->getTaskDescription(),
                    ];
                }, $project->getTasks()->toArray() ?: []),
            ];
        }

        return $cleanProjects;
    }
}
