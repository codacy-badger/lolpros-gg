<?php

namespace App\Event\Core\Identity;

use App\Entity\Core\Identity\Identity;
use Symfony\Contracts\EventDispatcher\Event;

class IdentityEvent extends Event
{
    const CREATED = 'identity.created';
    const UPDATED = 'identity.updated';
    const DELETED = 'identity.deleted';

    /**
     * @var Identity
     */
    private $identity;

    public function __construct(Identity $identity)
    {
        $this->identity = $identity;
    }

    public function getIdentity(): Identity
    {
        return $this->identity;
    }
}
