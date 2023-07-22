<?php

declare(strict_types=1);

namespace Bachalang;

use Bachalang\Errors\RuntimeError;
use Bachalang\Nodes\BinOpNode;
use Bachalang\Nodes\IfNode;
use Bachalang\Nodes\Node;
use Bachalang\Nodes\NumberNode;
use Bachalang\Nodes\UnaryOpNode;
use Bachalang\Nodes\VarAccessNode;
use Bachalang\Nodes\VarAssignNode;
use Bachalang\Values\Number;
use Exception;

class Interpreter
{
    public function visit(Node $node, Context $context): RuntimeError|RuntimeResult
    {
        $methodName = "visit";
        $methodName .= basename(get_class($node));
        if(method_exists($this, $methodName)) {
            return call_user_func_array([$this, $methodName], [$node, $context]);
        } else {
            $this->noVisitMethod($methodName, $context);
        }
    }

    private function noVisitMethod(string $methodName, Context $context): never
    {
        throw new Exception("Method: {$methodName} is not defined");
    }

    private function visitNumberNode(NumberNode $node, Context $context): RuntimeResult
    {
        return (new RuntimeResult())->success(
            (new Number($node->token->value))
            ->setContext($context)
            ->setPosition($node->posStart, $node->posEnd)
        );
    }

    private function visitVarAccessNode(VarAccessNode $node, Context $context): RuntimeError|RuntimeResult
    {
        $response = new RuntimeResult();
        $varName = $node->varNameToken->value;
        $value = $context->symbolTable->get($varName);

        if(is_null($value)) {
            return $response->failure(
                new RuntimeError(
                    $node->posStart,
                    $node->posEnd,
                    "'{$varName}' is not defined",
                    $context
                )
            );
        } else {
            $value = ($value->copy())->setPosition($node->posStart, $node->posEnd);
            return $response->success($value);
        }
    }

    private function visitVarAssignNode(VarAssignNode $node, Context $context): RuntimeResult
    {
        $response = new RuntimeResult();
        $varName = $node->varNameToken->value;
        $value = $response->register($this->visit($node->valueNode, $context));
        if($response->error != null) {
            return $response;
        }

        $context->symbolTable->set($varName, $value);
        return $response->success($value);

    }

    private function visitBinOpNode(BinOpNode $node, Context $context): RuntimeError|RuntimeResult
    {
        $response = new RuntimeResult();
        $left = $response->register($this->visit($node->leftNode, $context));
        if(!is_null($response->error)) {
            return $response;
        }
        $right = $response->register($this->visit($node->rightNode, $context));

        $operationType = $node->opNode->type;

        if ($operationType->checkOperator()) {

            $operationMethod = $operationType->getOperator();
            $result = call_user_func_array([$left, $operationMethod], [$right]);
        } elseif ($node->opNode->matches(TokenType::KEYWORD, '&&')) {
            $result = $left->andWith($right);
        } elseif ($node->opNode->matches(TokenType::KEYWORD, '||')) {
            $result = $left->orWith($right);
        }

        if($result instanceof RuntimeError) {
            return $response->failure($result);
        } else {
            return $response->success($result->setPosition($node->posStart, $node->posEnd));
        }
    }

    private function visitUnaryOpNode(UnaryOpNode $node, Context $context): RuntimeError|RuntimeResult
    {
        $response = new RuntimeResult();
        $number = $response->register($this->visit($node->node, $context));
        if(!is_null($response->error)) {
            return $response;
        }

        if($node->opToken->type == TokenType::MINUS) {
            $number = $number->multipliedBy(new Number(-1));
        } elseif($node->opToken->type == TokenType::NOT) {
            $number = $number->invert();
        }

        if($number instanceof RuntimeError) {
            return $response->failure($number);
        } else {
            return $response->success($number->setPosition($node->posStart, $node->posEnd));
        }
    }

    private function visitIfNode(IfNode $node, Context $context): RuntimeError|RuntimeResult
    {
        $response = new RuntimeResult();
        foreach ($node->cases as [$condition, $expr]) {
            $conditionValue = $response->register($this->visit($condition, $context));
            if(!is_null($response->error)) {
                return $response;
            }

            if($conditionValue->isTrue()) {
                $exprValue = $response->register($this->visit($expr, $context));
                if(!is_null($response->error)) {
                    return $response;
                }
                return $response->success($exprValue);
            }
        }

        if(!is_null($node->elseCase)) {
            $elseValue = $response->register($this->visit($node->elseCase, $context));
            if(!is_null($response->error)) {
                return $response;
            }
            return $response->success($elseValue);
        }

        return $response->success(null);
    }
}
