<?php

namespace App\Repository;

use App\Entity\Profile\Profile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;

class ProfileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Profile::class);
    }

    public function getPaginated(int $page = 1, int $pageSize = 20): Paginator
    {
        return new Paginator(
            $this->createQueryBuilder('players')
                ->addSelect('memberships')
                ->leftJoin('players.memberships', 'memberships')
                ->orderBy('players.name', 'ASC')
                ->setFirstResult($pageSize * ($page - 1))
                ->setMaxResults($pageSize)
                ->getQuery()
        );
    }

    public function searchPaginated(string $query, int $page = 1, int $pageSize = 20): Paginator
    {
        return new Paginator(
            $this->createQueryBuilder('players')
                ->leftJoin('players.memberships', 'memberships')
                ->andWhere('players.name LIKE :name')
                ->setParameter('name', '%'.$query.'%')
                ->setFirstResult($pageSize * ($page - 1))
                ->setMaxResults($pageSize)
                ->getQuery()
        );
    }

    public function getCountries()
    {
        $query = $this->getEntityManager()->getConnection()->prepare('SELECT country FROM profile__profile GROUP BY country');
        $query->execute();

        $array = $query->fetchAll();

        $flatten = [];
        array_walk_recursive($array, function ($value) use (&$flatten) {
            $flatten[] = $value;
        });

        return $flatten;
    }
}
