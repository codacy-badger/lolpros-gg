<?php

namespace App\Transformer;

use App\Entity\Region\Region;
use App\Entity\LeagueOfLegends\LeaguePlayer;

abstract class APlayerTransformer extends DefaultTransformer
{
    protected function buildTeam(Player $player): ?array
    {
        if (!($team = $player->getCurrentTeam())) {
            return null;
        }

        return [
            'uuid' => $team->getUuidAsString(),
            'tag' => $team->getTag(),
            'name' => $team->getName(),
            'slug' => $team->getSlug(),
            'logo' => $this->buildLogo($team->getLogo()),
        ];
    }

    protected function buildRegions(Player $player): array
    {
        $regions = [];

        foreach ($player->getRegions() as $region) {
            /* @var Region $region */
            array_push($regions, [
                'uuid' => $region->getUuidAsString(),
                'name' => $region->getName(),
                'slug' => $region->getSlug(),
                'shorthand' => $region->getShorthand(),
                'logo' => $this->buildLogo($region->getLogo()),
            ]);
        }

        return $regions;
    }

    protected function buildSocialMedia(Player $player): array
    {
        $socialMedia = $player->getSocialMedia();

        return [
            'twitter' => $socialMedia->getTwitter(),
            'twitch' => $socialMedia->getTwitch(),
            'discord' => $socialMedia->getDiscord(),
            'facebook' => $socialMedia->getFacebook(),
            'leaguepedia' => $socialMedia->getLeaguepedia(),
        ];
    }
}
