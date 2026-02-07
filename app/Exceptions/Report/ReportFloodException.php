<?php

namespace App\Exceptions\Report;

use Exception;
use Throwable;

class ReportFloodException extends Exception
{
    public function __construct(string $message = "This user has already been reported within the last 24 hours.", int $code = 0, Throwable|null $previous = null)
    {
        return parent::__construct($message, $code, $previous);
    }
}
