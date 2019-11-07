<?php

namespace App\Transformer;

use App\Entity\Core\Identity\Identity;
use App\Entity\Core\Team\Member;
use App\Entity\LeagueOfLegends\Player\Player;
use App\Entity\LeagueOfLegends\Player\Ranking;
use App\Entity\LeagueOfLegends\Player\RiotAccount;
use App\Entity\LeagueOfLegends\Player\SummonerName;
use App\Indexer\Indexer;
use DateTime;
use Elastica\Document;

class IdentityTransformer extends AIdentityTransformer
{
    public function fetchAndTransform($document, array $fields): ?Document
    {
        $identity = $this->entityManager->getRepository(Identity::class)->findOneBy(['uuid' => $document['uuid']]);

        if (!$identity instanceof Identity) {
            return null;
        }

        $document = $this->transform($identity, $fields);
        $this->entityManager->clear();

        return $document;
    }

    public function transform($identity, array $fields): ?Document
    {
        if (!$identity instanceof Identity) {
            return null;
        }

        $document = [
            'uuid' => $identity->getUuidAsString(),
            'name' => $identity->getName(),
            'slug' => $identity->getSlug(),
            'country' => $identity->getCountry(),
            'regions' => $this->buildRegions($identity),
            'social_media' => $this->buildSocialMedia($identity),
            'teams' => $this->buildTeams($identity),
            'previous_teams' => $this->buildPreviousTeams($identity),
        ];

        if ($identity instanceof Player) {
            $document = array_merge($document, [
                'position' => $identity->getPosition(),
                'score' => $identity->getScore(),
                'accounts' => $this->buildAccounts($identity),
                'rankings' => $this->buildPlayerRankings($identity),
            ]);
        }

        return new Document($identity->getUuidAsString(), $document, Indexer::INDEX_TYPE_IDENTITY, Indexer::INDEX_IDENTITIES);
    }

    private function buildAccounts(Player $identity): array
    {
        $accounts = [];

        foreach ($identity->getAccounts() as $account) {
            /* @var RiotAccount $account */
            array_push($accounts, [
                'uuid' => $account->getUuidAsString(),
                'profile_icon_id' => $account->getProfileIconId(),
                'smurf' => $account->isSmurf(),
                'riot_id' => $account->getRiotId(),
                'summoner_name' => $account->getCurrentSummonerName()->getName(),
                'summoner_names' => $this->buildSummonerNames($account),
                'rank' => $this->buildRanking($account->getCurrentRanking()),
                'peak' => $this->buildRanking($account->getBestRanking()),
                's9peak' => $this->buildRanking($account->getBestRanking(Ranking::SEASON_9_V2)),
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

    private function buildPlayerRankings(Player $player): array
    {
        $account = $player->getBestAccount();
        $playerRepository = $this->entityManager->getRepository(Player::class);
        $rankings = [];

        if ($account && $account->getCurrentRanking()->getScore()) {
            $rankings['global'] = $playerRepository->getPlayersRanked($player->getUuidAsString());
            $rankings['country'] = $playerRepository->getPlayersRanked($player->getUuidAsString(), null, $player->getCountry());
            $rankings['position'] = $playerRepository->getPlayersRanked($player->getUuidAsString(), $player->getPosition());
            $rankings['country_position'] = $playerRepository->getPlayersRanked($player->getUuidAsString(), $player->getPosition(), $player->getCountry());
        }

        return $rankings;
    }

    private function buildTeams(Identity $identity): array
    {
        $teams = [];

        foreach ($identity->getCurrentMemberships() as $member) {
            /** @var Member $member */
            $team = $member->getTeam();
            array_push($teams, [
                'uuid' => $team->getUuidAsString(),
                'tag' => $team->getTag(),
                'name' => $team->getName(),
                'slug' => $team->getSlug(),
                'logo' => $this->buildLogo($team->getLogo()),
                'join_date' => $member->getJoinDate()->format(DateTime::ISO8601),
                'leave_date' => $member->getLeaveDate() ? $member->getLeaveDate()->format(DateTime::ISO8601) : null,
                'current_members' => $this->buildMembers($team->getCurrentMemberships()),
                'previous_members' => $this->buildMembers($team->getSharedMemberships($member)),
            ]);
        }

        return $teams;
    }

    private function buildPreviousTeams(Identity $identity): array
    {
        $teams = [];

        foreach ($identity->getPreviousMemberships() as $member) {
            /** @var Member $member */
            $team = $member->getTeam();
            array_push($teams, [
                'uuid' => $team->getUuidAsString(),
                'tag' => $team->getTag(),
                'name' => $team->getName(),
                'slug' => $team->getSlug(),
                'logo' => $this->buildLogo($team->getLogo()),
                'join_date' => $member->getJoinDate()->format(DateTime::ISO8601),
                'leave_date' => $member->getLeaveDate() ? $member->getLeaveDate()->format(DateTime::ISO8601) : null,
                'members' => $this->buildMembers($team->getMembersBetweenDates($member->getJoinDate(), $member->getLeaveDate())),
            ]);
        }

        return $teams;
    }
}