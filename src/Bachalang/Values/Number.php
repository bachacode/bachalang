<?php

declare(strict_types=1);

namespace Bachalang\Values;

use Bachalang\Context;
use Bachalang\Errors\RuntimeError;
use Bachalang\Position;

class Number extends Value
{
    public function __construct(
        public mixed $value,
        ?Position $posStart = null,
        ?Position $posEnd = null,
        ?Context $context = null,
    ) {
        parent::__construct($posStart, $posEnd, $context);
    }

    public function addedTo(Value $other): Number | RuntimeError
    {
        if($other instanceof Number) {
            return (new Number($this->value + $other->value))->setContext($this->context);
        } else {
            return $this->illegalOperation($other);
        }
    }

    public function substractedBy(Value $other): Number | RuntimeError
    {
        if($other instanceof Number) {
            return (new Number($this->value - $other->value))->setContext($this->context);
        } else {
            return $this->illegalOperation($other);
        }
    }

    public function multipliedBy(Value $other): Number | RuntimeError
    {
        if($other instanceof Number) {
            return (new Number($this->value * $other->value))->setContext($this->context);
        } else {
            return $this->illegalOperation($other);
        }
    }

    public function dividedBy(Value $other): Number|RuntimeError
    {

        if($other instanceof Number) {
            if($other->value == 0) {
                return new RuntimeError($other->posStart, $other->posEnd, 'Division by zero is not allowed', $this->context);
            } else {
                return (new Number($this->value / $other->value))->setContext($this->context);
            }
        } else {
            return $this->illegalOperation($other);
        }
    }

    public function powBy(Value $other): Number|RuntimeError
    {
        if($other instanceof Number) {
            return (new Number($this->value ** $other->value))->setContext($this->context);
        } else {
            return $this->illegalOperation($other);
        }
    }

    public function getComparisonEq(Value $other): Number|RuntimeError
    {
        if($other instanceof Number) {
            return (new Number($this->value == $other->value ? 1 : 0))->setContext($this->context);
        } else {
            return $this->illegalOperation($other);
        }
    }

    public function getComparisonNe(Value $other): Number|RuntimeError
    {
        if($other instanceof Number) {
            return (new Number($this->value != $other->value ? 1 : 0))->setContext($this->context);
        } else {
            return $this->illegalOperation($other);
        }
    }

    public function getComparisonLt(Value $other): Number|RuntimeError
    {
        if($other instanceof Number) {
            return (new Number($this->value < $other->value ? 1 : 0))->setContext($this->context);
        } else {
            return $this->illegalOperation($other);
        }
    }

    public function getComparisonGt(Value $other): Number|RuntimeError
    {
        if($other instanceof Number) {
            return (new Number($this->value > $other->value ? 1 : 0))->setContext($this->context);
        } else {
            return $this->illegalOperation($other);
        }
    }

    public function getComparisonLte(Value $other): Number|RuntimeError
    {
        if($other instanceof Number) {
            return (new Number($this->value <= $other->value ? 1 : 0))->setContext($this->context);
        } else {
            return $this->illegalOperation($other);
        }
    }

    public function getComparisonGte(Value $other): Number|RuntimeError
    {
        if($other instanceof Number) {
            return (new Number($this->value >= $other->value ? 1 : 0))->setContext($this->context);
        } else {
            return $this->illegalOperation($other);
        }
    }

    public function andWith(Value $other): Number|RuntimeError
    {
        if($other instanceof Number) {
            return (new Number($this->value && $other->value ? 1 : 0))->setContext($this->context);
        } else {
            return $this->illegalOperation($other);
        }
    }

    public function orWith(Value $other): Number|RuntimeError
    {
        if($other instanceof Number) {
            return (new Number($this->value || $other->value ? 1 : 0))->setContext($this->context);
        } else {
            return $this->illegalOperation($other);
        }
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
