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
    public function updateSocialMedia(Profile $profile, SocialMedia $socialMedia): SocialMedia
    {
        try {
            $media = $profile->getSocialMedia();

            $media->setFacebook($socialMedia->getFacebook());
            $media->setTwitch($socialMedia->getTwitch());
            $media->setDiscord($socialMedia->getDiscord());
            $media->setTwitter($socialMedia->getTwitter());
            $media->setLeaguepedia($socialMedia->getLeaguepedia());

            $this->entityManager->flush($media);
            $this->entityManager->flush($profile);

            switch (true) {
                case $profile instanceof Player:
                    $this->eventDispatcher->dispatch(new PlayerEvent($profile), PlayerEvent::UPDATED);
                    break;
                default:
                    $this->eventDispatcher->dispatch(new ProfileEvent($profile), ProfileEvent::UPDATED);
            }

            return $media;
        } catch (Exception $e) {
            $this->logger->error('[SocialMediaManager] Could not update social medias for player {uuid} because of {reason}', ['uuid' => $profile->getUuidAsString(), 'reason' => $e->getMessage()]);
            throw new EntityNotUpdatedException($socialMedia->getOwner() ? $socialMedia->getOwner()->getUuidAsString() : $socialMedia->getId(), $e->getMessage());
        }
    }
}