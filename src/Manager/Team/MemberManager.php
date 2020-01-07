<?php

namespace App\Manager\Team;

use App\Entity\Team\Member;
use App\Event\Team\MemberEvent;
use App\Exception\EntityNotCreatedException;
use App\Exception\EntityNotDeletedException;
use App\Exception\EntityNotUpdatedException;
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

    public function delete(Member $member)
    {
        try {
            $member->getProfile()->removeMemberships($member);
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
