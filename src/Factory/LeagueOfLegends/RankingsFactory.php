<?php

namespace App\Factory\LeagueOfLegends;

use App\Entity\LeagueOfLegends\Player\Ranking;
use App\Manager\LeagueOfLegends\Player\RankingManager;
use RiotAPI\LeagueAPI\Objects\LeagueEntryDto;

class RankingsFactory
{
    public static function createFromLeague(LeagueEntryDto $league): Ranking
    {
        $ranking = new Ranking();

        $ranking->setQueueType($league->queueType)
            ->setTier(RankingManager::tierToDatabase($league->tier))
            ->setRank(RankingManager::rankToDatabase($league->rank))
            ->setWins($league->wins)
            ->setLosses($league->losses)
            ->setLeaguePoints($league->leaguePoints)
            ->setScore(RankingManager::calculateScore($ranking))
            ->setSeason(Ranking::PRE_SEASON_10);

        return $ranking;
    }

    public static function createEmptyRanking(): Ranking
    {
        $ranking = new Ranking();

        $ranking->setQueueType(Ranking::QUEUE_TYPE_SOLO)
            ->setTier(Ranking::TIER_UNRANKED)
            ->setWins(0)
            ->setLosses(0)
            ->setLeaguePoints(0)
            ->setScore(0)
            ->setSeason(Ranking::PRE_SEASON_10);

        return $ranking;
    }

    public static function createArrayFromLeague(LeagueEntryDto $league): array
    {
        return [
            'queueType' => $league->queueType,
            'tier' => RankingManager::tierToDatabase($league->tier),
            'rank' => RankingManager::rankToDatabase($league->rank),
            'wins' => $league->wins,
            'losses' => $league->losses,
            'leaguePoints' => $league->leaguePoints,
            'season' => Ranking::PRE_SEASON_10,
            'miniSeries' => $league->miniSeries ? [
                'wins' => $league->miniSeries->wins,
                'losses' => $league->miniSeries->losses,
                'target' => $league->miniSeries->target,
                'progress' => $league->miniSeries->progress,
            ] : null,
        ];
    }

    public static function createEmptyArray(): array
    {
        return [
            'queueType' => Ranking::QUEUE_TYPE_SOLO,
            'tier' => Ranking::TIER_UNRANKED,
            'rank' => 0,
            'wins' => 0,
            'losses' => 0,
            'leaguePoints' => 0,
            'season' => Ranking::PRE_SEASON_10,
        ];
    }
}
