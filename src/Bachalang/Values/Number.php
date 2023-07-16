<?php

declare(strict_types=1);

namespace Bachalang\Values;

use Bachalang\Context;
use Bachalang\Errors\RuntimeError;
use Bachalang\Position;

class Number
{
    public function __construct(
        public int|float $value,
        public ?Position $posStart = null,
        public ?Position $posEnd = null,
        public ?Context $context = null
    ) {
        $this->setPosition();
        $this->setContext();
    }

    public function setPosition(?Position $posStart = null, ?Position $posEnd = null): self
    {
        $this->posStart = $posStart;
        $this->posEnd = $posEnd;
        return $this;
    }

    public function setContext(Context $context = null): self
    {
        $this->context = $context;
        return $this;
    }

    public function addedTo(Number $other): Number
    {
        return (new Number($this->value + $other->value))->setContext($this->context);
    }

    public function substractedBy(Number $other): Number
    {
        return (new Number($this->value - $other->value))->setContext($this->context);
    }

    public function multipliedBy(Number $other): Number
    {
        return (new Number($this->value * $other->value))->setContext($this->context);
    }

    public function dividedBy(Number $other): Number|RuntimeError
    {
        if($other->value == 0) {
            return new RuntimeError($other->posStart, $other->posEnd, 'Division by zero is not allowed', $this->context);
        } else {
            return (new Number($this->value / $other->value))->setContext($this->context);
        }
    }

    public function __toString(): string
    {
        return "{$this->value}";
    }
}
