<?php

namespace App\Event\LeagueOfLegends;

use App\Entity\LeagueOfLegends\SummonerName;
use Symfony\Contracts\EventDispatcher\Event;

class SummonerNameEvent extends Event
{
    const CREATED = 'summoner_name.created';

    /**
     * @var SummonerName
     */
    private $summonerName;

    public function __construct(SummonerName $summonerName)
    {
        $this->summonerName = $summonerName;
    }

    public function getSummonerName(): SummonerName
    {
        return $this->summonerName;
    }
}
