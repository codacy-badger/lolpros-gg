<?php

namespace App\Repository\Core;

use App\Entity\Core\Player\Player;
use App\Entity\Core\Team\Member;
use App\Entity\Core\Team\Team;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class MemberRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Member::class);
    }

    public function getCurrentPlayerMemberships(Player $player)
    {
        return $this->createQueryBuilder('members')
            ->join('members.player', 'player')
            ->where('player  = :player')
            ->andWhere('members.leaveDate IS NULL')
            ->setParameter('player', $player)
            ->getQuery()
            ->getResult();
    }

    public function getPreviousPlayerMemberships(Player $player)
    {
        return $this->createQueryBuilder('members')
            ->join('members.player', 'player')
            ->where('player  = :player')
            ->andWhere('members.leaveDate IS NOT NULL')
            ->setParameter('player', $player)
            ->getQuery()
            ->getResult();
    }

    public function getCurrentTeamMemberships(Team $team)
    {
        return $this->createQueryBuilder('members')
            ->join('members.team', 'team')
            ->where('team  = :team')
            ->andWhere('members.leaveDate IS NULL')
            ->setParameter('team', $team)
            ->getQuery()
            ->getResult();
    }

    public function getPreviousTeamMemberships(Team $team)
    {
        return $this->createQueryBuilder('members')
            ->join('members.team', 'team')
            ->where('team  = :team')
            ->andWhere('members.leaveDate IS NOT NULL')
            ->setParameter('team', $team)
            ->getQuery()
            ->getResult();
    }
}
