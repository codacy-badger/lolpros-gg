<?php

namespace App\Exception\Core;

use Throwable;

class EntityNotDeletedException extends \Exception
{
    public function __construct($entity, $uuid, $message = '', $code = 409, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->message = sprintf('Entity %s with uuid %s could not be deleted because of reason %s', $entity, $uuid, $message);
    }
}
