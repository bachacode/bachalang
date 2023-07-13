<?php

declare(strict_types=1);

namespace Bachalang;

use Bachalang\Nodes\BinOpNode;
use Bachalang\Nodes\Node;
use Bachalang\Nodes\NumberNode;
use Bachalang\Nodes\UnaryOpNode;
use Bachalang\Values\Number;
use Exception;

class Interpreter
{
    public function visit(Node $node): Number
    {
        $methodName = "visit";
        $methodName .= basename(get_class($node));
        if(method_exists($this, $methodName)) {
            return call_user_func_array([$this, $methodName], [$node]);
        } else {
            return $this->noVisitMethod($methodName);
        }
    }

    private function noVisitMethod($methodName)
    {
        throw new Exception("Method: {$methodName} is not defined");
    }

    private function visitNumberNode(NumberNode $node): Number
    {
        return (new Number($node->token->value))
        ->setPosition($node->posStart, $node->posEnd);
    }

    private function visitBinOpNode(BinOpNode $node): Number
    {
        $left = $this->visit($node->leftNode);
        $right = $this->visit($node->rightNode);
        if($node->opNode == TT::PLUS->value) {
            $result = $left->addedTo($right);
        } elseif($node->opNode == TT::MINUS->value) {
            $result = $left->substractedBy($right);
        } elseif($node->opNode == TT::MUL->value) {
            $result = $left->multipliedBy($right);
        } elseif($node->opNode == TT::DIV->value) {
            $result = $left->dividedBy($right);
        }
        return $result->setPosition($node->posStart, $node->posEnd);
    }

    private function visitUnaryOpNode(UnaryOpNode $node): Number
    {
        $number = $this->visit($node->node);
        if($node->opToken == TT::MINUS->value) {
            $number = $number->multipliedBy(new Number(-1));
        }
        return $number->setPosition($node->posStart, $node->posEnd);
    }
}
