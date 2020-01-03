<?php

namespace App\Manager\Profile;

use App\Entity\LeagueOfLegends\LeaguePlayer;
use App\Entity\Profile\Profile;
use App\Entity\Profile\SocialMedia;
use App\Event\LeagueOfLegends\LeaguePlayerEvent;
use App\Event\Profile\ProfileEvent;
use App\Exception\Core\EntityNotUpdatedException;
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
                case $profile instanceof LeaguePlayer:
                    $this->eventDispatcher->dispatch(new LeaguePlayerEvent($profile), LeaguePlayerEvent::UPDATED);
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
