<?php

namespace App\Factory\LeagueOfLegends;

use App\Entity\LeagueOfLegends\Player\Ranking;
use App\Entity\LeagueOfLegends\Player\RiotAccount;
use DateTime;

class LoLProsFactory
{
    public static function createArrayFromRiotAccount(RiotAccount $riotAccount): array
    {
        $player = $riotAccount->getPlayer();
        $season = $riotAccount->getLatestRanking(Ranking::SEASON_9);
        $team = $player->getCurrentTeam();

        $lolpros = [
            'uuid' => $player->getUuidAsString(),
            'name' => $player->getName(),
            'slug' => $player->getSlug(),
            'country' => $player->getCountry(),
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
