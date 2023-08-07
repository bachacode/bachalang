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
        if(!is_null($result->error)) {
            return $result;
        }
        $value = $result->register(Interpreter::visit($this->bodyNode, $execContext));
        if(!is_null($result->error)) {
            return $result;
        }

        return $result->success($value);
    }

    public function copy(): Func
    {
        $copy = new Func($this->name, $this->bodyNode, $this->argNames);
        $copy->setPosition($this->posStart, $this->posEnd);
        $copy->setContext($this->context);
        return $copy;
    }
}
