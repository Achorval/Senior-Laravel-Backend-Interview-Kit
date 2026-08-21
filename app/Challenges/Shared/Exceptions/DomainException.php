<?php

namespace App\Challenges\Shared\Exceptions;

use Exception;

class DomainException extends Exception
{
    public function __construct(
        public readonly string $errorCode,
        string $message,
        public readonly int $status = 400,
    ) {
        parent::__construct($message);
    }
}
