<?php

declare(strict_types=1);

namespace Bachalang\Errors;

class InvalidSyntaxError extends Error
{
    protected string $errorName = 'Invalid Syntax';

    public function __construct($posStart, $posEnd, string $details)
    {
        parent::__construct($this->errorName, $posStart, $posEnd, $details);
    }
}
