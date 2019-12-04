<?php

namespace App\Manager\Core\Medal;

use App\Entity\Core\Medal\AMedal;
use App\Entity\Core\Medal\PlayerMedal;
use App\Entity\LeagueOfLegends\Medal\PlayerMedal as LoLPlayerMedal;
use App\Entity\LeagueOfLegends\Medal\RiotAccountMedal;
use App\Event\Core\Medal\MedalEvent;
use App\Exception\Core\EntityNotCreatedException;
use App\Exception\Core\EntityNotDeletedException;
use App\Exception\Core\EntityNotUpdatedException;
use App\Manager\DefaultManager;
use Exception;

class MedalManager extends DefaultManager
{
    public function create(AMedal $medalData): AMedal
    {
        try {
            switch (true) {
                case $medalData instanceof PlayerMedal:
                    $medal = new PlayerMedal();
                    break;
                case $medalData instanceof LoLPlayerMedal:
                    $medal = new LoLPlayerMedal();
                    break;
                case $medalData instanceof RiotAccountMedal:
                    $medal = new RiotAccountMedal();
                    break;
                default:
                    throw new EntityNotCreatedException('unrecognized medal type');
            }

            $medal->setName($medalData->getName());

            $this->entityManager->persist($medal);
            $this->entityManager->flush();

            $this->eventDispatcher->dispatch(new MedalEvent($medal), MedalEvent::CREATED);

            return $medal;
        } catch (Exception $e) {
            $this->logger->error('[MedalsManager] Could not create medal because of {reason}', [
                'reason' => $e->getMessage(),
            ]);

            throw new EntityNotCreatedException(AMedal::class, $e->getMessage());
        }
    }

    public function update(AMedal $medal): AMedal
    {
        try {
            $this->entityManager->flush($medal);

            $this->eventDispatcher->dispatch(new MedalEvent($medal), MedalEvent::UPDATED);

            return $medal;
        } catch (Exception $e) {
            $this->logger->error('[MedalsManager] Could not update medal {uuid} because of {reason}', [
                'uuid' => $medal->getUuidAsString(),
                'reason' => $e->getMessage(),
            ]);

            throw new EntityNotUpdatedException(AMedal::class, $medal->getUuidAsString(), $e->getMessage());
        }
    }

    public function delete(AMedal $medal): void
    {
        try {
            $this->entityManager->remove($medal);
            $this->entityManager->flush($medal);

            $this->eventDispatcher->dispatch(new MedalEvent($medal), MedalEvent::DELETED);
        } catch (Exception $e) {
            $this->logger->error('[MedalsManager] Could not delete region {uuid} because of {reason}', [
                'uuid' => $medal->getUuidAsString(),
                'reason' => $e->getMessage(),
            ]);

            throw new EntityNotDeletedException(AMedal::class, $medal->getUuidAsString(), $e->getMessage());
        }
    }
}
