<?php

declare(strict_types=1);

namespace Bachalang\Nodes;

use Bachalang\Token;

class NumberNode extends Node
{
    public function __construct(
        public Token $token
    ) {
        $this->posStart = $token->posStart;
        $this->posEnd = $token->posEnd;
    }

    public function __toString(): string
    {
        return "{$this->token}";
    }
}
