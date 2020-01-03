<?php

namespace App\Event\Profile;

use App\Entity\Profile\Profile;
use Symfony\Contracts\EventDispatcher\Event;

class ProfileEvent extends Event
{
    const CREATED = 'player.created';
    const UPDATED = 'player.updated';
    const DELETED = 'player.deleted';

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
