<?php

namespace App\Transformer;

use App\Entity\LeagueOfLegends\LeaguePlayer;
use App\Entity\LeagueOfLegends\Ranking;
use App\Entity\LeagueOfLegends\RiotAccount;
use App\Entity\LeagueOfLegends\SummonerName;
use App\Indexer\Indexer;
use App\Repository\LeagueOfLegends\RankingRepository;
use DateTime;
use Elastica\Document;

class PlayerTransformer extends APlayerTransformer
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

        $document = [
            'uuid' => $player->getUuidAsString(),
            'name' => $player->getName(),
            'slug' => $player->getSlug(),
            'country' => $player->getCountry(),
            'regions' => $this->buildRegions($player),
            'position' => $player->getPosition(),
            'score' => $player->getScore(),
            'accounts' => $this->buildAccounts($player),
            'social_media' => $this->buildSocialMedia($player),
        ];

        return new Document($player->getUuidAsString(), $document, Indexer::INDEX_TYPE_PLAYER, Indexer::INDEX_PLAYERS);
    }

    private function buildAccounts(Player $player): array
    {
        $accounts = [];
        /** @var RankingRepository $rankingRepository */
        $rankingRepository = $this->entityManager->getRepository(Ranking::class);

        foreach ($player->getAccounts() as $account) {
            /* @var RiotAccount $account */
            array_push($accounts, [
                'uuid' => $account->getUuidAsString(),
                'profile_icon_id' => $account->getProfileIconId(),
                'riot_id' => $account->getRiotId(),
                'summoner_name' => $account->getSummonerName(),
                'summoner_names' => $this->buildSummonerNames($account),
                'rank' => $this->buildRanking($rankingRepository->getLatestForAccount($account, Ranking::SEASON_10)),
                'peak' => $this->buildRanking($rankingRepository->getBestForAccount($account, Ranking::SEASON_10)),
                'season9' => [
                    'end' => $this->buildRanking($rankingRepository->getLatestForAccount($account, Ranking::SEASON_9_V2)),
                    'peak' => $this->buildRanking($rankingRepository->getBestForAccount($account, Ranking::SEASON_9_V2)),
                ],
            ]);
        }

        return $accounts;
    }

    private function buildRanking(?Ranking $ranking): ?array
    {
        if (!$ranking) {
            return null;
        }

        return [
            'score' => $ranking->getScore(),
            'tier' => $ranking->getTier(),
            'rank' => $ranking->getRank(),
            'league_points' => $ranking->getLeaguePoints(),
            'wins' => $ranking->getWins(),
            'losses' => $ranking->getLosses(),
            'created_at' => $ranking->getCreatedAt()->format(DateTime::ISO8601),
        ];
    }

    private function buildSummonerNames(RiotAccount $account): array
    {
        $names = [];

        foreach ($account->getSummonerNames() as $name) {
            /* @var SummonerName $name */
            array_push($names, [
                'name' => $name->getName(),
                'created_at' => $name->getCreatedAt()->format(DateTime::ISO8601),
            ]);
        }

        return $names;
    }
}
