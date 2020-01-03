<?php

namespace App\Repository\LeagueOfLegends;

use App\Entity\LeagueOfLegends\RiotAccount;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;

class RiotAccountRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RiotAccount::class);
    }

    public function getPaginated(int $page = 1, int $pageSize = 20): Paginator
    {
        return new Paginator(
            $this->createQueryBuilder('riotAccount')
                ->leftJoin('riotAccount.summonerNames', 'summonerNames')
                ->orderBy('summonerNames.name', 'ASC')
                ->setFirstResult($pageSize * ($page - 1))
                ->setMaxResults($pageSize)
                ->getQuery()
        );
    }

    public function searchPaginated(string $query, int $page = 1, int $pageSize = 20): Paginator
    {
        return new Paginator(
            $this->createQueryBuilder('riotAccount')
                ->leftJoin('riotAccount.summonerNames', 'summonerNames')
                ->andWhere('summonerNames.name LIKE :name')
                ->andWhere('summonerNames.current = 1')
                ->setParameter('name', '%'.$query.'%')
                ->setFirstResult($pageSize * ($page - 1))
                ->setMaxResults($pageSize)
                ->getQuery())
            ;
    }
}
