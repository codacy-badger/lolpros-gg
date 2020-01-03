<?php

namespace App\Event\Region;

use App\Entity\Region\Region;
use Symfony\Contracts\EventDispatcher\Event;

class RegionEvent extends Event
{
    const CREATED = 'region.created';
    const UPDATED = 'region.updated';
    const DELETED = 'region.deleted';

    /**
     * @var Region
     */
    private $region;

    public function __construct(Region $region)
    {
        $this->region = $region;
    }

    public function getRegion(): Region
    {
        return $this->region;
    }
}
