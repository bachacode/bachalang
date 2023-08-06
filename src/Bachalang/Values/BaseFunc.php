<?php

declare(strict_types=1);

namespace Bachalang\Values;

use Bachalang\Context;
use Bachalang\Errors\RuntimeError;
use Bachalang\Interpreter;
use Bachalang\Position;
use Bachalang\RuntimeResult;
use Bachalang\SymbolTable;

class BaseFunc extends Value
{
    public function __construct(
        public mixed $name,
        public $bodyNode,
        public $argNames,
        ?Position $posStart = null,
        ?Position $posEnd = null,
        ?Context $context = null
    ) {
        if(is_null($name)) {
            $this->name = '<anonymous>';
        }
        parent::__construct($posStart, $posEnd, $context);
    }

    public function execute($args)
    {
        $result = new RuntimeResult();
        $newContext = new Context($this->name, $this->context, $this->posStart);
        $newContext->symbolTable = new SymbolTable([], $newContext->parent->symbolTable);

        if(count($args) > count($this->argNames)) {
            $tooMany = count($args) - count($this->argNames);
            return (new RuntimeError(
                $this->posStart,
                $this->posEnd,
                "{$tooMany} too many arguments passed into {$this->name}",
                $this->context
            ));
        }
        if(count($args) < count($this->argNames)) {
            $tooFew = count($this->argNames) - count($args);
            return (new RuntimeError(
                $this->posStart,
                $this->posEnd,
                "{$tooFew} too few arguments passed into {$this->name}",
                $this->context
            ));
        }

        foreach ($args as $key => $value) {
            $argName = $this->argNames[$key];
            $argValue = $value;
            $argValue->setContext($newContext);
            $newContext->symbolTable->set($argName, $argValue);
        }

        $value = $result->register(Interpreter::visit($this->bodyNode, $newContext));
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

    public function __toString(): string
    {
        return "<Function {$this->name}>";
    }

}
