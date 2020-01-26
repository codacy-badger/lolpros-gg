<?php

namespace App\Manager\Profile;

use App\Entity\LeagueOfLegends\Player;
use App\Entity\Profile\Profile;
use App\Entity\Profile\SocialMedia;
use App\Event\LeagueOfLegends\PlayerEvent;
use App\Event\Profile\ProfileEvent;
use App\Exception\EntityNotUpdatedException;
use App\Manager\DefaultManager;
use Exception;

final class SocialMediaManager extends DefaultManager
{
    public function updateSocialMedia(Profile $profile, array $data): SocialMedia
    {
        try {
            $media = $profile->getSocialMedia();

            $media->setFacebook($data['facebook']);
            $media->setDiscord($data['discord']);
            $media->setTwitch($data['twitch']);
            $media->setTwitter($data['twitter']);
            $media->setLeaguepedia($data['leaguepedia']);

            $this->entityManager->flush();

            switch (true) {
                case $profile instanceof Player:
                    $this->eventDispatcher->dispatch(new PlayerEvent($profile), PlayerEvent::UPDATED);
                    break;
                default:
                    $this->eventDispatcher->dispatch(new ProfileEvent($profile), ProfileEvent::UPDATED);
            }

            return $media;
        } catch (Exception $e) {
            $this->logger->error('[SocialMediaManager] Could not update social medias for profile {uuid} because of {reason}', ['uuid' => $profile->getUuidAsString(), 'reason' => $e->getMessage()]);
            throw new EntityNotUpdatedException($profile->getUuidAsString(), $e->getMessage());
        }
    }
}
