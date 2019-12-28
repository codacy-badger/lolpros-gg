<?php

namespace App\Manager\Core\Player;

use App\Entity\Core\Player\Player;
use App\Entity\Core\Player\SocialMedia;
use App\Entity\LeagueOfLegends\Player\Player as LeaguePlayer;
use App\Event\Core\Player\PlayerEvent;
use App\Event\LeagueOfLegends\Player\PlayerEvent as LeaguePlayerEvent;
use App\Exception\Core\EntityNotUpdatedException;
use App\Manager\DefaultManager;
use Exception;

final class SocialMediaManager extends DefaultManager
{
    public function updateSocialMedia(Player $player, SocialMedia $socialMedia): SocialMedia
    {
        try {
            $media = $player->getSocialMedia();

            $media->setFacebook($socialMedia->getFacebook());
            $media->setTwitch($socialMedia->getTwitch());
            $media->setDiscord($socialMedia->getDiscord());
            $media->setTwitter($socialMedia->getTwitter());
            $media->setLeaguepedia($socialMedia->getLeaguepedia());

            $this->entityManager->flush($media);
            $this->entityManager->flush($player);

            switch (true) {
                case $player instanceof LeaguePlayer:
                    $this->eventDispatcher->dispatch(new LeaguePlayerEvent($player), LeaguePlayerEvent::UPDATED);
                    break;
                default:
                    $this->eventDispatcher->dispatch(new PlayerEvent($player), PlayerEvent::UPDATED);
            }

            return $media;
        } catch (Exception $e) {
            $this->logger->error('[SocialMediaManager] Could not update social medias for player {uuid} because of {reason}', ['uuid' => $player->getUuidAsString(), 'reason' => $e->getMessage()]);
            throw new EntityNotUpdatedException($socialMedia->getOwner() ? $socialMedia->getOwner()->getUuidAsString() : $socialMedia->getId(), $e->getMessage());
        }
    }
}
