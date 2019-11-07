<?php

namespace App\Manager\Core\Identity;

use App\Entity\Core\Identity\Identity;
use App\Entity\Core\Identity\SocialMedia;
use App\Entity\LeagueOfLegends\Player\Player;
use App\Event\Core\Identity\IdentityEvent;
use App\Event\LeagueOfLegends\Player\PlayerEvent;
use App\Exception\Core\EntityNotUpdatedException;
use App\Manager\DefaultManager;
use Exception;

final class SocialMediaManager extends DefaultManager
{
    public function updateSocialMedia(Identity $identity, SocialMedia $socialMedia): SocialMedia
    {
        try {
            $media = $identity->getSocialMedia();

            $media->setFacebook($socialMedia->getFacebook());
            $media->setTwitch($socialMedia->getTwitch());
            $media->setDiscord($socialMedia->getDiscord());
            $media->setTwitter($socialMedia->getTwitter());
            $media->setLeaguepedia($socialMedia->getLeaguepedia());

            $this->entityManager->flush($media);
            $this->entityManager->flush($identity);

            switch (true) {
                case $identity instanceof Player:
                    $this->eventDispatcher->dispatch(new PlayerEvent($identity), PlayerEvent::UPDATED);
                    break;
                default:
                    $this->eventDispatcher->dispatch(new IdentityEvent($identity), IdentityEvent::UPDATED);
            }

            return $media;
        } catch (Exception $e) {
            $this->logger->error('[SocialMediaManager] Could not update social medias for identity {uuid} because of {reason}', ['uuid' => $identity->getUuidAsString(), 'reason' => $e->getMessage()]);
            throw new EntityNotUpdatedException($socialMedia->getOwner()->getUuidAsString(), $e->getMessage());
        }
    }
}
