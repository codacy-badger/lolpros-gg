<?php

namespace App\Event\Core\Player;

use App\Entity\Core\Player\Staff;
use Symfony\Contracts\EventDispatcher\Event;

class StaffEvent extends Event
{
    const CREATED = 'staff.created';
    const UPDATED = 'staff.updated';
    const DELETED = 'staff.deleted';

    /**
     * @var Staff
     */
    private $staff;

    public function __construct(Staff $staff)
    {
        $this->staff = $staff;
    }

    public function getStaff(): Staff
    {
        return $this->staff;
    }
}
