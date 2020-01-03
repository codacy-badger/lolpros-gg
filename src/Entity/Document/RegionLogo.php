<?php

namespace App\Entity\Document;

use App\Entity\Region\Region;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class RegionLogo extends Document
{
    /**
     * @var Region
     * @ORM\OneToOne(targetEntity="App\Entity\Region\Region", inversedBy="logo")
     */
    protected $region;

    public function getRegion(): Region
    {
        return $this->region;
    }

    public function setRegion(Region $region): self
    {
        $this->region = $region;

        return $this;
    }
}
