<?php

declare(strict_types=1);

namespace Bachalang\Values;

use Bachalang\Context;
use Bachalang\Errors\RuntimeError;
use Bachalang\Position;

class StringVal extends Value
{
    public function __construct(
        public mixed $value,
        ?Position $posStart = null,
        ?Position $posEnd = null,
        ?Context $context = null,
    ) {
        parent::__construct($posStart, $posEnd, $context);
    }

    public function addedTo(Value $other): StringVal | RuntimeError
    {
        if($other instanceof Number || $other instanceof StringVal) {
            return (new StringVal($this->value . $other->value))->setContext($this->context);
        } else {
            return $this->illegalOperation($other);
        }
    }

    public function getComparisonEq(Value $other): Number|RuntimeError
    {
        if($other instanceof Number || $other instanceof StringVal) {
            return (new Number($this->value == $other->value ? 1 : 0))->setContext($this->context);
        } else {
            return $this->illegalOperation($other);
        }
    }

    public function getComparisonNe(Value $other): Number|RuntimeError
    {
        if($other instanceof Number || $other instanceof StringVal) {
            return (new Number($this->value != $other->value ? 1 : 0))->setContext($this->context);
        } else {
            return $this->illegalOperation($other);
        }
    }

    public function andWith(Value $other): Number|RuntimeError
    {
        if($other instanceof Number || $other instanceof StringVal) {
            return (new Number($this->value && $other->value ? 1 : 0))->setContext($this->context);
        } else {
            return $this->illegalOperation($other);
        }
    }

    public function orWith(Value $other): Number|RuntimeError
    {
        if($other instanceof Number || $other instanceof StringVal) {
            return (new Number($this->value || $other->value ? 1 : 0))->setContext($this->context);
        } else {
            return $this->illegalOperation($other);
        }
    }

    public function invert(): Number|RuntimeError
    {
        return (new Number(strlen($this->value) != 0 ? 0 : 1))->setContext($this->context);
    }

    public function isTrue(): bool|int
    {
        return strlen($this->value) != 0 ? 1 : 0;
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
}
