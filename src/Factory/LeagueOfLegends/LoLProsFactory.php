<?php

namespace App\Factory\LeagueOfLegends;

use App\Entity\LeagueOfLegends\Player\RiotAccount;
use DateTime;

class LoLProsFactory
{
    public static function createArrayFromRiotAccount(RiotAccount $riotAccount): array
    {
        $player = $riotAccount->getPlayer();
        $team = $player->getCurrentTeam();

        return [
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
    }
}
