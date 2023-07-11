<?php

declare(strict_types=1);

namespace Bachalang;

use Bachalang\Nodes\BinOpNode;
use Bachalang\Nodes\NumberNode;

class Parser
{
    public function __construct(
        public array $tokens,
        public int $tokenIndex = 0,
        public $currentToken = null
    ) {
        $this->currentToken = $this->tokens[$this->tokenIndex];
    }

    public function advance()
    {
        $this->tokenIndex++;
        if ($this->tokenIndex < count($this->tokens)) {
            $this->currentToken = $this->tokens[$this->tokenIndex];
        }
        return $this->currentToken;
    }

    public function factor()
    {
        $token = $this->currentToken;

        if(in_array($token->type, [TT::FLOAT->value, TT::INT->value])) {
            $this->advance();
            return new NumberNode($token);
        }
    }

    public function run()
    {
        $res = $this->expression();
        return $res;
    }

    public function expression()
    {
        $binaryOp = $this->getBinaryOperation(array($this, 'term'), [TT::PLUS->value, TT::MINUS->value]);

        return $binaryOp;
    }

    public function term()
    {
        $binaryOp = $this->getBinaryOperation(array($this, 'factor'), [TT::MUL->value, TT::DIV->value]);

        return $binaryOp;
    }

    private function getBinaryOperation(callable $func, array $operators)
    {
        $left = $func();

        while(in_array($this->currentToken, $operators)) {
            $opToken = $this->currentToken;
            $this->advance();
            $right = $func();
            $left = new BinOpNode($left, $opToken, $right);
        }

        return $left;
    }
}
