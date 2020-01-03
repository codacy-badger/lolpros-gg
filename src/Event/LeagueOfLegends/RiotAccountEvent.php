<?php

namespace App\Event\LeagueOfLegends;

use App\Entity\LeagueOfLegends\RiotAccount;
use Symfony\Contracts\EventDispatcher\Event;

class RiotAccountEvent extends Event
{
    const CREATED = 'riot_account.created';
    const UPDATED = 'riot_account.updated';
    const DELETED = 'riot_account.deleted';

    /**
     * @var RiotAccount
     */
    private $riotAccount;

    public function __construct(RiotAccount $riotAccount)
    {
        $this->riotAccount = $riotAccount;
    }

    public function getRiotAccount(): RiotAccount
    {
        return $this->riotAccount;
    }
}
