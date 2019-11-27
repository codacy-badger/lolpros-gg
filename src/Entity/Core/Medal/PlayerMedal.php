<?php

namespace App\Entity\Core\Medal;

use App\Entity\Core\Player\Player;
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
     * @ORM\ManyToMany(targetEntity="App\Entity\Core\Player\Player", mappedBy="medals")
     * @Serializer\Type("App\Entity\Core\Player\Player")
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
