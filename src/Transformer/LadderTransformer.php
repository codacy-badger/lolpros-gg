<?php

namespace App\Transformer;

use App\Entity\LeagueOfLegends\Player;
use App\Entity\LeagueOfLegends\RiotAccount;
use App\Indexer\Indexer;
use DateTime;
use Doctrine\Common\Collections\Collection;
use Elastica\Document;

class LadderTransformer extends AProfileTransformer
{
    public function fetchAndTransform($document, array $fields): ?Document
    {
        $player = $this->entityManager->getRepository(Player::class)->findOneBy(['uuid' => $document['uuid']]);

        /** @var Player $player */
        if (!$player instanceof Player) {
            return null;
        }

        $document = $this->transform($player, $fields);
        $this->entityManager->clear();

        return $document;
    }

    public function transform($player, array $fields): ?Document
    {
        /** @var Player $player */
        if (!$player instanceof Player) {
            return null;
        }

        $profile = $player->getProfile();
        $accounts = $player->getAccounts();

        $document = [
            'uuid' => $profile->getUuidAsString(),
            'name' => $profile->getName(),
            'slug' => $profile->getSlug(),
            'country' => $profile->getCountry(),
            'regions' => $this->buildRegions($profile),
            'position' => $player->getPosition(),
            'score' => $player->getScore(),
            'account' => $accounts->count() ? $this->buildAccount($player->getBestAccount()) : null,
            'peak' => $this->buildPeak($player),
            'total_games' => $this->getTotalGames($accounts),
            'team' => $this->buildTeam($profile),
        ];

        return new Document($player->getUuidAsString(), $document, Indexer::INDEX_TYPE_LADDER, Indexer::INDEX_LADDER);
    }

    private function buildAccount(RiotAccount $account): array
    {
        $rank = $account->getCurrentRanking();

        return [
            'uuid' => $account->getUuidAsString(),
            'riot_id' => $account->getRiotId(),
            'account_id' => $account->getAccountId(),
            'profile_icon_id' => $account->getProfileIconId(),
            'summoner_name' => $account->getSummonerName(),
            'rank' => $rank->getRank(),
            'tier' => $rank->getTier(),
            'league_points' => $rank->getLeaguePoints(),
            'games' => $rank->getTotalGames(),
            'winrate' => round($rank->getWinrate(), 1),
        ];
    }

    private function buildPeak(Player $player): ?array
    {
        if (!count($accounts = $player->getAccounts())) {
            $this->logger->notice(sprintf('[LadderTransformer] No accounts found for %s (%s)', $player->getProfile()->getName(), $player->getUuidAsString()));

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
            $this->logger->error(sprintf('[LadderTransformer] No best account found for %s (%s)', $player->getName(), $player->getUuidAsString()));

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
            $games += $account->getCurrentRanking()->getTotalGames();
        }

        return $games;
    }
}
