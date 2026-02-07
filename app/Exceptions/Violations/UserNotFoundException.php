<?php

namespace App\Exceptions\Violations;

use Exception;
use Throwable;

class UserNotFoundException extends Exception
{
    public function __construct(string $message = "User not found", int $code = 0, Throwable|null $previous = null)
    {
        return parent::__construct($message, $code, $previous);
    }
}
