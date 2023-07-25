<?php

declare(strict_types=1);

namespace Bachalang\Values;

use Bachalang\Context;
use Bachalang\Errors\RuntimeError;
use Bachalang\Position;

class Number
{
    public function __construct(
        public int|float|bool $value,
        private ?Position $posStart = null,
        private ?Position $posEnd = null,
        private ?Context $context = null
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

    public function powBy(Number $other): Number|RuntimeError
    {
        return (new Number($this->value ** $other->value))->setContext($this->context);
    }

    public function getComparisonEq(Number $other): Number|RuntimeError
    {
        return (new Number($this->value == $other->value ? 1 : 0))->setContext($this->context);
    }

    public function getComparisonNe(Number $other): Number|RuntimeError
    {
        return (new Number($this->value != $other->value ? 1 : 0))->setContext($this->context);
    }

    public function getComparisonLt(Number $other): Number|RuntimeError
    {
        return (new Number($this->value < $other->value ? 1 : 0))->setContext($this->context);
    }

    public function getComparisonGt(Number $other): Number|RuntimeError
    {
        return (new Number($this->value > $other->value ? 1 : 0))->setContext($this->context);
    }

    public function getComparisonLte(Number $other): Number|RuntimeError
    {
        return (new Number($this->value <= $other->value ? 1 : 0))->setContext($this->context);
    }

    public function getComparisonGte(Number $other): Number|RuntimeError
    {
        return (new Number($this->value >= $other->value ? 1 : 0))->setContext($this->context);
    }

    public function andWith(Number $other): Number|RuntimeError
    {
        return (new Number($this->value && $other->value ? 1 : 0))->setContext($this->context);
    }

    public function orWith(Number $other): Number|RuntimeError
    {
        return (new Number($this->value || $other->value ? 1 : 0))->setContext($this->context);
    }

    public function invert(): Number|RuntimeError
    {
        return (new Number($this->value ? 0 : 1))->setContext($this->context);
    }

    public function isTrue(): bool|int
    {
        return $this->value != 0 ? 1 : 0;
    }

    public function __toString(): string
    {
        return "{$this->value}";
    }

    public function __get($nombrePropiedad)
    {
        if ($nombrePropiedad === "propiedad") {
            return $this->propiedad;
        }
    }

    public function copy(): Number
    {
        $copy = new Number($this->value);
        $copy->setPosition($this->posStart, $this->posEnd);
        $copy->setContext($this->context);
        return $copy;
    }
}
