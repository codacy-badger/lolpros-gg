<?php

namespace App\Event\Team;

use App\Entity\Team\Team;
use Symfony\Contracts\EventDispatcher\Event;

class TeamEvent extends Event
{
    const CREATED = 'team.created';
    const UPDATED = 'team.updated';
    const DELETED = 'team.deleted';

    /**
     * @var Team
     */
    private $team;

    public function __construct(Team $team)
    {
        $this->team = $team;
    }

    public function getTeam(): Team
    {
        return $this->team;
    }
}
