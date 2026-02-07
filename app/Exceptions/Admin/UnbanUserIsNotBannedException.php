<?php

namespace App\Exceptions\Admin;

use Exception;
use Throwable;

class UnbanUserIsNotBannedException extends Exception
{
    public function __construct(string $message = "This user is not banned", int $code = 0, Throwable|null $previous = null)
    {
        return parent::__construct($message, $code, $previous);
    }
}
