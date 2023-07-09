<?php

declare(strict_types=1);

namespace Bachalang;

use Bachalang\Position;

class Error
{
    public function __construct(
        protected string $errorName,
        protected Position $posStart,
        protected Position $posEnd,
        protected string $details
    ) {
    }

    public function __toString()
    {
        $result = $this->errorName . ': ' . $this->details . PHP_EOL;
        $result = $result . "File " . $this->posStart->fn . ', line ' . $this->posStart->line + 1;
        return $result;
    }
}
