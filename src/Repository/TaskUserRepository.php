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
        return $this->createQueryBuilder('tu')
            ->select('u')
            ->join('tu.user', 'u')
            ->where('tu.task = :taskId')
            ->setParameter('taskId', $taskId)
            ->getQuery()
            ->getResult();
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
        return (bool) $this->createQueryBuilder('tu')
            ->select('COUNT(tu.id)')
            ->where('tu.task = :taskId')
            ->andWhere('tu.user = :userId')
            ->setParameter('taskId', $taskId)
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Zwraca liczbę użytkowników przypisanych do zadania.
     *
     * @param int $taskId
     * @return int
     */
    public function getAssignedUsersCount(int $taskId): int
    {
        return (int) $this->createQueryBuilder('tu')
            ->select('COUNT(tu.id)')
            ->where('tu.task = :taskId')
            ->setParameter('taskId', $taskId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Tworzy relację między zadaniem a użytkownikiem, czyli przypisuje użytkownika do zadania.
     * Tutaj tylko zapytanie, bez walidacji.
     *
     * @param Task $task
     * @param User $user
     * @return void
     */
    public function assignTaskToUser(Task $task, User $user): void
    {
        $taskUser = new TaskUser();
        $taskUser->setTask($task);
        $taskUser->setUser($user);

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
    public function removeUserAssignment(int $taskId, int $userId): void
    {
        $qb = $this->createQueryBuilder('tu')
            ->delete()
            ->where('tu.task = :taskId')
            ->andWhere('tu.user = :userId')
            ->setParameter('taskId', $taskId)
            ->setParameter('userId', $userId);

        $qb->getQuery()->execute();
    }

    /**
     * Usuwa wszystkie przypisania użytkownika do zadań na podstawie jego identyfikatora.
     *
     * @param int $userId Identyfikator użytkownika, którego przypisania mają zostać usunięte.
     * @return int Zwraca liczbę usuniętych przypisań.
     */
    public function removeUserAssignmentsByUserId(int $userId): void
    {
        $qb = $this->createQueryBuilder('tu')
            ->delete()
            ->where('tu.user = :userId')
            ->setParameter('userId', $userId);

        $qb->getQuery()->execute();
    }

    /**
     * Pobiera projekty i zadania przypisane do danego użytkownika przez tabelę `tasks_users`.
     *
     * @param int $userId Identyfikator użytkownika, dla którego mają być pobrane projekty i zadania.
     * @return array Zwraca tablicę projektów z przypisanymi zadaniami.
     */
    public function getProjectsAndTasksByUser(int $userId): array
    {
        $qb = $this->createQueryBuilder('tu')
            ->select(
                'p.projectId AS project_id',
                'p.projectName AS project_name',
                't.taskId AS task_id',
                't.taskName AS task_name',
                't.taskDescription AS task_description',
                't.taskProgress AS task_progress'
            )
            ->innerJoin('tu.task', 't')
            ->innerJoin('t.project', 'p')
            ->where('tu.user = :userId')
            ->setParameter('userId', $userId);

        $results = $qb->getQuery()->getArrayResult();

        $groupedProjects = [];

        // Grupowanie wyników według projektów
        foreach ($results as $row) {
            $projectId = $row['project_id'];

            if (!isset($groupedProjects[$projectId])) {
                $groupedProjects[$projectId] = [
                    'project_id' => $row['project_id'],
                    'project_name' => $row['project_name'],
                    'task_count' => 0,
                    'remaining_task_count' => 0,
                    'inprogress_task_count' => 0,
                    'completed_task_count' => 0,
                    'tasks' => []
                ];
            }

            // Dodajemy zadanie do odpowiedniego projektu
            if ($row['task_id'] !== null) {
                $groupedProjects[$projectId]['tasks'][] = [
                    'task_id' => $row['task_id'],
                    'task_name' => $row['task_name'],
                    'task_progress' => $row['task_progress'],
                ];

                // Zwiększ licznik zadań
                $groupedProjects[$projectId]['task_count']++;

                // Zwiększ liczniki na podstawie postępu zadania
                if ($row['task_progress'] === 0) {
                    $groupedProjects[$projectId]['remaining_task_count']++;
                } elseif ($row['task_progress'] > 0 && $row['task_progress'] < 100) {
                    $groupedProjects[$projectId]['inprogress_task_count']++;
                } elseif ($row['task_progress'] === 100) {
                    $groupedProjects[$projectId]['completed_task_count']++;
                }
            }
        }

        return array_values($groupedProjects);
    }
}
