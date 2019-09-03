<?php

namespace App\Manager\LeagueOfLegends\Region;

use App\Entity\LeagueOfLegends\Region\Region;
use App\Event\LeagueOfLegends\Region\RegionEvent;
use App\Exception\Core\EntityNotCreatedException;
use App\Exception\Core\EntityNotDeletedException;
use App\Exception\Core\EntityNotUpdatedException;
use App\Manager\DefaultManager;

class RegionManager extends DefaultManager
{
    public function create(Region $region): Region
    {
        try {
            $this->entityManager->persist($region);
            $this->entityManager->flush($region);

            return $region;
        } catch (\Exception $e) {
            $this->logger->error('[RegionsManager] Could not create region because of {reason}', [
                'reason' => $e->getMessage(),
            ]);

            throw new EntityNotCreatedException(Region::class, $e->getMessage());
        }
    }

    public function update(Region $region): Region
    {
        try {
            $this->entityManager->flush($region);

            $this->eventDispatcher->dispatch(new RegionEvent($region), RegionEvent::UPDATED);

            return $region;
        } catch (\Exception $e) {
            $this->logger->error('[RegionsManager] Could not update region {uuid} because of {reason}', [
                'uuid' => $region->getUuid()->toString(),
                'reason' => $e->getMessage(),
            ]);

            throw new EntityNotUpdatedException(Region::class, $region->getUuid()->toString(), $e->getMessage());
        }
    }

    public function delete(Region $region)
    {
        try {
            $this->entityManager->remove($region);
            $this->entityManager->flush($region);
        } catch (\Exception $e) {
            $this->logger->error('[RegionsManager] Could not delete region {uuid} because of {reason}', [
                'uuid' => $region->getUuid()->toString(),
                'reason' => $e->getMessage(),
            ]);

            throw new EntityNotDeletedException(Region::class, $region->getUuid()->toString(), $e->getMessage());
        }
    }
}
