<?php

namespace App\Manager\Team;

use App\Entity\Team\SocialMedia;
use App\Entity\Team\Team;
use App\Event\Team\TeamEvent;
use App\Exception\EntityNotUpdatedException;
use App\Manager\DefaultManager;
use Exception;

final class SocialMediaManager extends DefaultManager
{
    public function updateSocialMedia(Team $team, array $data): SocialMedia
    {
        try {
            $media = $team->getSocialMedia();

            $media->setFacebook($data['facebook']);
            $media->setWebsite($data['website']);
            $media->setTwitter($data['twitter']);
            $media->setLeaguepedia($data['leaguepedia']);

            $this->entityManager->flush();

            $this->eventDispatcher->dispatch(new TeamEvent($team), TeamEvent::UPDATED);

            return $media;
        } catch (Exception $e) {
            $this->logger->error('[SocialMediaManager] Could not update social medias for team {uuid} because of {reason}', ['uuid' => $team->getUuidAsString(), 'reason' => $e->getMessage()]);
            throw new EntityNotUpdatedException($team->getUuidAsString(), $e->getMessage());
        }
    }
}
