<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Project;
use Doctrine\ORM\EntityRepository;

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
     * Zwraca zliczone ilości projektów utworzonych w każdym miesiącu bieżącego roku.
     *
     * @return array
     */
    public function countProjectsPerMonthThisYear(): array
    {
        $currentYear = (new \DateTime())->format('Y');

        $projectsCountByMonth = array_fill(1, 12, 0);

        $projects = $this->createQueryBuilder('p')
            ->where('p.createdAt >= :startDate AND p.createdAt < :endDate')
            ->setParameter('startDate', "$currentYear-01-01 00:00:00")
            ->setParameter('endDate', "$currentYear-12-31 23:59:59")
            ->getQuery()
            ->getResult();

        foreach ($projects as $project) {
            $month = (int) $project->getCreatedAt()->format('m');
            $projectsCountByMonth[$month]++;
        }

        return $projectsCountByMonth;
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
     * Pobiera projekty, zadania oraz użytkowników przypisanych do zadań dla danego użytkownika.
     *
     * @param int $userId Identyfikator użytkownika, dla którego mają być pobrane projekty i zadania.
     * @return array Zwraca tablicę projektów, zadań oraz użytkowników.
     */
    public function getProjectWithTasksAndUsers(int $userId): array
    {
        $qb = $this->createQueryBuilder('p')
            ->select('p.projectId AS project_id, p.projectName AS project_name, 
              t.taskId AS task_id, t.taskName AS task_name, 
              t.taskDescription AS task_description, t.taskCreatedAt AS task_created_at,
              u.userId AS user_id, u.login AS user_login, u.email AS user_email, u.avatar AS user_avatar')
            ->leftJoin('p.tasks', 't')
            ->leftJoin('t.users', 'u')
            ->where('p.user = :userId')
            ->setParameter('userId', $userId);

        $result = $qb->getQuery()->getArrayResult();

        // Grupowanie projektów według identyfikatora projektu
        $groupedProjects = [];
        foreach ($result as $row) {
            $projectId = $row['project_id'];
            if (!isset($groupedProjects[$projectId])) {
                $groupedProjects[$projectId] = [
                    'project_id' => $row['project_id'],
                    'project_name' => $row['project_name'],
                    'tasks' => [],
                ];
            }

            if ($row['task_id'] !== null) {
                $taskId = (int) $row['task_id'];
                if (!isset($groupedProjects[$projectId]['tasks'][$taskId])) {
                    $groupedProjects[$projectId]['tasks'][$taskId] = [
                        'task_id' => $taskId,
                        'task_name' => $row['task_name'],
                        'task_description' => $row['task_description'],
                        'created_at' => $row['task_created_at'],
                        'users' => [],
                    ];
                }

                // Dodaj użytkowników do zadania
                if ($row['user_id']) {
                    $groupedProjects[$projectId]['tasks'][$taskId]['users'][] = [
                        'user_id' => $row['user_id'],
                        'user_login' => $row['user_login'],
                        'user_avatar' => $row['user_avatar'],
                    ];
                }
            }
        }

        // Zamień indeksy na numery i zwróć wynik
        return array_values($groupedProjects);
    }
}
