<?php

declare(strict_types=1);

namespace Bachalang;

use Bachalang\Errors\InvalidSyntaxError;
use Bachalang\Nodes\BinOpNode;
use Bachalang\Nodes\NumberNode;
use Bachalang\Nodes\UnaryOpNode;

class Parser
{
    public function __construct(
        public array $tokens,
        public int $tokenIndex = 0,
        public ?Token $currentToken = null
    ) {
        $this->currentToken = $this->tokens[$this->tokenIndex];
    }

    private function advance(): Token
    {
        $this->tokenIndex++;
        if ($this->tokenIndex < count($this->tokens)) {
            $this->currentToken = $this->tokens[$this->tokenIndex];
        }
        return $this->currentToken;
    }

    private function atom()
    {
        $response = new ParseResult();
        $token = $this->currentToken;

        if(in_array($token->type, [TT::FLOAT->value, TT::INT->value])) {
            $response->register($this->advance());
            return $response->success(new NumberNode($token));
        } elseif ($token->type == TT::LPAREN->value) {
            $response->register($this->advance());
            $expr = $response->register($this->expression());
            if($response->error != null) {
                return $response;
            } elseif($this->currentToken->type == TT::RPAREN->value) {
                $response->register($this->advance());
                return $response->success($expr);
            } else {
                return $response->failure(new InvalidSyntaxError(
                    $this->currentToken->posStart,
                    $this->currentToken->posEnd,
                    "Expected ')' closing parentesis"
                ));
            }
        } else {
            return $response->failure(new InvalidSyntaxError(
                $token->posStart,
                $token->posEnd,
                "Expected '+', '-', '*' or '/'"
            ));
        }
    }

    private function power()
    {
        return $this->getBinaryOperation(array($this, 'atom'), [TT::POW->value]);
    }

    private function factor()
    {
        $response = new ParseResult();
        $token = $this->currentToken;

        if(in_array($token->type, [TT::PLUS->value, TT::MINUS->value])) {
            $response->register($this->advance());
            $factor = $response->register($this->factor());
            if($response->error != null) {
                return $response;
            } else {
                return $response->success(new UnaryOpNode($token, $factor));
            }
        } else {
            return $this->power();
        }
    }

    public function run()
    {
        $res = $this->expression();
        if($res->error == null && $this->currentToken->type != TT::EOF->value) {
            return $res->failure(new InvalidSyntaxError(
                $this->currentToken->posStart,
                $this->currentToken->posEnd,
                "Expected '+', '-', '*' or '/'"
            ));
        }
        return $res;
    }

    private function expression()
    {
        $binaryOp = $this->getBinaryOperation(array($this, 'term'), [TT::PLUS->value, TT::MINUS->value]);

        return $binaryOp;
    }

    private function term()
    {
        $binaryOp = $this->getBinaryOperation(array($this, 'factor'), [TT::MUL->value, TT::DIV->value]);

        return $binaryOp;
    }

    private function getBinaryOperation(callable $funcA, array $operators, ?callable $funcB = null)
    {
        if($funcB == null) {
            $funcB = $funcA;
        }

        $response = new ParseResult();
        $left = $response->register($funcA());

        if($response->error != null) {
            return $response;
        }

        while(in_array($this->currentToken, $operators)) {
            $opToken = $this->currentToken;
            $response->register($this->advance());
            $right = $response->register($funcB());
            if($response->error != null) {
                return $response;
            } else {
                $left = new BinOpNode($left, $opToken, $right);
            }
        }

        return $response->success($left);
    }
}
