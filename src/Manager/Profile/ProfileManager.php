<?php

namespace App\Manager\Profile;

use App\Entity\LeagueOfLegends\RiotAccount;
use App\Entity\Profile\Profile;
use App\Event\Profile\ProfileEvent;
use App\Exception\Core\EntityNotCreatedException;
use App\Exception\Core\EntityNotDeletedException;
use App\Exception\Core\EntityNotUpdatedException;
use App\Manager\DefaultManager;
use Exception;

final class ProfileManager extends DefaultManager
{
    public function create(Profile $profile): Profile
    {
        $this->logger->debug('[ProfileManager::create] Creating player {uuid}', ['uuid' => $profile->getUuidAsString()]);
        try {
            $this->entityManager->persist($profile);
            $this->entityManager->flush($profile);

            $this->eventDispatcher->dispatch(new ProfileEvent($profile), ProfileEvent::CREATED);

            return $profile;
        } catch (Exception $e) {
            $this->logger->error('[ProfileManager::create] Could not create player because of {reason}', ['reason' => $e->getMessage()]);

            throw new EntityNotCreatedException(Profile::class, $e->getMessage());
        }
    }

    public function update(Profile $profile, Profile $profileData): Profile
    {
        $this->logger->debug('[ProfileManager::update] Updating player {uuid}', ['uuid' => $profile->getUuidAsString()]);
        try {
            $profile->setName($profileData->getName() ? $profileData->getName() : $profile->getName());
            $profile->setCountry($profileData->getCountry() ? $profileData->getCountry() : $profile->getCountry());
            $profile->setPosition($profileData->getPosition() ? $profileData->getPosition() : $profile->getPosition());
            $profile->setRegions($profileData->getRegions());

            $this->entityManager->flush($profile);

            $this->eventDispatcher->dispatch(new ProfileEvent($profile), ProfileEvent::UPDATED);

            return $profile;
        } catch (Exception $e) {
            $this->logger->error('[ProfileManager::update]] Could not update player {uuid} because of {reason}', [
                'uuid' => $profile->getUuidAsString(),
                'reason' => $e->getMessage(),
            ]);

            throw new EntityNotUpdatedException(Profile::class, $profile->getUuidAsString(), $e->getMessage());
        }
    }

    public function delete(Profile $profile)
    {
        $this->logger->debug('[ProfileManager::delete] Deleting player {uuid}', ['uuid' => $profile->getUuidAsString()]);
        try {
            foreach ($profile->getMemberships() as $membership) {
                $this->entityManager->remove($membership);
            }

            $this->entityManager->remove($profile);
            $this->entityManager->flush();

            $this->eventDispatcher->dispatch(new ProfileEvent($profile), ProfileEvent::DELETED);
        } catch (Exception $e) {
            $this->logger->error('[ProfileManager::delete] Could not delete player {uuid} because of {reason}', [
                'uuid' => $profile->getUuidAsString(),
                'reason' => $e->getMessage(),
            ]);

            throw new EntityNotDeletedException(Profile::class, $profile->getUuidAsString(), $e->getMessage());
        }
    }

    public function findWithAccount(string $summonerId): ?RiotAccount
    {
        return $this->entityManager->getRepository(RiotAccount::class)->findOneBy([
            'encryptedRiotId' => $summonerId,
        ]);
    }
}
