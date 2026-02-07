<?php

namespace App\Exceptions\Violations;

use Exception;
use Throwable;

class SuspiciousActivityException extends Exception
{
    public function __construct(string $message = "Suspicious activity! Moderation access has been revoked. Contact the administrator.", int $code = 0, Throwable|null $previous = null)
    {
        return parent::__construct($message, $code, $previous);
    }
}
