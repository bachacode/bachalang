<?php

declare(strict_types=1);

namespace Bachalang;

use Bachalang\Error;

class IllegalCharError extends Error
{
    protected string $errorName = 'Illegal Character';

    public function __construct($posStart, $posEnd, string $details)
    {
        parent::__construct($this->errorName, $posStart, $posEnd, $details);
    }
}
