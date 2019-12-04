<?php

namespace App\Event\Core\Medal;

use App\Entity\Core\Medal\AMedal;
use Symfony\Contracts\EventDispatcher\Event;

class MedalEvent extends Event
{
    const CREATED = 'medal.created';
    const UPDATED = 'medal.updated';
    const DELETED = 'medal.deleted';

    /**
     * @var AMedal
     */
    private $medal;

    public function __construct(AMedal $medal)
    {
        $this->medal = $medal;
    }

    public function getMedal(): AMedal
    {
        return $this->medal;
    }
}
