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
    public function getProjectById(int $projectId): ?Project
    {
        return $this->find($projectId);
    }

    /**
     * Zwraca projekty powiązane z użytkownikiem o danym ID.
     *
     * @param int $userId
     * @return Project[]
     */
    public function getProjectsByUserId(int $userId): array
    {
        return $this->findBy(['userId' => $userId]);
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
}
