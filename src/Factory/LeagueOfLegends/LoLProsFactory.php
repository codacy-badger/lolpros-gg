<?php

namespace App\Factory\LeagueOfLegends;

use App\Entity\LeagueOfLegends\Ranking;
use App\Entity\LeagueOfLegends\RiotAccount;
use DateTime;

class LoLProsFactory
{
    public static function createArrayFromRiotAccount(RiotAccount $riotAccount): array
    {
        $player = $riotAccount->getLeaguePlayer();
        $profile = $player->getProfile();
        $season = $riotAccount->getLatestRanking(Ranking::SEASON_10);
        $team = $profile->getCurrentTeam();

        $lolpros = [
            'uuid' => $player->getUuidAsString(),
            'name' => $profile->getName(),
            'slug' => $profile->getSlug(),
            'country' => $profile->getCountry(),
            'position' => $player->getPosition(),
            'team' => $team ? [
                'team' => $team->getUuidAsString(),
                'name' => $team->getName(),
                'slug' => $team->getSlug(),
                'tag' => $team->getTag(),
                'logo' => $team->getLogo() ? [
                    'public_id' => $team->getLogo()->getPublicId(),
                    'version' => $team->getLogo()->getVersion(),
                    'url' => $team->getLogo()->getUrl(),
                ] : null,
            ] : null,
        ];

        if ($season) {
            $lolpros['s9peak'] = [
                'score' => $season->getScore(),
                'tier' => $season->getTier(),
                'rank' => $season->getRank(),
                'league_points' => $season->getLeaguePoints(),
                'wins' => $season->getWins(),
                'losses' => $season->getLosses(),
                'created_at' => $season->getCreatedAt()->format(DateTime::ISO8601),
            ];
        }

        return $lolpros;
    }
}
