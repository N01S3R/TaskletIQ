<?php

namespace App\Repository;

use App\Entity\Task;
use App\Entity\User;
use App\Entity\Project;
use Doctrine\ORM\EntityRepository;

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
    public function getTaskById(int $taskId, int $userId)
    {
        return $this->createQueryBuilder('t')
            ->where('t.taskId = :taskId')
            ->andWhere('t.user = :user')
            ->setParameter('taskId', $taskId)
            ->setParameter('user', $userId)
            ->getQuery()
            ->getOneOrNullResult();
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

    /**
     * Zwraca pogrupowane zadania według projektu na podstawie identyfikatora postępu.
     *
     * @param int $userId Identyfikator użytkownika.
     * @param int $progressId Identyfikator postępu zadania.
     * @return array Pogrupowane zadania według projektów.
     */
    public function getGroupedTasksByProgress(int $userId, int $progressId): array
    {
        // Tworzymy zapytanie do pobrania zadań i powiązanych projektów
        $qb = $this->createQueryBuilder('t')
            ->innerJoin('t.project', 'p')
            ->where('t.user = :userId')
            ->andWhere('t.taskProgress = :progressId')
            ->setParameter('userId', $userId)
            ->setParameter('progressId', $progressId)
            ->select('p.projectName, t.taskName, t.taskDescription, p.projectId')
            ->getQuery();

        $tasks = $qb->getArrayResult();

        // Grupowanie zadań według nazwy projektu
        $groupedTasks = [];
        foreach ($tasks as $taskItem) {
            $projectName = $taskItem['projectName'];
            $projectId = $taskItem['projectId'];

            if (!isset($groupedTasks[$projectName])) {
                $groupedTasks[$projectName] = [];
            }

            $groupedTasks[$projectName][] = [
                'task_name' => $taskItem['taskName'],
                'task_description' => $taskItem['taskDescription'],
                'project_id' => $projectId
            ];
        }

        return $groupedTasks;
    }

    /**
     * Sprawdza, czy zadanie o podanej nazwie już istnieje dla użytkownika.
     *
     * @param string $title Tytuł zadania.
     * @param int $userId Identyfikator użytkownika.
     * @return bool Zwraca true, jeśli zadanie istnieje, w przeciwnym razie false.
     */
    public function taskExists($title, $userId): bool
    {
        $query = $this->createQueryBuilder('t')
            ->select('COUNT(t.taskId)')
            ->where('t.taskName = :title')
            ->andWhere('t.user = :userId')
            ->setParameter('title', $title)
            ->setParameter('userId', $userId)
            ->getQuery();

        return (bool) $query->getSingleScalarResult();
    }

    /**
     * Dodaje nowe zadanie do bazy danych.
     *
     * @param int $projectId Identyfikator projektu, do którego przypisane jest zadanie.
     * @param string $title Tytuł zadania.
     * @param string $description Opis zadania.
     * @param string $descriptionLong Długi opis zadania.
     * @param int $userId Identyfikator użytkownika, który stworzył zadanie.
     * @return Task Zwraca nowo utworzone zadanie.
     */
    public function addTask($projectId, $title, $description, $descriptionLong, $userId): ?Task
    {
        $task = new Task();
        $task->setTaskName($title);
        $task->setTaskDescription($description);
        $task->setTaskDescriptionLong($descriptionLong);
        $task->setTaskProgress(0);
        $task->setTaskStatus("Nowy");
        $task->setProject($this->getEntityManager()->getRepository(Project::class)->find($projectId));
        $task->setUser($this->getEntityManager()->getRepository(User::class)->find($userId));
        $task->setTaskCreatedAt(new \DateTime());

        // Persist and flush
        $this->_em->persist($task);
        $this->_em->flush();

        return $task; // Zwróć nowo utworzone zadanie
    }

    /**
     * Aktualizuje zadanie na podstawie podanego identyfikatora.
     *
     * @param int $taskId Identyfikator zadania do aktualizacji
     * @param string|null $title Nowy tytuł zadania
     * @param string|null $description Nowy opis zadania
     * @param string $descriptionLong Nowy długi opis zadania
     * @return bool Zwraca true, jeśli aktualizacja powiodła się, w przeciwnym razie false
     */
    public function setTask(int $taskId, ?string $title, ?string $description, string $descriptionLong): bool
    {
        // Znajdź zadanie po identyfikatorze
        $task = $this->find($taskId);

        if (!$task) {
            return false;
        }

        // Aktualizuj właściwości zadania
        $task->setTaskName($title);
        $task->setTaskDescription($description);
        $task->setTaskDescriptionLong($descriptionLong);

        // Zwróć true, gdy aktualizacja się powiodła
        return true;
    }

    /**
     * Usuwa zadanie na podstawie podanego identyfikatora.
     *
     * @param int $taskId Identyfikator zadania do usunięcia.
     * @param int $userId Identyfikator użytkownika.
     * @return bool Zwraca true, jeśli usunięcie się powiodło, w przeciwnym razie false.
     */
    public function deleteTask(int $taskId, int $userId): bool
    {
        // Znajdź zadanie po identyfikatorze
        $task = $this->getTaskById($taskId, $userId);

        if (!$task) {
            return false; // Zadanie nie zostało znalezione
        }

        // Usuń zadanie
        $this->_em->remove($task);
        $this->_em->flush();

        return true; // Usunięcie powiodło się
    }
}
