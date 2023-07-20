<?php

declare(strict_types=1);

namespace Bachalang\Nodes;

use Bachalang\Token;

class VarAccessNode extends Node
{
    public function __construct(
        public Token $varNameToken
    ) {
        $this->posStart = $varNameToken->posStart;
        $this->posEnd = $varNameToken->posEnd;
    }

    public function __toString(): string
    {
        return "{$this->varNameToken}";
    }
}
