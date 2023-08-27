<?php

declare(strict_types=1);

namespace Bachalang\Nodes;

use Bachalang\Position;

class ContinueNode extends Node
{
    public function __construct(
        Position $posStart,
        Position $posEnd
    ) {
        $this->posStart = $posStart;
        $this->posEnd = $posEnd;
    }
}
