<?php

namespace App\Transformer;

use App\Entity\LeagueOfLegends\Player\Player;
use App\Entity\LeagueOfLegends\Player\RiotAccount;
use App\Indexer\Indexer;
use DateTime;
use Doctrine\Common\Collections\Collection;
use Elastica\Document;

class LadderTransformer extends APlayerTransformer
{
    public function fetchAndTransform($document, array $fields): ?Document
    {
        $player = $this->entityManager->getRepository(Player::class)->findOneBy(['uuid' => $document['uuid']]);

        if (!$player instanceof Player) {
            return null;
        }

        $document = $this->transform($player, $fields);
        $this->entityManager->clear();

        return $document;
    }

    public function transform($player, array $fields): ?Document
    {
        if (!$player instanceof Player) {
            return null;
        }

        $accounts = $player->getAccounts();

        $document = [
            'uuid' => $player->getUuidAsString(),
            'name' => $player->getName(),
            'slug' => $player->getSlug(),
            'country' => $player->getCountry(),
            'regions' => $this->buildRegions($player),
            'position' => $player->getPosition(),
            'score' => $player->getScore(),
            'account' => $accounts->count() ? $this->buildAccount($player->getBestAccount()) : null,
            'peak' => $this->buildPeak($player),
            'total_games' => $this->getTotalGames($accounts),
            'team' => $this->buildTeam($player),
        ];

        return new Document($player->getUuidAsString(), $document, Indexer::INDEX_TYPE_LADDER, Indexer::INDEX_LADDER);
    }

    private function buildAccount(RiotAccount $account): array
    {
        $rank = $account->getCurrentRanking();
        $totalGames = $rank->getWins() + $rank->getLosses();

        return [
            'uuid' => $account->getUuidAsString(),
            'riot_id' => $account->getRiotId(),
            'account_id' => $account->getAccountId(),
            'profile_icon_id' => $account->getProfileIconId(),
            'summoner_name' => $account->getCurrentSummonerName()->getName(),
            'rank' => $rank->getRank(),
            'tier' => $rank->getTier(),
            'league_points' => $rank->getLeaguePoints(),
            'games' => $totalGames,
            'winrate' => $totalGames ? round($rank->getWins() / $totalGames * 100, 1) : 0,
        ];
    }

    private function buildPeak(Player $player): ?array
    {
        if (!count($accounts = $player->getAccounts())) {
            $this->logger->critical(sprintf('[LadderTransformer] No accounts found for %s (%s)', $player->getName(), $player->getUuidAsString()));

            return null;
        }

        $peak = null;

        /** @var RiotAccount $account */
        foreach ($accounts as $account) {
            if (!($bestRanking = $account->getBestRanking())) {
                continue;
            }
            $peak = $peak instanceof RiotAccount && $peak->getScore() >= $bestRanking->getScore() ? $peak : $bestRanking;
        }

        if (!$peak) {
            $this->logger->critical(sprintf('[LadderTransformer] No best account found for %s (%s)', $player->getName(), $player->getUuidAsString()));

            return null;
        }

        return [
            'rank' => $peak->getRank(),
            'tier' => $peak->getTier(),
            'league_points' => $peak->getLeaguePoints(),
            'score' => $peak->getScore(),
            'date' => $peak->getCreatedAt()->format(DateTime::ISO8601),
        ];
    }

    private function getTotalGames(Collection $accounts): int
    {
        $games = 0;

        foreach ($accounts as $account) {
            /* @var RiotAccount $account */
            $games += $account->getCurrentRanking()->getWins() + $account->getCurrentRanking()->getLosses();
        }

        return $games;
    }
}
