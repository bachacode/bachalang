<?php

declare(strict_types=1);

namespace Bachalang;

use Bachalang\Errors\RuntimeError;
use Bachalang\Nodes\BinOpNode;
use Bachalang\Nodes\Node;
use Bachalang\Nodes\NumberNode;
use Bachalang\Nodes\UnaryOpNode;
use Bachalang\Values\Number;
use Exception;

class Interpreter
{
    public function visit(Node $node): RuntimeError|RuntimeResult
    {
        $methodName = "visit";
        $methodName .= basename(get_class($node));
        if(method_exists($this, $methodName)) {
            return call_user_func_array([$this, $methodName], [$node]);
        } else {
            $this->noVisitMethod($methodName);
        }
    }

    private function noVisitMethod(string $methodName): never
    {
        throw new Exception("Method: {$methodName} is not defined");
    }

    private function visitNumberNode(NumberNode $node): RuntimeResult
    {
        return (new RuntimeResult())->success(
            (new Number($node->token->value))
        ->setPosition($node->posStart, $node->posEnd)
        );
    }

    private function visitBinOpNode(BinOpNode $node): RuntimeError|RuntimeResult
    {
        $response = new RuntimeResult();
        $left = $response->register($this->visit($node->leftNode));
        if($response->error != null) {
            return $response;
        }
        $right = $response->register($this->visit($node->rightNode));

        if($node->opNode == TT::PLUS->value) {
            $result = $left->addedTo($right);
        } elseif($node->opNode == TT::MINUS->value) {
            $result = $left->substractedBy($right);
        } elseif($node->opNode == TT::MUL->value) {
            $result = $left->multipliedBy($right);
        } elseif($node->opNode == TT::DIV->value) {
            $result = $left->dividedBy($right);
        }
        if($result instanceof RuntimeError) {
            return $response->failure($result);
        } else {
            return $response->success($result->setPosition($node->posStart, $node->posEnd));
        }
    }

    private function visitUnaryOpNode(UnaryOpNode $node): RuntimeError|RuntimeResult
    {
        $response = new RuntimeResult();
        $number = $response->register($this->visit($node->node));
        if($response->error != null) {
            return $response;
        }

        if($node->opToken == TT::MINUS->value) {
            $number = $number->multipliedBy(new Number(-1));
        }
        if($number instanceof RuntimeError) {
            return $response->failure($number);
        } else {
            return $number->setPosition($node->posStart, $node->posEnd);
        }
    }
}
