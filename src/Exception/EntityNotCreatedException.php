<?php

namespace App\Exception;

use Throwable;

class EntityNotCreatedException extends \Exception
{
    public function __construct(string $className, $message = '', $code = 409, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->message = sprintf('Entity %s could not be created because of reason %s', $className, $message);
    }
}
