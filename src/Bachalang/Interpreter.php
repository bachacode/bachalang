<?php

declare(strict_types=1);

namespace Bachalang;

use Bachalang\Errors\RuntimeError;
use Bachalang\Nodes\ArrayNode;
use Bachalang\Nodes\BinOpNode;
use Bachalang\Nodes\CallNode;
use Bachalang\Nodes\ForNode;
use Bachalang\Nodes\FuncDefNode;
use Bachalang\Nodes\IfNode;
use Bachalang\Nodes\Node;
use Bachalang\Nodes\NumberNode;
use Bachalang\Nodes\StringNode;
use Bachalang\Nodes\UnaryOpNode;
use Bachalang\Nodes\VarAccessNode;
use Bachalang\Nodes\VarAssignNode;
use Bachalang\Nodes\WhileNode;
use Bachalang\Values\ArrayVal;
use Bachalang\Values\BaseFunc;
use Bachalang\Values\BuiltInFunc;
use Bachalang\Values\Func;
use Bachalang\Values\Number;
use Bachalang\Values\StringVal;
use Bachalang\Values\Value;
use Exception;

class Interpreter
{
    public static function visit(Node &$node, Context &$context): RuntimeError|RuntimeResult
    {
        $methodName = "visit";
        $methodName .= basename(get_class($node));
        if(method_exists(Interpreter::class, $methodName)) {
            return call_user_func_array([Interpreter::class, $methodName], [&$node, &$context]);
        } else {
            Interpreter::noVisitMethod($methodName, $context);
        }
    }

    private static function noVisitMethod(string $methodName, Context $context): never
    {
        throw new Exception("Method: {$methodName} is not defined");
    }

    private static function visitNumberNode(NumberNode $node, Context $context): RuntimeResult
    {
        return (new RuntimeResult())->success(
            (new Number($node->token->value))
            ->setContext($context)
            ->setPosition($node->posStart, $node->posEnd)
        );
    }

    private static function visitStringNode(StringNode $node, Context $context): RuntimeResult
    {
        return (new RuntimeResult())->success(
            (new StringVal($node->token->value))
            ->setContext($context)
            ->setPosition($node->posStart, $node->posEnd)
        );
    }

    private static function visitArrayNode(ArrayNode $node, Context $context): RuntimeError|RuntimeResult
    {
        $result = new RuntimeResult();
        $elements = [];

        foreach ($node->elementNodes as $value) {
            $elements[] = $result->register(Interpreter::visit($value, $context));
            if(!is_null($result->error)) {
                return $result;
            }
        }
        return $result->success(
            (new ArrayVal($elements))
            ->setContext($context)
            ->setPosition($node->posStart, $node->posEnd)
        );
    }

    private static function visitVarAccessNode(VarAccessNode $node, Context $context): RuntimeError|RuntimeResult
    {
        $result = new RuntimeResult();
        $varName = $node->varNameToken->value;
        $value = &$context->symbolTable->get($varName);

        if(is_null($value)) {
            return $result->failure(
                new RuntimeError(
                    $node->posStart,
                    $node->posEnd,
                    "'{$varName}' is not defined",
                    $context
                )
            );
        } else {
            $value = $value->setPosition($node->posStart, $node->posEnd)->setContext($context);
            return $result->success($value);
        }
    }

    private static function visitVarAssignNode(VarAssignNode $node, Context $context): RuntimeResult
    {
        $result = new RuntimeResult();
        $varName = $node->varNameToken->value;
        $value = $result->register(Interpreter::visit($node->valueNode, $context));
        if($result->error != null) {
            return $result;
        }

        $context->symbolTable->set($varName, $value);
        return $result->success($value);

    }

    private static function visitBinOpNode(BinOpNode $node, Context $context): RuntimeError|RuntimeResult
    {
        $result = new RuntimeResult();
        $left = $result->register(Interpreter::visit($node->leftNode, $context));
        if(!is_null($result->error)) {
            return $result;
        }
        $right = $result->register(Interpreter::visit($node->rightNode, $context));

        $operationType = $node->opNode->type;

        if ($operationType->checkOperator()) {
            $operationMethod = $operationType->getOperator();
            $opResult = call_user_func_array([$left, $operationMethod], [$right]);
        } elseif ($node->opNode->matches(TokenType::KEYWORD, '&&')) {
            $opResult = $left->andWith($right);
        } elseif ($node->opNode->matches(TokenType::KEYWORD, '||')) {
            $opResult = $left->orWith($right);
        }

        if($opResult instanceof RuntimeError) {
            return $result->failure($opResult);
        } else {
            return $result->success($opResult->setPosition($node->posStart, $node->posEnd));
        }
    }

    private static function visitUnaryOpNode(UnaryOpNode $node, Context $context): RuntimeError|RuntimeResult
    {
        $result = new RuntimeResult();
        $number = $result->register(Interpreter::visit($node->node, $context));
        if(!is_null($result->error)) {
            return $result;
        }

        if($node->opToken->type == TokenType::MINUS) {
            $number = $number->multipliedBy(new Number(-1));
        } elseif($node->opToken->type == TokenType::NOT) {
            $number = $number->invert();
        }

        if($number instanceof RuntimeError) {
            return $result->failure($number);
        } else {
            return $result->success($number->setPosition($node->posStart, $node->posEnd));
        }
    }

    private static function visitIfNode(IfNode $node, Context $context): RuntimeError|RuntimeResult
    {
        $result = new RuntimeResult();
        foreach ($node->cases as [$condition, $expr]) {
            $conditionValue = $result->register(Interpreter::visit($condition, $context));
            if(!is_null($result->error)) {
                return $result;
            }

            if($conditionValue->isTrue()) {
                $exprValue = $result->register(Interpreter::visit($expr, $context));
                if(!is_null($result->error)) {
                    return $result;
                }
                return $result->success($exprValue);
            }
        }

        if(!is_null($node->elseCase)) {
            $elseValue = $result->register(Interpreter::visit($node->elseCase, $context));
            if(!is_null($result->error)) {
                return $result;
            }
            return $result->success($elseValue);
        }

        return $result->success(new Number(Number::NULL));
    }

    private static function visitForNode(ForNode $node, Context $context): RuntimeResult
    {
        $result = new RuntimeResult();
        $elements = [];
        $startValue = $result->register(Interpreter::visit($node->startValueNode, $context));
        if(!is_null($result->error)) {
            return $result;
        }
        $endValue = $result->register(Interpreter::visit($node->endValueNode, $context));
        if(!is_null($result->error)) {
            return $result;
        }

        if(!is_null($node->stepValueNode)) {
            $stepValue = $result->register(Interpreter::visit($node->stepValueNode, $context));
            if(!is_null($result->error)) {
                return $result;
            }
        } else {
            $stepValue = new Number(1);
        }

        if($startValue instanceof Number && $stepValue instanceof Number && $endValue instanceof Number) {
            $i = $startValue->value;


            if($stepValue->value >= 0) {
                $condition = function () use ($i, $endValue) {
                    return $i < $endValue->value;
                };
            } else {
                $condition = function () use ($i, $endValue) {
                    return $i > $endValue->value;
                };
            }

            while($i < $endValue->value) {
                $context->symbolTable->set($node->varNameToken->value, new Number($i));
                $i += $stepValue->value;

                array_push($elements, $result->register(Interpreter::visit($node->bodyNode, $context)));
                if(!is_null($result->error)) {
                    return $result;
                }

            }
        }
        return $result->success(
            (
            new ArrayVal($elements))
            ->setContext($context)
            ->setPosition($node->posStart, $node->posEnd)
        );
    }

    private static function visitWhileNode(WhileNode $node, Context $context): RuntimeError|RuntimeResult
    {
        $result = new RuntimeResult();
        $elements = [];
        while (true) {
            $condition = $result->register(Interpreter::visit($node->conditionNode, $context));
            if(!is_null($result->error)) {
                return $result;
            }

            if(!$condition->isTrue()) {
                break;
            }
            array_push($elements, $result->register(Interpreter::visit($node->bodyNode, $context)));
            if(!is_null($result->error)) {
                return $result;
            }
        }
        return $result->success(
            (
            new ArrayVal($elements))
            ->setContext($context)
            ->setPosition($node->posStart, $node->posEnd)
        );
    }

    private static function visitFuncDefNode(FuncDefNode $node, Context $context): RuntimeError|RuntimeResult
    {
        $result = new RuntimeResult();

        $funcName = $node->varNameToken->value ?? null;
        $bodyNode = $node->bodyNode;
        $argNames = [];
        foreach ($node->argNameTokens as $argName) {
            $argNames[] = $argName->value;
        }

        $funcValue = new Func($funcName, $bodyNode, $argNames);
        $funcValue->setContext($context);
        $funcValue->setPosition($node->posStart, $node->posEnd);

        if(!is_null($node->varNameToken)) {
            $context->symbolTable->set($funcName, $funcValue);
        }

        return $result->success($funcValue);
    }

    private static function visitCallNode(CallNode &$node, Context &$context): RuntimeError|RuntimeResult
    {
        $result = new RuntimeResult();

        $args = [];

        $valueToCall = $result->register(Interpreter::visit($node->nodeToCall, $context));
        if(!is_null($result->error)) {
            return $result;
        }
        if($valueToCall instanceof Func || $valueToCall instanceof BuiltInFunc) {
            $valueToCall = clone $valueToCall->setPosition($node->posStart, $node->posEnd);

            foreach ($node->argNodes as $value) {
                $args[] = $result->register(Interpreter::visit($value, $context));
            }

            $returnValue = $result->register($valueToCall->execute($args));
        }
        if(!is_null($result->error)) {
            return $result;
        }
        return $result->success($returnValue);
    }
}
