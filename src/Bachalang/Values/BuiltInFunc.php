<?php

declare(strict_types=1);

namespace Bachalang\Values;

use Bachalang\Context;
use Bachalang\Errors\RuntimeError;
use Bachalang\Interpreter;
use Bachalang\Position;
use Bachalang\RuntimeResult;

class BuiltInFunc extends BaseFunc
{
    public function __construct(
        mixed $name,
        ?Position $posStart = null,
        ?Position $posEnd = null,
        ?Context $context = null,
        readonly array $print = ['value'],
        readonly array $print_return = ['value'],
        readonly array $input = [],
        readonly array $input_int = [],
        readonly array $clear = [],
        readonly array $is_number = ['value'],
        readonly array $is_array = ['value'],
        readonly array $is_string = ['value'],
        readonly array $is_function = ['value'],
        readonly array $append = ['array', 'value'],
        readonly array $pop = ['array', 'index'],
        readonly array $extend = ['array', 'secondArray'],
    ) {

        parent::__construct($name, $posStart, $posEnd, $context);
    }

    public function execute($args): RuntimeResult
    {
        $result = new RuntimeResult();
        $execContext = $this->generateNewContext();
        $methodName = "execute_{$this->name}";


        $result->register($this->checkAndPopulateArgs($this->{"$this->name"}, $args, $execContext));
        if(!is_null($result->error)) {
            return $result;
        }

        if(method_exists($this, $methodName)) {
            $returnValue = $this->$methodName($execContext);
        } else {
            $this->noExecuteMethod($methodName, $execContext);
        }
        if(!is_null($result->error)) {
            return $result;
        }

        return $result->success($returnValue->result);
    }

    private function noExecuteMethod($methodName)
    {
        throw new \Exception("Method: {$methodName} is not defined");
    }

    private function execute_print(Context $execContext): RuntimeResult
    {
        print((string)$execContext->symbolTable->get('value') . PHP_EOL);
        return (new RuntimeResult())->success(new Number(Number::NULL));
    }

    private function execute_print_return(Context $execContext): RuntimeResult
    {
        return (new RuntimeResult())->success(new StringVal($execContext->symbolTable->get('value')));
    }

    private function execute_input(Context $execContext): RuntimeResult
    {
        $text = readline();
        return (new RuntimeResult())->success(new StringVal((string) $text));
    }

    private function execute_input_int(Context $execContext): RuntimeResult
    {
        while (true) {
            $text = readline();
            $number = 0;
            try {
                $number = (int) $text;
                break;
            } catch (\Throwable $th) {
                print("{$text} must be an integer. Try again!" . PHP_EOL);
            }
        }
        return (new RuntimeResult())->success(new Number($number));
    }

    private function execute_clear(Context $execContext): RuntimeResult
    {
        echo chr(27).chr(91).'H'.chr(27).chr(91).'J'; // ^[H^[J
        return (new RuntimeResult())->success(new Number(Number::NULL));
    }

    private function execute_is_number(Context $execContext): RuntimeResult
    {
        $condition = $execContext->symbolTable->get('value') instanceof Number ? new Number(Number::TRUE) : new Number(Number::FALSE);

        return (new RuntimeResult())->success($condition);
    }

    private function execute_is_string(Context $execContext): RuntimeResult
    {
        $condition = $execContext->symbolTable->get('value') instanceof StringVal ? new Number(Number::TRUE) : new Number(Number::FALSE);

        return (new RuntimeResult())->success($condition);
    }

    private function execute_is_array(Context $execContext): RuntimeResult
    {
        $condition = $execContext->symbolTable->get('value') instanceof ArrayVal ? new Number(Number::TRUE) : new Number(Number::FALSE);

        return (new RuntimeResult())->success($condition);
    }

    private function execute_is_function(Context $execContext): RuntimeResult
    {
        $condition = $execContext->symbolTable->get('value') instanceof BaseFunc ? new Number(Number::TRUE) : new Number(Number::FALSE);

        return (new RuntimeResult())->success($condition);
    }

    private function execute_append(Context $execContext): RuntimeResult
    {
        $result = new RuntimeResult();
        $array = $execContext->symbolTable->get('array');
        $value = $execContext->symbolTable->get('value');

        if(!$array instanceof ArrayVal) {
            return $result->failure(
                new RuntimeError(
                    $this->posStart,
                    $this->posEnd,
                    "First argument must be an array",
                    $execContext
                )
            );
        }

        $array->elements[] = $value;
        return $result->success(new Number(Number::NULL));
    }

    private function execute_pop(Context $execContext): RuntimeResult
    {
        $result = new RuntimeResult();
        $array = $execContext->symbolTable->get('array');
        $index = $execContext->symbolTable->get('index');

        if(!$array instanceof ArrayVal) {
            return $result->failure(
                new RuntimeError(
                    $this->posStart,
                    $this->posEnd,
                    "First argument must be an array",
                    $execContext
                )
            );
        }

        if(!$index instanceof Number) {
            return $result->failure(
                new RuntimeError(
                    $this->posStart,
                    $this->posEnd,
                    "second argument must be an int",
                    $execContext
                )
            );
        }

        try {
            $element = $array->elements[$index->value];
            unset($array->elements[$index->value]);
        } catch (\Throwable $th) {
            return $result->failure(
                new RuntimeError(
                    $this->posStart,
                    $this->posEnd,
                    'Element at this index could not be removed from array because index is out of bound',
                    $execContext
                )
            );
        }
        return $result->success($element);
    }

    private function execute_extend(Context $execContext): RuntimeResult
    {
        $result = new RuntimeResult();
        $array = $execContext->symbolTable->get('array');
        $secondArray = $execContext->symbolTable->get('secondArray');

        if(!$array instanceof ArrayVal) {
            return $result->failure(
                new RuntimeError(
                    $this->posStart,
                    $this->posEnd,
                    "First argument must be an array",
                    $execContext
                )
            );
        }

        if(!$secondArray instanceof ArrayVal) {
            return $result->failure(
                new RuntimeError(
                    $this->posStart,
                    $this->posEnd,
                    "Second argument must be an array",
                    $execContext
                )
            );
        }

        $array->elements = array_merge($array->elements, $secondArray->elements);
        return $result->success(new Number(Number::NULL));
    }

    public function copy(): BuiltInFunc
    {
        $copy = new BuiltInFunc($this->name);
        $copy->setPosition($this->posStart, $this->posEnd);
        $copy->setContext($this->context);
        return $copy;
    }

    public function __toString(): string
    {
        return "<Built-In Function {$this->name}>";
    }
}
