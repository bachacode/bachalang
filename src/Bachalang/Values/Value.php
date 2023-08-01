<?php

declare(strict_types=1);

namespace Bachalang\Values;

use Bachalang\Context;
use Bachalang\Errors\RuntimeError;
use Bachalang\Position;
use Exception;

class Value
{
    public function __construct(
        protected ?Position $posStart = null,
        protected ?Position $posEnd = null,
        protected ?Context $context = null
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

    public function addedTo(Value $other): Value | RuntimeError
    {
        return $this->illegalOperation($other);
    }

    public function substractedBy(Value $other): Value | RuntimeError
    {
        return $this->illegalOperation($other);
    }

    public function multipliedBy(Value $other): Value | RuntimeError
    {
        return $this->illegalOperation($other);
    }

    public function dividedBy(Value $other): Value|RuntimeError
    {
        return $this->illegalOperation($other);
    }

    public function powBy(Value $other): Value|RuntimeError
    {
        return $this->illegalOperation($other);
    }

    public function getComparisonEq(Value $other): Value|RuntimeError
    {
        return $this->illegalOperation($other);
    }

    public function getComparisonNe(Value $other): Value|RuntimeError
    {
        return $this->illegalOperation($other);
    }

    public function getComparisonLt(Value $other): Value|RuntimeError
    {
        return $this->illegalOperation($other);
    }

    public function getComparisonGt(Value $other): Value|RuntimeError
    {
        return $this->illegalOperation($other);
    }

    public function getComparisonLte(Value $other): Value|RuntimeError
    {
        return $this->illegalOperation($other);
    }

    public function getComparisonGte(Value $other): Value|RuntimeError
    {
        return $this->illegalOperation($other);
    }

    public function andWith(Value $other): Value|RuntimeError
    {
        return $this->illegalOperation($other);
    }

    public function orWith(Value $other): Value|RuntimeError
    {
        return $this->illegalOperation($other);
    }

    public function invert(): Value|RuntimeError
    {
        return $this->illegalOperation();
    }

    public function isTrue(): bool|int
    {
        return false;
    }
    
    protected function illegalOperation(Value $other = null)
    {
        return (new RuntimeError($this->posStart, $other->posEnd, 'Illegal Operation', $this->context));
    }

    public function copy()
    {
        throw new Exception('No copy method defined');
    }
}
