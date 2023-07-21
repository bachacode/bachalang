<?php

declare(strict_types=1);

namespace Bachalang;

use Bachalang\Errors\InvalidSyntaxError;
use Bachalang\Nodes\BinOpNode;
use Bachalang\Nodes\NumberNode;
use Bachalang\Nodes\UnaryOpNode;
use Bachalang\Nodes\VarAccessNode;
use Bachalang\Nodes\VarAssignNode;

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
        if(in_array($token->type, [TokenType::FLOAT, TokenType::INT])) {
            $response->registerAdvancement();
            $this->advance();
            return $response->success(new NumberNode($token));
        } elseif($token->type == TokenType::IDENTIFIER) {
            $response->registerAdvancement();
            $this->advance();
            return $response->success(new VarAccessNode($token));
        } elseif ($token->type == TokenType::LPAREN) {
            $response->registerAdvancement();
            $this->advance();
            $expr = $response->register($this->expression());
            if($response->error != null) {
                return $response;
            } elseif($this->currentToken->type == TokenType::RPAREN) {
                $response->registerAdvancement();
                $this->advance();
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
                "Expected '+', '-', 'IDENTIFIER', '(' 'INT' or 'FLOAT'"
            ));
        }
    }

    private function power()
    {
        $binaryOp = $this->getBinaryOperation(array($this, 'atom'), [TokenType::POW]);

        return $binaryOp;
    }

    private function factor()
    {
        $response = new ParseResult();
        $token = $this->currentToken;

        if(in_array($token->type, [TokenType::PLUS, TokenType::MINUS])) {
            $response->registerAdvancement();
            $this->advance();
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
        if($res->error == null && $this->currentToken->type != TokenType::EOF) {
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
        $response = new ParseResult();
        if(!$this->currentToken->matches(TokenType::KEYWORD, 'var')) {
            $node = $response->register(
                $this->getBinaryOperation(array($this, 'term'), [TokenType::PLUS, TokenType::MINUS])
            );
            if($response->error != null) {
                return $response->failure(new InvalidSyntaxError(
                    $this->currentToken->posStart,
                    $this->currentToken->posEnd,
                    "Expected '+', '-', 'IDENTIFIER', 'VAR', '(' 'INT' or 'FLOAT'"
                ));
            } else {
                return $response->success($node);
            }
        }
        $response->registerAdvancement();
        $this->advance();
        if($this->currentToken->type != TokenType::IDENTIFIER) {
            return $response->failure(new InvalidSyntaxError(
                $this->currentToken->posStart,
                $this->currentToken->posEnd,
                "Expected identifier"
            ));
        }
        $varName = $this->currentToken;
        $response->registerAdvancement();
        $this->advance();

        if($this->currentToken->type != TokenType::EQUALS) {
            return $response->failure(new InvalidSyntaxError(
                $this->currentToken->posStart,
                $this->currentToken->posEnd,
                "Expected equal sign '='"
            ));
        }

        $response->registerAdvancement();
        $this->advance();

        $expr = $response->register($this->expression());

        if($response->error != null) {
            return $response;
        } else {
            return $response->success(new VarAssignNode($varName, $expr));
        }
    }

    private function term()
    {
        $binaryOp = $this->getBinaryOperation(array($this, 'factor'), [TokenType::MUL, TokenType::DIV]);

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
        while(in_array($this->currentToken->type, $operators)) {
            $opToken = $this->currentToken;
            $response->registerAdvancement();
            $this->advance();
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
