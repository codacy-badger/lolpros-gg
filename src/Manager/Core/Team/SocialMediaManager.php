<?php

namespace App\Manager\Core\Team;

use App\Entity\Core\Team\SocialMedia;
use App\Entity\Core\Team\Team;
use App\Event\Core\Team\TeamEvent;
use App\Exception\Core\EntityNotUpdatedException;
use App\Manager\DefaultManager;
use Exception;

final class SocialMediaManager extends DefaultManager
{
    public function updateSocialMedia(Team $team, SocialMedia $socialMedia): SocialMedia
    {
        try {
            $media = $team->getSocialMedia();

            $media->setFacebook($socialMedia->getFacebook());
            $media->setWebsite($socialMedia->getWebsite());
            $media->setTwitter($socialMedia->getTwitter());
            $media->setLeaguepedia($socialMedia->getLeaguepedia());

            $this->entityManager->flush($media);
            $this->entityManager->flush($team);

            $this->eventDispatcher->dispatch(new TeamEvent($team), TeamEvent::UPDATED);

            return $media;
        } catch (Exception $e) {
            $this->logger->error('[SocialMediaManager] Could not update social medias for team {uuid} because of {reason}', ['uuid' => $team->getUuidAsString(), 'reason' => $e->getMessage()]);
            throw new EntityNotUpdatedException($socialMedia->getOwner()->getUuidAsString(), $e->getMessage());
        }
    }
}
