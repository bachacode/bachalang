<?php

declare(strict_types=1);

namespace Bachalang;

use Bachalang\Errors\InvalidSyntaxError;
use Bachalang\Nodes\Node;

class ParseResult
{
    public function __construct(
        public ?InvalidSyntaxError $error = null,
        public ?Node $node = null,
        public int $lastRegisteredAdvanceCount = 0,
        public int $advanceCount = 0,
        public int $toReverseCount = 0
    ) {
    }

    public function register(ParseResult $res): ?Node
    {
        $this->lastRegisteredAdvanceCount = $res->advanceCount;
        $this->advanceCount += $res->advanceCount;
        if($res->error != null) {
            $this->error = $res->error;
        }
        return $res->node;
    }

    public function tryRegister(ParseResult $res): ?Node
    {
        if($res->error != null) {
            $this->toReverseCount = $res->advanceCount;
            return null;
        }
        return $this->register($res);
    }

    public function registerAdvancement(): void
    {
        $this->lastRegisteredAdvanceCount = 1;
        $this->advanceCount++;
    }

    public function success(?Node $node): static
    {
        $this->node = $node;
        return $this;
    }

    public function failure(InvalidSyntaxError $error): static
    {
        if($this->error == null || $this->advanceCount == 0) {
            $this->error = $error;
        }
        return $this;
    }
}
