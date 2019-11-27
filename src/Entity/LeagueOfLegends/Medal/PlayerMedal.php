<?php

namespace App\Entity\LeagueOfLegends\Medal;

use App\Entity\Core\Medal\AMedal;
use App\Entity\LeagueOfLegends\Player\Player;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity
 */
class PlayerMedal extends AMedal
{
    /**
     * @var Collection|Player[]
     * @ORM\ManyToMany(targetEntity="App\Entity\LeagueOfLegends\Player\Player", mappedBy="medals")
     * @Serializer\Type("App\Entity\LeagueOfLegends\Player\Player")
     * @Serializer\Groups({
     * })
     */
    protected $players;

    public function __construct()
    {
        parent::__construct();
        $this->players = new ArrayCollection();
    }

    public function getPlayers(): Collection
    {
        return $this->players;
    }
}
