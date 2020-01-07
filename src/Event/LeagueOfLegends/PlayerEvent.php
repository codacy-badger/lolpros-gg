<?php

namespace App\Event\LeagueOfLegends;

use App\Entity\Profile\Profile;
use Symfony\Contracts\EventDispatcher\Event;

class PlayerEvent extends Event
{
    const CREATED = 'league.player.created';
    const UPDATED = 'league.player.updated';
    const DELETED = 'league.player.deleted';

    /**
     * @var Profile
     */
    private $player;

    public function __construct(Profile $profile)
    {
        $this->player = $profile;
    }

    public function getProfile(): Profile
    {
        return $this->player;
    }
}
