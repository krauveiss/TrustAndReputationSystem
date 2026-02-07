<?php

namespace App\Exceptions\Report;

use Exception;
use Throwable;

class SelfReportException extends Exception
{
    public function __construct(string $message = "Unable to submit a complaint against yourself", int $code = 0, Throwable|null $previous = null)
    {
        return parent::__construct($message, $code, $previous);
    }
}
