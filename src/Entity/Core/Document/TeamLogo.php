<?php

namespace App\Entity\Core\Document;

use App\Entity\Core\Team\Team;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 */
class TeamLogo extends Document
{
    /**
     * @var Team
     * @ORM\OneToOne(targetEntity="App\Entity\Core\Team\Team", inversedBy="logo")
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
