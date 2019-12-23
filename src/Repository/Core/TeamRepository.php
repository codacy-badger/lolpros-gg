<?php

namespace App\Repository\Core;

use App\Entity\Core\Team\Team;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Common\Persistence\ManagerRegistry;

class TeamRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Team::class);
    }

    public function getTeamsUuids(): array
    {
        $query = $this->getEntityManager()->getConnection()->prepare('SELECT uuid FROM team__team');
        $query->execute();

        $array = $query->fetchAll();

        $flatten = [];
        array_walk_recursive($array, function ($value) use (&$flatten) {
            $flatten[] = $value;
        });

        return $flatten;
    }

    public function getPaginated(int $page = 1, int $pageSize = 20): Paginator
    {
        return new Paginator(
            $this->createQueryBuilder('team')
                ->orderBy('team.name', 'ASC')
                ->setFirstResult($pageSize * ($page - 1))
                ->setMaxResults($pageSize)
                ->getQuery()
        );
    }

    public function searchPaginated(string $query, int $page = 1, int $pageSize = 20): Paginator
    {
        return new Paginator(
            $this->createQueryBuilder('team')
                ->andWhere('team.name LIKE :name')
                ->setParameter('name', '%'.$query.'%')
                ->setFirstResult($pageSize * ($page - 1))
                ->setMaxResults($pageSize)
                ->getQuery()
        );
    }
}
