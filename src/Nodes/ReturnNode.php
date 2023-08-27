<?php

declare(strict_types=1);

namespace Bachalang\Nodes;

use Bachalang\Position;

class ReturnNode extends Node
{
    public function __construct(
        public ?Node $nodeToReturn,
        Position $posStart,
        Position $posEnd
    ) {
        $this->posStart = $posStart;
        $this->posEnd = $posEnd;
    }
}
