<?php

namespace App\Repository\LeagueOfLegends;

use App\Entity\LeagueOfLegends\Player\Ranking;
use App\Entity\LeagueOfLegends\Player\RiotAccount;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class RankingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ranking::class);
    }

    public function getBestForAccount(RiotAccount $account, $season = null): ?Ranking
    {
        $queryBuilder = $this->createQueryBuilder('ranking')
            ->where('ranking.owner = :account')
            ->andWhere('ranking.best = 1')
            ->setParameter('account', $account)
            ->setMaxResults(1);

        if ($season) {
            $queryBuilder->andWhere('ranking.season = :season')
                ->setParameter('season', $season);
        }

        $result = $queryBuilder->getQuery()->getResult();

        if (isset($result[0])) {
            return $result[0];
        }

        return null;
    }

    public function getXForAccount(RiotAccount $account, $months)
    {
        $today = new DateTime('+1 day');
        $previous = new DateTime('-'.$months.' month');

        return $this->createQueryBuilder('ranking')
            ->where('ranking.owner = :account')
            ->orderBy('ranking.createdAt', 'desc')
            ->andWhere('ranking.createdAt BETWEEN :date AND :today')
            ->setParameter('account', $account)
            ->setParameter('date', $previous->format('Y-m-d'))
            ->setParameter('today', $today->format('Y-m-d'))
            ->getQuery()
            ->getResult();
    }
}
