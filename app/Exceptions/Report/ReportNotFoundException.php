<?php

namespace App\Exceptions\Report;

use Exception;
use Throwable;

class ReportNotFoundException extends Exception
{
    public function __construct(string $message = "Report not found", int $code = 0, Throwable|null $previous = null)
    {
        return parent::__construct($message, $code, $previous);
    }
}
