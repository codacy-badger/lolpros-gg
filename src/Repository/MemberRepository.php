<?php

namespace App\Repository;

use App\Entity\Profile\Profile;
use App\Entity\Team\Member;
use App\Entity\Team\Team;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class MemberRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Member::class);
    }

    private function toArrayString(array $uuids)
    {
        return array_map(function ($array) {
            return$array['uuid']->toString();
        }, $uuids);
    }

    public function getProfilesUuidsFromTeam(Team $team): array
    {
        $uuids = $this->createQueryBuilder('member')
            ->select('profile.uuid')
            ->join('member.profile', 'profile')
            ->join('member.team', 'team')
            ->where('team = :team')
            ->setParameter('team', $team)
            ->getQuery()
            ->getResult();

        return $this->toArrayString($uuids);
    }

    public function getMembersUuidsFromTeam(Team $team): array
    {
        $uuids = $this->createQueryBuilder('members')
            ->select('members.uuid')
            ->join('members.team', 'team')
            ->where('team = :team')
            ->setParameter('team', $team)
            ->getQuery()
            ->getResult();

        return $this->toArrayString($uuids);
    }

    public function getMembersUuidsFromPlayer(Profile $profile): array
    {
        $uuids = $this->createQueryBuilder('members')
            ->select('members.uuid')
            ->join('members.profile', 'profile')
            ->where('profile = :profile')
            ->setParameter('profile', $profile)
            ->getQuery()
            ->getResult();

        return $this->toArrayString($uuids);
    }
}
