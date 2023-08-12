<?php

declare(strict_types=1);

namespace Bachalang\Nodes;

use Bachalang\Token;

class VarAssignNode extends Node
{
    public function __construct(
        public Token $varNameToken,
        public Node $valueNode
    ) {
        $this->posStart = $varNameToken->posStart;
        $this->posEnd = $valueNode->posEnd;
    }

    public function __toString(): string
    {
        return "{$this->varNameToken}";
    }
}
