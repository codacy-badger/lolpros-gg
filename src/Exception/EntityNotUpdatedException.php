<?php

namespace App\Exception;

use Throwable;

class EntityNotUpdatedException extends \Exception
{
    public function __construct($entity, $uuid, $message = '', $code = 409, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->message = sprintf('Entity %s with uuid %s could not be updated because of reason %s', $entity, $uuid, $message);
    }
}
