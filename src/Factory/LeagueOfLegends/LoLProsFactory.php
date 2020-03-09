<?php

namespace App\Factory\LeagueOfLegends;

class LoLProsFactory
{
    public static function createArrayFromRiotAccount($profile): array
    {
        $team = array_shift($profile['teams']);

        return [
            'uuid' => $profile['uuid'],
            'name' => $profile['name'],
            'slug' => $profile['slug'],
            'country' => $profile['country'],
            'position' => $profile['league_player']['position'],
            'team' => $team ? [
                'team' => $team['uuid'],
                'name' => $team['name'],
                'slug' => $team['slug'],
                'tag' => $team['tag'],
                'logo' => $team['logo'],
            ] : null,
        ];
    }
}
