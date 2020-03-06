<?php

namespace App\Transformer;

use App\Entity\LeagueOfLegends\Player;
use App\Entity\LeagueOfLegends\Ranking;
use App\Entity\LeagueOfLegends\RiotAccount;
use App\Entity\LeagueOfLegends\SummonerName;
use App\Entity\Profile\Profile;
use App\Indexer\Indexer;
use App\Repository\LeagueOfLegends\RankingRepository;
use DateTime;
use Elastica\Document;

class ProfileTransformer extends AProfileTransformer
{
    public function fetchAndTransform($document, array $fields): ?Document
    {
        $profile = $this->entityManager->getRepository(Profile::class)->findOneBy(['uuid' => $document['uuid']]);

        if (!$profile instanceof Profile) {
            return null;
        }

        $document = $this->transform($profile, $fields);
        $this->entityManager->clear();

        return $document;
    }

    public function transform($profile, array $fields): ?Document
    {
        if (!$profile instanceof Profile) {
            return null;
        }

        $document = [
            'uuid' => $profile->getUuidAsString(),
            'name' => $profile->getName(),
            'slug' => $profile->getSlug(),
            'country' => $profile->getCountry(),
            'regions' => $this->buildRegions($profile),
            'social_media' => $this->buildSocialMedia($profile),
            'league_player' => $this->buildLeaguePlayer($profile),
            'staff' => $this->buildStaff($profile),
        ];

        return new Document($profile->getUuidAsString(), $document, Indexer::INDEX_TYPE_PROFILE, Indexer::INDEX_PROFILES);
    }

    private function buildStaff(Profile $profile)
    {
        if (!($staff = $profile->getStaff())) {
            return null;
        }

        return [
            'position' => $staff->getPosition(),
        ];
    }

    private function buildLeaguePlayer(Profile $profile)
    {
        if (!($player = $profile->getLeaguePlayer())) {
            return null;
        }

        return [
            'position' => $player->getPosition(),
            'score' => $player->getScore(),
            'accounts' => $this->buildAccounts($player),
        ];
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
                'encrypted_riot_id' => $account->getEncryptedRiotId(),
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