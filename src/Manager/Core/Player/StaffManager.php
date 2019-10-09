<?php

namespace App\Manager\Core\Player;

use App\Entity\Core\Player\Staff;
use App\Event\Core\Player\StaffEvent;
use App\Exception\Core\EntityNotCreatedException;
use App\Exception\Core\EntityNotDeletedException;
use App\Exception\Core\EntityNotUpdatedException;
use App\Manager\DefaultManager;
use Exception;

final class StaffManager extends DefaultManager
{
    public function create(Staff $staff): Staff
    {
        $this->logger->debug('[StaffsManager::create] Creating staff {uuid}', ['uuid' => $staff->getUuidAsString()]);
        try {
            $this->entityManager->persist($staff);
            $this->entityManager->flush($staff);

            $this->eventDispatcher->dispatch(new StaffEvent($staff), StaffEvent::CREATED);

            return $staff;
        } catch (Exception $e) {
            $this->logger->error('[StaffsManager::create] Could not create staff because of {reason}', ['reason' => $e->getMessage()]);

            throw new EntityNotCreatedException(Staff::class, $e->getMessage());
        }
    }

    public function update(Staff $staff, Staff $staffData): Staff
    {
        $this->logger->debug('[StaffsManager::update] Updating staff {uuid}', ['uuid' => $staff->getUuidAsString()]);
        try {
            $staff->setName($staffData->getName() ? $staffData->getName() : $staff->getName());
            $staff->setCountry($staffData->getCountry() ? $staffData->getCountry() : $staff->getCountry());
            $staff->setRole($staffData->getRole() ? $staffData->getRole() : $staff->getRole());
            $staff->setRoleName($staffData->getRoleName() ? $staffData->getRoleName() : $staff->getRoleName());
            $staff->setRegions($staffData->getRegions());

            $this->entityManager->flush($staff);

            $this->eventDispatcher->dispatch(new StaffEvent($staff), StaffEvent::UPDATED);

            return $staff;
        } catch (Exception $e) {
            $this->logger->error('[StaffsManager::update]] Could not update staff {uuid} because of {reason}', [
                'uuid' => $staff->getUuidAsString(),
                'reason' => $e->getMessage(),
            ]);

            throw new EntityNotUpdatedException(Staff::class, $staff->getUuidAsString(), $e->getMessage());
        }
    }

    public function delete(Staff $staff)
    {
        $this->logger->debug('[StaffsManager::delete] Deleting staff {uuid}', ['uuid' => $staff->getUuidAsString()]);
        try {
            $this->eventDispatcher->dispatch(new StaffEvent($staff), StaffEvent::DELETED);

            foreach ($staff->getMemberships() as $membership) {
                $this->logger->debug('[StaffsManager::deleteMembership] Deleting membership {uuid}', ['uuid' => $membership->getUuidAsString()]);
                $this->entityManager->remove($membership);
            }

            $this->entityManager->remove($staff);
            $this->entityManager->flush();
        } catch (Exception $e) {
            $this->logger->error('[StaffsManager::delete] Could not delete staff {uuid} because of {reason}', [
                'uuid' => $staff->getUuidAsString(),
                'reason' => $e->getMessage(),
            ]);

            throw new EntityNotDeletedException(Staff::class, $staff->getUuidAsString(), $e->getMessage());
        }
    }
}
