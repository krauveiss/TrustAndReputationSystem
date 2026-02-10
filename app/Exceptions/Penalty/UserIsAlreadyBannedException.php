<?php

namespace App\Exceptions\Penalty;

use Exception;
use Throwable;

class UserIsAlreadyBannedException extends Exception
{
    public function __construct(string $message = "The user is banned and timeout cannot be applied.", int $code = 0, Throwable|null $previous = null)
    {
        return parent::__construct($message, $code, $previous);
    }
}
