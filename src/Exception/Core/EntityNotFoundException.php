<?php

namespace App\Exception\Core;

use Exception;
use Throwable;

class EntityNotFoundException extends Exception
{
    public function __construct($entity, $uuid, $message = '', $code = 409, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->message = sprintf('Entity %s with uuid %s was not found', $entity, $uuid);
    }
}
