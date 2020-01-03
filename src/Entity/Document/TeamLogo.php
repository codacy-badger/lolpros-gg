<?php

namespace App\Entity\Document;

use App\Entity\Team\Team;
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
     * @ORM\OneToOne(targetEntity="App\Entity\Team\Team", inversedBy="logo")
     * @Serializer\Type("App\Entity\Team\Team")
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
