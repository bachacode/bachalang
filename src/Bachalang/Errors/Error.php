<?php

declare(strict_types=1);

namespace Bachalang\Errors;

use Bachalang\Position;

use Bachalang\Helpers\StringHelper;

class Error
{
    public function __construct(
        protected string $errorName,
        protected Position $posStart,
        protected ?Position $posEnd,
        protected string $details
    ) {
    }

    public function __toString(): string
    {
        $result = "{$this->errorName}: {$this->details}" . PHP_EOL;
        $lineNumber = $this->posStart->line + 1;
        $result = "{$result}File {$this->posStart->fn}, line {$lineNumber}";
        $stringWithArrows = StringHelper::stringWithArrows($this->posStart->ftxt, $this->posStart, $this->posEnd);
        $result = "{$result}\n\n $stringWithArrows";
        return $result;
    }
}
