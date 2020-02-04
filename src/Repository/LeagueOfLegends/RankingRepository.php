<?php

namespace App\Repository\LeagueOfLegends;

use App\Entity\LeagueOfLegends\Ranking;
use App\Entity\LeagueOfLegends\RiotAccount;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class RankingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ranking::class);
    }

    public function getBestForAccount(RiotAccount $account, string $season = null): ?Ranking
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

    public function getForAccount(RiotAccount $account, int $months = null, string $season = null)
    {
        $queryBuilder = $this->createQueryBuilder('ranking')
            ->addSelect('DATE(ranking.createdAt) as HIDDEN date')
            ->where('ranking.owner = :account')
            ->groupBy('date')
            ->orderBy('ranking.createdAt', 'desc')
            ->setParameter('account', $account);

        if ($months) {
            $today = new DateTime('+1 day');
            $previous = new DateTime('-'.$months.' month');
            $queryBuilder
                ->andWhere('ranking.createdAt BETWEEN :date AND :today')
                ->setParameter('date', $previous->format('Y-m-d'))
                ->setParameter('today', $today->format('Y-m-d'));
        }

        if ($season) {
            if (Ranking::SEASON_9_V2 === $season || Ranking::SEASON_9 === $season) {
                $queryBuilder->andWhere('ranking.season = \'season_9\' or ranking.season = \'season_9_v2\'');
            } else {
                $queryBuilder->andWhere('ranking.season = :season')->setParameter('season', $season);
            }
        }

        return $queryBuilder->getQuery()->getResult();
    }

    public function getLatestForAccount(RiotAccount $account, string $season = null)
    {
        $queryBuilder = $this->createQueryBuilder('ranking')
            ->where('ranking.owner = :account')
            ->orderBy('ranking.createdAt', 'desc')
            ->setParameter('account', $account)
            ->setMaxResults(1);

        if ($season) {
            $queryBuilder->andWhere('ranking.season = :season')->setParameter('season', $season);
        }

        $result = $queryBuilder->getQuery()->getResult();

        if (isset($result[0])) {
            return $result[0];
        }

        return null;
    }
}
