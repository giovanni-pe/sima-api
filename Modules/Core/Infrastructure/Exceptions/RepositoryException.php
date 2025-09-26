<?php

namespace Modules\Core\Infrastructure\Exceptions;

use Throwable;

class RepositoryException extends \RuntimeException
{
    public function __construct(string $message = 'Repository error', int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
