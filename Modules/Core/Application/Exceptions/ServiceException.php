<?php

namespace Modules\Core\Application\Exceptions;

use Throwable;

class ServiceException extends \RuntimeException
{
    public function __construct(string $message = "Service error", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
