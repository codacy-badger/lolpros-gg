<?php

namespace App\Entity\Core\Document;

use App\Entity\Core\Medal\AMedal;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class MedalLogo extends Document
{
    /**
     * @var AMedal
     * @ORM\OneToOne(targetEntity="App\Entity\Core\Medal\AMedal", inversedBy="logo")
     */
    protected $medal;

    public function getMedal(): AMedal
    {
        return $this->medal;
    }

    public function setMedal(AMedal $medal): self
    {
        $this->medal = $medal;

        return $this;
    }
}
