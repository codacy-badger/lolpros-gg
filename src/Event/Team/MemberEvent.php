<?php

namespace App\Event\Team;

use App\Entity\Team\Member;
use Symfony\Contracts\EventDispatcher\Event;

class MemberEvent extends Event
{
    const CREATED = 'member.created';
    const UPDATED = 'member.updated';
    const DELETED = 'member.deleted';

    /**
     * @var Member
     */
    private $member;

    public function __construct(Member $member)
    {
        $this->member = $member;
    }

    public function getMember(): Member
    {
        return $this->member;
    }
}
