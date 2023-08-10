<?php

declare(strict_types=1);

namespace Bachalang\Values;

use Bachalang\Context;
use Bachalang\Errors\RuntimeError;
use Bachalang\Position;

class ArrayVal extends Value
{
    public function __construct(
        public array $elements,
        ?Position $posStart = null,
        ?Position $posEnd = null,
        ?Context $context = null
    ) {
        parent::__construct($posStart, $posEnd, $context);
    }

    public function addedTo(Value $other): ArrayVal
    {
        $newList = &$this;
        array_push($newList->elements, $other);
        return $newList;
    }

    public function substractedBy(Value $other): ArrayVal|RuntimeError
    {
        if($other instanceof Number) {
            $newList = clone $this;
            try {
                unset($newList[$other]);
                return $newList;
            } catch (\Throwable $e) {
                return new RuntimeError(
                    $other->posStart,
                    $other->posEnd,
                    'Element at this index could not be removed from array because index is out of bound',
                    $this->context
                );
            }
        } else {
            return $this->illegalOperation($other);
        }
    }

    public function __toString(): string
    {
        $string = '[';
        foreach ($this->elements as $key => $element) {
            $string .= !($key + 1 == count($this->elements)) ? $element . ',' : $element;
        }
        $string .= ']';
        return $string;
    }
}
