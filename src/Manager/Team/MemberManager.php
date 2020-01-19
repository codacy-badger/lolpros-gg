<?php

namespace App\Manager\Team;

use App\Entity\Profile\Profile;
use App\Entity\Team\Member;
use App\Entity\Team\Team;
use App\Event\Team\MemberEvent;
use App\Exception\EntityNotCreatedException;
use App\Exception\EntityNotDeletedException;
use App\Exception\EntityNotUpdatedException;
use App\Manager\DefaultManager;
use DateTime;
use Exception;

class MemberManager extends DefaultManager
{
    public function create(array $data): Member
    {
        try {
            $this->entityManager->beginTransaction();
            $member = new Member();

            $member->setRole($data['role']);
            $member->setJoinDate(new DateTime($data['joinDate']));
            $member->setLeaveDate($data['leaveDate'] ? new DateTime($data['leaveDate']) : null);

            $member->setProfile($this->entityManager->getRepository(Profile::class)->findOneBy(['uuid' => $data['profile']]));
            $member->setTeam($this->entityManager->getRepository(Team::class)->findOneBy(['uuid' => $data['team']]));

            $this->entityManager->persist($member);
            $this->entityManager->flush();
            $this->entityManager->commit();

            $this->eventDispatcher->dispatch(new MemberEvent($member), MemberEvent::CREATED);

            return $member;
        } catch (Exception $e) {
            $this->entityManager->rollback();
            $this->logger->error('[MembersManager] Could not create Member because of {reason}', ['reason' => $e->getMessage()]);
            throw new EntityNotCreatedException(Member::class, $e->getMessage());
        }
    }

    public function update(Member $member, Member $memberData): Member
    {
        try {
            $this->entityManager->beginTransaction();
            $member->setRole($memberData->getRole());
            $member->setJoinDate($memberData->getJoinDate());
            $member->setLeaveDate($memberData->getLeaveDate());

            $this->entityManager->flush($member);
            $this->entityManager->commit();

            $this->eventDispatcher->dispatch(new MemberEvent($member), MemberEvent::UPDATED);

            return $member;
        } catch (Exception $e) {
            $this->entityManager->rollback();
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
