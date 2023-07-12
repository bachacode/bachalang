<?php

declare(strict_types=1);

namespace Bachalang;

use Bachalang\Errors\InvalidSyntaxError;
use Bachalang\Nodes\BinOpNode;
use Bachalang\Nodes\NumberNode;

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

    private function factor()
    {
        $response = new ParseResult();
        $token = $this->currentToken;

        if(in_array($token->type, [TT::FLOAT->value, TT::INT->value])) {
            $response->register($this->advance());
            return $response->success(new NumberNode($token));
        } else {
            return $response->failure(new InvalidSyntaxError(
                $token->posStart,
                $token->posEnd,
                'Expected INT or FLOAT'
            ));
        }
    }

    public function run()
    {
        $res = $this->expression();
        // if($res->error != null && $this->currentToken->type != TT::EOF->value) {
        //     return $res->failure(new InvalidSyntaxError(
        //         $this->currentToken->posStart,
        //         $this->currentToken->posEnd,
        //         "Expected '+', '-', '*' or '/'"
        //     ));
        // }
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

    private function getBinaryOperation(callable $func, array $operators)
    {
        $response = new ParseResult();
        $left = $response->register($func());

        if($response->error != null) {
            return $response;
        }

        while(in_array($this->currentToken, $operators)) {
            $opToken = $this->currentToken;
            $response->register($this->advance());
            $right = $response->register($func());
            if($response->error != null) {
                return $response;
            } else {
                $left = new BinOpNode($left, $opToken, $right);
            }
        }

        return $response->success($left);
    }
}
