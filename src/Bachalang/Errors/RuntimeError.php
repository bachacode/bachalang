<?php

declare(strict_types=1);

namespace Bachalang\Errors;

class RuntimeError extends Error
{
    protected string $errorName = 'Runtime Error';

    public function __construct($posStart, $posEnd, string $details)
    {
        parent::__construct($this->errorName, $posStart, $posEnd, $details);
    }
}
