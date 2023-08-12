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
        public int $advanceCount = 0
    ) {
    }

    public function register(ParseResult $res)
    {
        $this->advanceCount += $res->advanceCount;
        if($res->error != null) {
            $this->error = $res->error;
            return $res->error;
        }
        return $res->node;
    }

    public function registerAdvancement()
    {
        $this->advanceCount++;
    }

    public function success($node)
    {
        $this->node = $node;
        return $this;
    }

    public function failure($error)
    {
        if($this->error == null || $this->advanceCount == 0) {
            $this->error = $error;
        }
        return $this;
    }
}
