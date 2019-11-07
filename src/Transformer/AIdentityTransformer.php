<?php

namespace App\Transformer;

use App\Entity\Core\Identity\Identity;
use App\Entity\Core\Region\Region;

abstract class AIdentityTransformer extends DefaultTransformer
{
    protected function buildTeam(Identity $identity): ?array
    {
        if (!($team = $identity->getCurrentTeam())) {
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

    protected function buildRegions(Identity $identity): array
    {
        $regions = [];

        foreach ($identity->getRegions() as $region) {
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

    protected function buildSocialMedia(Identity $identity): array
    {
        $socialMedia = $identity->getSocialMedia();

        return [
            'twitter' => $socialMedia->getTwitter(),
            'twitch' => $socialMedia->getTwitch(),
            'discord' => $socialMedia->getDiscord(),
            'facebook' => $socialMedia->getFacebook(),
            'leaguepedia' => $socialMedia->getLeaguepedia(),
        ];
    }
}
