<?php

namespace App\Services\Ghl;

class GhlException extends \RuntimeException
{
    public function __construct(string $message, public readonly int $status = 0)
    {
        parent::__construct($message, $status);
    }

    /** Client errors (bad data, bad token, missing scope) will not fix themselves, so do not retry. 429 and 5xx do. */
    public function retriable(): bool
    {
        return $this->status === 0 || $this->status === 429 || $this->status >= 500;
    }
}
