<?php

namespace App\Event\Core\Player;

use App\Entity\Core\Identity\Identity;
use Symfony\Contracts\EventDispatcher\Event;

class PlayerEvent extends Event
{
    const CREATED = 'player.created';
    const UPDATED = 'player.updated';
    const DELETED = 'player.deleted';

    /**
     * @var Identity
     */
    private $player;

    public function __construct(Identity $player)
    {
        $this->player = $player;
    }

    public function getPlayer(): Identity
    {
        return $this->player;
    }
}
