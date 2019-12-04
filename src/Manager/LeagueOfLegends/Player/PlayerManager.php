<?php

namespace App\Manager\LeagueOfLegends\Player;

use App\Entity\LeagueOfLegends\Player\Player;
use App\Entity\LeagueOfLegends\Player\RiotAccount;
use App\Event\LeagueOfLegends\Player\PlayerEvent;
use App\Exception\Core\EntityNotCreatedException;
use App\Exception\Core\EntityNotDeletedException;
use App\Exception\Core\EntityNotUpdatedException;
use App\Manager\DefaultManager;
use Exception;

final class PlayerManager extends DefaultManager
{
    public function create(Player $player): Player
    {
        $this->logger->debug('[PlayersManager::create] Creating player {uuid}', ['uuid' => $player->getUuidAsString()]);
        try {
            $this->entityManager->persist($player);
            $this->entityManager->flush($player);

            $this->eventDispatcher->dispatch(new PlayerEvent($player), PlayerEvent::CREATED);

            return $player;
        } catch (Exception $e) {
            $this->logger->error('[PlayersManager::create] Could not create player because of {reason}', ['reason' => $e->getMessage()]);

            throw new EntityNotCreatedException(Player::class, $e->getMessage());
        }
    }

    public function update(Player $player, Player $playerData): Player
    {
        $this->logger->debug('[PlayersManager::update] Updating player {uuid}', ['uuid' => $player->getUuidAsString()]);
        try {
            $player->setName($playerData->getName() ? $playerData->getName() : $player->getName());
            $player->setCountry($playerData->getCountry() ? $playerData->getCountry() : $player->getCountry());
            $player->setPosition($playerData->getPosition() ? $playerData->getPosition() : $player->getPosition());
            $player->setRegions($playerData->getRegions());

            $this->entityManager->flush($player);

            $this->eventDispatcher->dispatch(new PlayerEvent($player), PlayerEvent::UPDATED);

            return $player;
        } catch (Exception $e) {
            $this->logger->error('[PlayersManager::update]] Could not update player {uuid} because of {reason}', [
                'uuid' => $player->getUuidAsString(),
                'reason' => $e->getMessage(),
            ]);

            throw new EntityNotUpdatedException(Player::class, $player->getUuidAsString(), $e->getMessage());
        }
    }

    public function delete(Player $player): void
    {
        $this->logger->debug('[PlayersManager::delete] Deleting player {uuid}', ['uuid' => $player->getUuidAsString()]);
        try {
            foreach ($player->getMemberships() as $membership) {
                $this->entityManager->remove($membership);
            }

            $this->entityManager->remove($player);
            $this->entityManager->flush();

            $this->eventDispatcher->dispatch(new PlayerEvent($player), PlayerEvent::DELETED);
        } catch (Exception $e) {
            $this->logger->error('[PlayersManager::delete] Could not delete player {uuid} because of {reason}', [
                'uuid' => $player->getUuidAsString(),
                'reason' => $e->getMessage(),
            ]);

            throw new EntityNotDeletedException(Player::class, $player->getUuidAsString(), $e->getMessage());
        }
    }

    public function findWithAccount(string $summonerId): ?RiotAccount
    {
        return $this->entityManager->getRepository(RiotAccount::class)->findOneBy([
            'encryptedRiotId' => $summonerId,
        ]);
    }
}
