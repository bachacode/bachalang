<?php

declare(strict_types=1);

namespace Bachalang\Errors;

use Bachalang\Context;
use Bachalang\Helpers\StringHelper;

class RuntimeError extends Error
{
    protected string $errorName = 'Runtime Error';

    public function __construct($posStart, $posEnd, string $details, public Context $context)
    {
        parent::__construct($this->errorName, $posStart, $posEnd, $details);
    }

    public function __toString(): string
    {
        $result = $this->generateTraceback();
        $result .= "{$this->errorName}: {$this->details}" . PHP_EOL;
        $stringWithArrows = StringHelper::stringWithArrows($this->posStart->ftxt, $this->posStart, $this->posEnd);
        $result .= "\n $stringWithArrows";
        return $result;
    }

    public function generateTraceback()
    {
        $result = '';
        $pos = $this->posStart;
        $context = $this->context;
        $lineNumber = $pos->line + 1;

        while($context != null) {
            $result = " File {$pos->fn}, line {$lineNumber}, {$context->displayName}\n";
            $pos = $context->parentEntryPos;
            $context = $context->parent;
        }

        return "Traceback (most recent call last): \n" . $result;
    }
}
