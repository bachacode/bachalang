<?php

declare(strict_types=1);

namespace Bachalang\Nodes;

use Bachalang\Token;

class BinOpNode extends Node
{
    public function __construct(
        public Node $leftNode,
        public Token $opNode,
        public Node $rightNode
    ) {
    }

    public function __toString(): string
    {
        return "({$this->leftNode}, {$this->opNode}, {$this->rightNode})";
    }
}
