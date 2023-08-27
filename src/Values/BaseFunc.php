<?php

declare(strict_types=1);

namespace Bachalang\Values;

use Bachalang\Context;
use Bachalang\Errors\RuntimeError;
use Bachalang\Position;
use Bachalang\RuntimeResult;
use Bachalang\SymbolTable;

class BaseFunc extends Value
{
    public function __construct(
        public mixed $name,
        ?Position $posStart = null,
        ?Position $posEnd = null,
        ?Context $context = null
    ) {
        if(is_null($name)) {
            $this->name = '<anonymous>';
        }
        parent::__construct($posStart, $posEnd, $context);
    }

    public function generateNewContext(): Context
    {
        $newContext = new Context($this->name, $this->context, $this->posStart);
        $newContext->symbolTable = new SymbolTable([], $newContext->parent->symbolTable);

        return $newContext;
    }

    public function generateReferenceContext(): Context
    {
        $newContext = new Context($this->name, $this->context, $this->posStart);
        $symbolTable = &$newContext->parent->symbolTable;
        $newContext->symbolTable = new SymbolTable([], $symbolTable);

        return $newContext;
    }

    public function checkArgs($argNames, &$args)
    {
        $result = new RuntimeResult();
        if(count($args) > count($argNames)) {
            $tooMany = count($args) - count($argNames);
            return $result->failure(new RuntimeError(
                $this->posStart,
                $this->posEnd,
                "{$tooMany} too many arguments passed into {$this->name}",
                $this->context
            ));
        }
        if(count($args) < count($argNames)) {
            $tooFew = count($argNames) - count($args);
            return $result->failure(new RuntimeError(
                $this->posStart,
                $this->posEnd,
                "{$tooFew} too few arguments passed into {$this->name}",
                $this->context
            ));
        }

        return $result->success(Number::null());
    }

    public function populateArgs($argNames, &$args, $execContext)
    {
        $result = new RuntimeResult();
        foreach ($args as $key => $value) {
            $argName = $argNames[$key];
            $argValue = $value;
            $argValue->setContext($execContext);
            $execContext->symbolTable->set($argName, $argValue);
        }
        return $result->success(Number::null());
    }

    public function checkAndPopulateArgs($argNames, &$args, $execContext)
    {
        $result = new RuntimeResult();
        $result->register($this->checkArgs($argNames, $args));
        if(!is_null($result->error)) {
            return $result;
        }
        $result->register($this->populateArgs($argNames, $args, $execContext));
        if(!is_null($result->error)) {
            return $result;
        }

        return $result->success(Number::null());
    }

    public function __toString(): string
    {
        return "<Function {$this->name}>";
    }

}
