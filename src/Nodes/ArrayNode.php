<?php

declare(strict_types=1);

namespace Bachalang\Nodes;

use Bachalang\Position;

class ArrayNode extends Node
{
    public function __construct(
        public array $elementNodes,
        public ?Position $posStart = null,
        public ?Position $posEnd = null,
    ) {
    }
}
