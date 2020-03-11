<?php

namespace App\Manager\LeagueOfLegends\Player;

use App\Entity\LeagueOfLegends\Player\Ranking;
use App\Entity\LeagueOfLegends\Player\RiotAccount;
use App\Event\LeagueOfLegends\Player\RankingEvent;
use App\Factory\LeagueOfLegends\RankingsFactory;
use App\Manager\DefaultManager;
use App\Manager\LeagueOfLegends\Riot\RiotLeagueManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class RankingManager extends DefaultManager
{
    /**
     * @var RiotLeagueManager
     */
    private $riotLeagueManager;

    /**
     * RankingsManager constructor.
     */
    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger, EventDispatcherInterface $eventDispatcher, RiotLeagueManager $riotLeagueManager)
    {
        parent::__construct($entityManager, $logger, $eventDispatcher);
        $this->riotLeagueManager = $riotLeagueManager;
    }

    private function updateBestWithNewRanking(Ranking $ranking)
    {
        /** @var Ranking $best */
        $best = $this->entityManager->getRepository(Ranking::class)->getBestForAccount($ranking->getOwner(), $ranking->getSeason());

        if (!$best) {
            $ranking->setBest(true);
        }

        if ($best && $ranking->getScore() > $best->getScore()) {
            $ranking->setBest(true);
            $best->setBest(false);
        }

        $ranking->getOwner()->setScore($ranking->getScore());
    }

    public function updateRanking(RiotAccount $riotAccount)
    {
        $soloQ = $this->riotLeagueManager->getSoloQForId($riotAccount->getEncryptedRiotId());
        $ranking = $soloQ ? RankingsFactory::createFromLeague($soloQ) : RankingsFactory::createEmptyRanking();
        $ranking->setOwner($riotAccount);
        $ranking->setSeason(Ranking::SEASON_10);
        $riotAccount->addRanking($ranking);

        $this->entityManager->persist($ranking);
        $this->updateBestWithNewRanking($ranking);
        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new RankingEvent($ranking), RankingEvent::CREATED);
    }

    public function getRankingsForRiotAccount(RiotAccount $riotAccount, $months, $season)
    {
        $season = Ranking::SEASON_9 === $season ? Ranking::SEASON_9_V2 : $season;

        return $this->entityManager->getRepository(Ranking::class)->getForAccount($riotAccount, $months, $season);
    }

    public function getForRiotAccount(RiotAccount $riotAccount): ?Ranking
    {
        try {
            $soloQ = $this->riotLeagueManager->getSoloQForId($riotAccount->getEncryptedRiotId());
            $ranking = $soloQ ? RankingsFactory::createFromLeague($soloQ) : RankingsFactory::createEmptyRanking();
            $ranking->setBest(true);

            return $ranking;
        } catch (Exception $e) {
            $this->logger->error('[RankingsManager] Could not get ranking for account {uuid} because of {reason}', [
                'uuid' => $riotAccount->getUuid()->toString(),
                'reason' => $e->getMessage(),
            ]);
        }

        return null;
    }

    public static function tierToDatabase(string $tier): string
    {
        switch ($tier) {
            case 'CHALLENGER':
                return '00_challenger';
            case 'GRANDMASTER':
                return '10_grandmaster';
            case 'MASTER':
                return '20_master';
            case 'DIAMOND':
                return '30_diamond';
            case 'PLATINUM':
                return '40_platinum';
            case 'GOLD':
                return '50_gold';
            case 'SILVER':
                return '60_silver';
            case 'BRONZE':
                return '70_bronze';
            case 'IRON':
                return '80_iron';
            case 'UNRANKED':
                return '90_unranked';
            default:
                return null;
        }
    }

    public static function tierToRiot(string $tier): ?string
    {
        switch ($tier) {
            case '00_challenger':
                return 'CHALLENGER';
            case '10_grandmaster':
                return 'GRANDMASTER';
            case '20_master':
                return 'MASTER';
            case '30_diamond':
                return 'DIAMOND';
            case '40_platinum':
                return 'PLATINUM';
            case '50_gold':
                return 'GOLD';
            case '60_silver':
                return 'SILVER';
            case '70_bronze':
                return 'BRONZE';
            case '80_iron':
                return 'IRON';
            case '90_unranked':
                return 'UNRANKED';
            default:
                return null;
        }
    }

    public static function rankToDatabase(string $rank): ?int
    {
        switch ($rank) {
            case 'I':
                return 1;
            case 'II':
                return 2;
            case 'III':
                return 3;
            case 'IV':
                return 4;
            case 'V':
                return 5;
            default:
                return null;
        }
    }

    public static function rankToRiot(int $rank): ?string
    {
        switch ($rank) {
            case 1:
                return 'I';
            case 2:
                return 'II';
            case 3:
                return 'III';
            case 4:
                return 'IV';
            case 5:
                return 'V';
            default:
                return null;
        }
    }

    public static function calculateScore(Ranking $ranking): int
    {
        switch ($ranking->getTier()) {
            case Ranking::TIER_CHALLENGER:
            case Ranking::TIER_GRANDMASTER:
            case Ranking::TIER_MASTER:
                $score = 3006;
                break;
            case Ranking::TIER_DIAMOND:
                $score = 2505;
                break;
            case Ranking::TIER_PLATINUM:
                $score = 2004;
                break;
            case Ranking::TIER_GOLD:
                $score = 1503;
                break;
            case Ranking::TIER_SILVER:
                $score = 1002;
                break;
            case Ranking::TIER_BRONZE:
                $score = 501;
                break;
            case Ranking::TIER_IRON:
            case Ranking::TIER_UNRANKED:
            default:
                $score = 0;
                break;
        }

        $leaguePoints = 100 === $ranking->getLeaguePoints() ? 99.5 : $ranking->getLeaguePoints();

        return in_array($ranking->getTier(), [Ranking::TIER_CHALLENGER, Ranking::TIER_GRANDMASTER, Ranking::TIER_MASTER, Ranking::TIER_UNRANKED])
            ? $score + $leaguePoints
            : $score + (5 - $ranking->getRank()) * 100 + $leaguePoints;
    }
}
