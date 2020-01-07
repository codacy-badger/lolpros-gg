<?php

namespace App\Transformer;

use App\Entity\Profile\Profile;
use App\Entity\Region\Region;

abstract class AProfileTransformer extends DefaultTransformer
{
    protected function buildTeam(Profile $profile): ?array
    {
        if (!($team = $profile->getCurrentTeam())) {
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

    protected function buildRegions(Profile $profile): array
    {
        $regions = [];

        foreach ($profile->getRegions() as $region) {
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

    protected function buildSocialMedia(Profile $profile): array
    {
        $socialMedia = $profile->getSocialMedia();

        return [
            'twitter' => $socialMedia->getTwitter(),
            'twitch' => $socialMedia->getTwitch(),
            'discord' => $socialMedia->getDiscord(),
            'facebook' => $socialMedia->getFacebook(),
            'leaguepedia' => $socialMedia->getLeaguepedia(),
        ];
    }
}
