<?php

declare(strict_types=1);

namespace Bachalang\Values;

use Bachalang\Position;

class Number
{
    public function __construct(
        public int $value,
        public ?Position $posStart = null,
        public ?Position $posEnd = null
    ) {
        $this->setPosition();
    }

    public function setPosition(?Position $posStart = null, ?Position $posEnd = null)
    {
        $this->posStart = $posStart;
        $this->posEnd = $posEnd;
        return $this;
    }

    public function addedTo(Number $other): Number
    {
        return new Number($this->value + $other->value);
    }

    public function substractedBy(Number $other): Number
    {
        return new Number($this->value - $other->value);
    }

    public function multipliedBy(Number $other): Number
    {
        return new Number($this->value * $other->value);
    }

    public function dividedBy(Number $other): Number
    {
        return new Number($this->value / $other->value);
    }

    public function __toString(): string
    {
        return "{$this->value}";
    }
}
