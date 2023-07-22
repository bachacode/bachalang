<?php

declare(strict_types=1);

namespace Bachalang\Errors;

class ExpectedCharError extends Error
{
    protected string $errorName = 'Expected Character';

    public function __construct($posStart, $posEnd, string $details)
    {
        parent::__construct($this->errorName, $posStart, $posEnd, $details);
    }
}
