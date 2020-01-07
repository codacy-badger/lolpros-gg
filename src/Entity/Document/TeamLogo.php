<?php

namespace App\Entity\Document;

use App\Entity\Team\Team;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 */
class TeamLogo extends Document
{
    /**
     * @var Team
     * @ORM\OneToOne(targetEntity="App\Entity\Team\Team", inversedBy="logo")
     * @Assert\NotNull
     */
    protected $team;

    public function getTeam(): Team
    {
        return $this->team;
    }

    public function setTeam(Team $team): self
    {
        $this->team = $team;

        return $this;
    }
}
