<?php

namespace App\Repository;

use App\Entity\Task;
use App\Entity\User;
use App\Entity\Project;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityNotFoundException;

class TaskRepository extends EntityRepository
{
    /**
     * Zwraca wszystkie zadania przypisane do zalogowanego użytkownika.
     *
     * @param int $userId Identyfikator zalogowanego użytkownika.
     * @return Task[] Tablica z zadaniami użytkownika.
     */
    public function getAllTasksByUserId(int $userId): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.userId = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getResult();
    }

    /**
     * Znajduje zadanie po ID oraz użytkowniku.
     *
     * @param int $taskId
     * @param int $userId
     * @return Task|null
     */
    public function getTaskById(int $taskId, int $userId): ?Task
    {
        return $this->createQueryBuilder('t')
            ->where('t.taskId = :taskId')
            ->innerJoin('t.users', 'u')
            ->andWhere('u.userId = :userId')
            ->setParameter('taskId', $taskId)
            ->setParameter('userId', $userId)
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
     * Zwraca zadania przypisane do użytkownika wraz z informacjami o powiązanych projektach.
     *
     * @param int $userId Identyfikator użytkownika, dla którego mają być zwrócone zadania.
     * @return array Tablica z zadaniami przypisanymi do użytkownika, zawierająca informacje o projektach.
     */
    public function getTasksByUserIdWithProjects(int $userId): array
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.users', 'u')
            ->leftJoin('t.project', 'p')
            ->where('u.userId = :userId')
            ->setParameter('userId', $userId)
            ->addSelect('p')
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * Zwraca zadania o określonym poziomie postępu dla danego użytkownika.
     *
     * @param int $userId
     * @param int $progress
     * @return array
     */
    public function getTasksByProgress(int $userId, int $progress): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.taskProgress = :progress')
            ->andWhere('t.userId = :userId')
            ->setParameter('progress', $progress)
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getArrayResult();
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
        return $this->createQueryBuilder('t')
            ->select('t, p')
            ->join('t.project', 'p')
            ->where('t.userId = :userId')
            ->andWhere('t.taskProgress = :taskProgress')
            ->setParameter('userId', $userId)
            ->setParameter('taskProgress', $progressId)
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * Sprawdza, czy zadanie o podanej nazwie już istnieje dla użytkownika.
     *
     * @param string $title Tytuł zadania.
     * @param int $userId Identyfikator użytkownika.
     * @return bool Zwraca true, jeśli zadanie istnieje, w przeciwnym razie false.
     */
    public function taskExists(string $title, int $userId): bool
    {
        $query = $this->createQueryBuilder('t')
            ->select('COUNT(t.taskId)')
            ->where('t.taskName = :title')
            ->innerJoin('t.users', 'u')
            ->andWhere('u.userId = :userId')
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
     * @param string|null $descriptionLong Długi opis zadania.
     * @param int $userId Identyfikator użytkownika, który stworzył zadanie.
     * @return Task Zwraca nowo utworzone zadanie.
     * @throws EntityNotFoundException
     */
    public function addTask(int $projectId, string $title, string $description, ?string $descriptionLong, int $userId): ?Task
    {
        $project = $this->getEntityManager()->getRepository(Project::class)->find($projectId);
        $user = $this->getEntityManager()->getRepository(User::class)->find($userId);

        if (!$project || !$user) {
            throw new EntityNotFoundException("Projekt lub użytkownik nie znaleziony.");
        }

        $task = new Task();
        $task->setTaskName($title);
        $task->setTaskDescription($description);
        $task->setTaskDescriptionLong($descriptionLong ?? '');
        $task->setTaskProgress(0);
        $task->setTaskStatus("Nowy");
        $task->setProject($project);
        $task->setUser($user);
        $task->setTaskCreatedAt(new \DateTime());

        $this->_em->persist($task);
        $this->_em->flush();

        return $task;
    }

    /**
     * Aktualizuje zadanie na podstawie podanego identyfikatora.
     *
     * @param int $taskId Identyfikator zadania do aktualizacji
     * @param string|null $title Nowy tytuł zadania
     * @param string|null $description Nowy opis zadania
     * @param string|null $descriptionLong Nowy długi opis zadania
     * @return bool Zwraca true, jeśli aktualizacja powiodła się, w przeciwnym razie false
     */
    public function setTask(int $taskId, ?string $title, ?string $description, ?string $descriptionLong): bool
    {
        $task = $this->find($taskId);

        if (!$task) {
            return false;
        }

        if ($title !== null) {
            $task->setTaskName($title);
        }
        if ($description !== null) {
            $task->setTaskDescription($description);
        }
        if ($descriptionLong !== null) {
            $task->setTaskDescriptionLong($descriptionLong);
        }

        // Zapisanie zmian
        $this->_em->flush();

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
        $task = $this->getTaskById($taskId, $userId);

        if (!$task) {
            return false;
        }

        $this->_em->remove($task);
        $this->_em->flush();

        return true;
    }
}
