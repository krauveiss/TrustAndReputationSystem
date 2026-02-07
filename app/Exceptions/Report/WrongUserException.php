<?php

namespace App\Exceptions\Report;

use Exception;
use Throwable;

class WrongUserException extends Exception
{
    public function __construct(string $message = "Wrong user to report", int $code = 0, Throwable|null $previous = null)
    {
        return parent::__construct($message, $code, $previous);
    }
}
