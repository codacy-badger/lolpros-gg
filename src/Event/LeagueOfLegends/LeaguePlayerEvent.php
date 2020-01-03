<?php

namespace App\Event\LeagueOfLegends;

use App\Entity\Profile\Profile;
use Symfony\Contracts\EventDispatcher\Event;

class LeaguePlayerEvent extends Event
{
    const CREATED = 'league.player.created';
    const UPDATED = 'league.player.updated';
    const DELETED = 'league.player.deleted';

    /**
     * @var Profile
     */
    private $player;

    public function __construct(Profile $player)
    {
        $this->player = $player;
    }

    public function getPlayer(): Profile
    {
        return $this->player;
    }
}
