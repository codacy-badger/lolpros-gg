<?php

namespace App\Manager\Profile;

use App\Entity\LeagueOfLegends\Player;
use App\Entity\LeagueOfLegends\RiotAccount;
use App\Entity\Profile\Profile;
use App\Entity\Profile\Staff;
use App\Entity\Region\Region;
use App\Event\Profile\ProfileEvent;
use App\Exception\EntityNotCreatedException;
use App\Exception\EntityNotDeletedException;
use App\Exception\EntityNotUpdatedException;
use App\Manager\DefaultManager;
use Doctrine\Common\Collections\ArrayCollection;
use Exception;

final class ProfileManager extends DefaultManager
{
    public function create(array $data): Profile
    {
        try {
            $this->entityManager->beginTransaction();
            $profile = new Profile();

            $profile->setName($data['name']);
            $profile->setCountry($data['country']);
            $this->setProfileRegions($profile, $data['regions']);
            $this->entityManager->persist($profile);

            if ($data['leaguePlayer']) {
                $leaguePlayer = new Player();
                $leaguePlayer->setPosition($data['leaguePlayer']['position']);
                $leaguePlayer->setProfile($profile);
                $profile->setLeaguePlayer($leaguePlayer);
                $this->entityManager->persist($leaguePlayer);
            }

            if ($data['staff']) {
                $staff = new Staff();
                $staff->setPosition($data['staff']['position']);
                $staff->setProfile($profile);
                $profile->setStaff($staff);
                $this->entityManager->persist($staff);
            }

            $this->entityManager->flush();
            $this->entityManager->commit();
            $this->eventDispatcher->dispatch(new ProfileEvent($profile), ProfileEvent::CREATED);

            return $profile;
        } catch (Exception $e) {
            $this->entityManager->rollback();
            $this->logger->error('[ProfileManager::create] Could not create player because of {reason}', ['reason' => $e->getMessage()]);

            throw new EntityNotCreatedException(Profile::class, $e->getMessage());
        }
    }

    public function update(Profile $profile, array $data): Profile
    {
        try {
            $this->entityManager->beginTransaction();
            $profile->setName($data['name'] ? $data['name'] : $profile->getName());
            $profile->setCountry($data['country'] ? $data['country'] : $profile->getCountry());
            $this->setProfileRegions($profile, $data['regions']);

            $this->updateProfilePlayer($profile, $data['leaguePlayer']);
            $this->updateProfileStaff($profile, $data['staff']);

            $this->entityManager->flush($profile);
            $this->entityManager->commit();
            $this->eventDispatcher->dispatch(new ProfileEvent($profile), ProfileEvent::UPDATED);

            return $profile;
        } catch (Exception $e) {
            $this->entityManager->rollback();
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

    private function setProfileRegions(Profile $profile, array $regionsList)
    {
        $regions = new ArrayCollection();
        $regionRepository = $this->entityManager->getRepository(Region::class);
        foreach ($regionsList as $region) {
            $regions->add($regionRepository->findOneBy(['uuid' => is_array($region) ? $region['uuid'] : $region]));
        }
        $profile->setRegions($regions);
    }

    private function updateProfilePlayer(Profile $profile, ?array $data)
    {
        if (!$data) {
            return;
        }

        $leaguePlayer = $profile->getLeaguePlayer();
        if (!$leaguePlayer) {
            $player = new Player();
            $player->setPosition($data['position']);
            $player->setProfile($profile);
            $profile->setLeaguePlayer($player);
            $this->entityManager->persist($player);

            return;
        }

        $leaguePlayer->setPosition($data['position']);
        $this->entityManager->flush();
    }

    private function updateProfileStaff(Profile $profile, ?array $data)
    {
        if (!$data) {
            return;
        }

        $staff = $profile->getStaff();
        if (!$staff) {
            $newStaff = new Staff();
            $newStaff->setPosition($data['position']);
            $newStaff->setProfile($profile);
            $profile->setStaff($newStaff);
            $this->entityManager->persist($newStaff);

            return;
        }

        $staff->setPosition($data['position']);
        $this->entityManager->flush();
    }

    public function findWithAccount(string $summonerId): ?RiotAccount
    {
        return $this->entityManager->getRepository(RiotAccount::class)->findOneBy([
            'encryptedRiotId' => $summonerId,
        ]);
    }
}
