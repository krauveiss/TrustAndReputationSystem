<?php

namespace App\Exceptions\Admin;

use Exception;
use Throwable;


class AttempToBanAdminException extends Exception
{
    public function __construct(string $message = "Security error", int $code = 0, Throwable|null $previous = null)
    {
        return parent::__construct($message, $code, $previous);
    }
}
