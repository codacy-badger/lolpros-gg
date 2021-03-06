<?php

namespace App\Event\LeagueOfLegends\Player;

use App\Entity\LeagueOfLegends\Player\Ranking;
use Symfony\Contracts\EventDispatcher\Event;

class RankingEvent extends Event
{
    const CREATED = 'ranking.created';
    const UPDATED = 'ranking.updated';
    const DELETED = 'ranking.deleted';

    /**
     * @var Ranking
     */
    private $ranking;

    public function __construct(Ranking $ranking)
    {
        $this->ranking = $ranking;
    }

    public function getRanking(): Ranking
    {
        return $this->ranking;
    }
}
