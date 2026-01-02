<?php

namespace App\Exceptions;

use Exception;

class InvalidEmailException extends Exception
{
    protected $details;

    public function __construct(array $details = [], string $message = 'Invalid email', int $code = 0, Exception $previous = null)
    {
        $this->details = $details;
        parent::__construct($message, $code, $previous);
    }

    public function getDetails(): array
    {
        return $this->details;
    }
}