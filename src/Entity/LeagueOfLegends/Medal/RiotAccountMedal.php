<?php

namespace App\Entity\LeagueOfLegends\Medal;

use App\Entity\Core\Medal\AMedal;
use App\Entity\Core\Player\Player;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity
 */
class RiotAccountMedal extends AMedal
{
    /**
     * @var Collection|Player[]
     * @ORM\ManyToMany(targetEntity="App\Entity\LeagueOfLegends\Player\RiotAccount", mappedBy="medals")
     * @Serializer\Type("App\Entity\LeagueOfLegends\Player\RiotAccount")
     * @Serializer\Groups({
     * })
     */
    protected $accounts;

    public function __construct()
    {
        parent::__construct();
        $this->accounts = new ArrayCollection();
    }

    public function getAccounts(): Collection
    {
        return $this->accounts;
    }
}
