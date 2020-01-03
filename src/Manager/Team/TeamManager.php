<?php

namespace App\Manager\Team;

use App\Entity\Team\Team;
use App\Event\Team\TeamEvent;
use App\Exception\Core\EntityNotCreatedException;
use App\Exception\Core\EntityNotDeletedException;
use App\Exception\Core\EntityNotUpdatedException;
use App\Manager\DefaultManager;
use Exception;

final class TeamManager extends DefaultManager
{
    public function create(Team $team): Team
    {
        try {
            $this->entityManager->persist($team);
            $this->entityManager->flush($team);

            $this->eventDispatcher->dispatch(new TeamEvent($team), TeamEvent::CREATED);

            return $team;
        } catch (Exception $e) {
            $this->logger->error('[TeamsManager] Could not create team because of {reason}', ['reason' => $e->getMessage()]);
            throw new EntityNotCreatedException(Team::class, $e->getMessage());
        }
    }

    public function update(Team $team, Team $teamData): Team
    {
        try {
            $team->setName($teamData->getName() ? $teamData->getName() : $team->getName());
            $team->setTag($teamData->getTag() ? $teamData->getTag() : $team->getTag());
            $team->setRegion($teamData->getRegion());
            $team->setCreationDate($teamData->getCreationDate() ? $teamData->getCreationDate() : $team->getCreationDate());
            $team->setDisbandDate($teamData->getDisbandDate() ? $teamData->getDisbandDate() : $team->getDisbandDate());

            $this->entityManager->flush($team);

            $this->eventDispatcher->dispatch(new TeamEvent($team), TeamEvent::UPDATED);

            return $team;
        } catch (Exception $e) {
            $this->logger->error('[TeamsManager] Could not update team {uuid} because of {reason}', ['uuid' => $team->getUuidAsString(), 'reason' => $e->getMessage()]);
            throw new EntityNotUpdatedException(Team::class, $team->getUuidAsString(), $e->getMessage());
        }
    }

    public function delete(Team $team)
    {
        try {
            $this->eventDispatcher->dispatch(new TeamEvent($team), TeamEvent::DELETED);

            $this->entityManager->remove($team);
            $this->entityManager->flush($team);
        } catch (Exception $e) {
            $this->logger->error('[TeamsManager] Could not delete team {uuid} because of {reason}', ['uuid' => $team->getUuidAsString(), 'reason' => $e->getMessage()]);
            throw new EntityNotDeletedException(Team::class, $team->getUuidAsString(), $e->getMessage());
        }
    }
}
