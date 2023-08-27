<?php

declare(strict_types=1);

namespace Bachalang\Values;

use Bachalang\Context;
use Bachalang\Interpreter;
use Bachalang\Position;
use Bachalang\RuntimeResult;

class Func extends BaseFunc
{
    public function __construct(
        mixed $name,
        public $bodyNode,
        public $argNames,
        public bool $shouldAutoReturn = false,
        ?Position $posStart = null,
        ?Position $posEnd = null,
        ?Context $context = null
    ) {

        parent::__construct($name, $posStart, $posEnd, $context);
    }

    public function execute($args)
    {
        $result = new RuntimeResult();
        $execContext = $this->generateNewContext();
        $result->register($this->checkAndPopulateArgs($this->argNames, $args, $execContext));
        if($result->shouldReturn()) {
            return $result;
        }
        $value = $result->register(Interpreter::visit($this->bodyNode, $execContext));
        if($result->shouldReturn() && $result->funcReturnValue == null) {
            return $result;
        }
        if($this->shouldAutoReturn) {
            $returnValue = $value;
        } elseif($result->funcReturnValue) {
            $returnValue = $result->funcReturnValue;
        } else {
            $returnValue = Number::null();
        }
        return $result->success($returnValue);
    }
}
