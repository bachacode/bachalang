<?php

declare(strict_types=1);

namespace Bachalang\Nodes;

use Bachalang\Token;

class UnaryOpNode extends Node
{
    public function __construct(
        public Token $opToken,
        public Node $node
    ) {
    }

    public function __toString(): string
    {
        return "({$this->opToken}, {$this->node})";
    }
}
