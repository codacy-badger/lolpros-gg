<?php

namespace App\Repository\LeagueOfLegends;

use App\Entity\LeagueOfLegends\Player\SummonerName;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class SummonerNameRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SummonerName::class);
    }

    public function getLatestXChanges(?int $max = 15)
    {
        return $this->createQueryBuilder('summonerName')
            ->where('summonerName.previous IS NOT NULL')
            ->orderBy('summonerName.createdAt', 'desc')
            ->setMaxResults($max)
            ->getQuery()
            ->getResult();
    }
}
