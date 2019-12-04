<?php

namespace App\Manager\Core\Team;

use App\Entity\Core\Team\Member;
use App\Event\Core\Team\MemberEvent;
use App\Exception\Core\EntityNotCreatedException;
use App\Exception\Core\EntityNotDeletedException;
use App\Exception\Core\EntityNotUpdatedException;
use App\Manager\DefaultManager;
use Exception;

class MemberManager extends DefaultManager
{
    public function create(Member $member): Member
    {
        try {
            $this->entityManager->persist($member);
            $this->entityManager->flush();

            $this->eventDispatcher->dispatch(new MemberEvent($member), MemberEvent::CREATED);

            return $member;
        } catch (Exception $e) {
            $this->logger->error('[MembersManager] Could not create Member because of {reason}', ['reason' => $e->getMessage()]);
            throw new EntityNotCreatedException(Member::class, $e->getMessage());
        }
    }

    public function update(Member $member): Member
    {
        try {
            $this->entityManager->flush($member);

            $this->eventDispatcher->dispatch(new MemberEvent($member), MemberEvent::UPDATED);

            return $member;
        } catch (Exception $e) {
            $this->logger->error('[MembersManager] Could not update Member {uuid} because of {reason}', ['uuid' => $member->getUuidAsString(), 'reason' => $e->getMessage()]);
            throw new EntityNotUpdatedException(Member::class, $member->getUuidAsString(), $e->getMessage());
        }
    }

    public function delete(Member $member): void
    {
        try {
            $member->getPlayer()->removeMemberships($member);
            $member->getTeam()->removeMember($member);

            $this->eventDispatcher->dispatch(new MemberEvent($member), MemberEvent::DELETED);

            $this->entityManager->remove($member);
            $this->entityManager->flush();
        } catch (Exception $e) {
            $this->logger->error('[MembersManager] Could not delete member {uuid} because of {reason}', ['uuid' => $member->getUuidAsString(), 'reason' => $e->getMessage()]);
            throw new EntityNotDeletedException(Member::class, $member->getUuidAsString(), $e->getMessage());
        }
    }
}
