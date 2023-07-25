<?php

declare(strict_types=1);

namespace Bachalang\Nodes;

class WhileNode extends Node
{
    public function __construct(
        public Node $conditionNode,
        public Node $bodyNode
    ) {
        $this->posStart = $conditionNode->posStart;
        $this->posEnd = $bodyNode->posEnd;
    }
}
