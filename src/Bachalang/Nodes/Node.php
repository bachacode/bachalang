<?php

declare(strict_types=1);

namespace Bachalang\Nodes;

use Bachalang\Position;

abstract class Node
{
    public ?Position $posStart;
    public ?Position $posEnd;
}
