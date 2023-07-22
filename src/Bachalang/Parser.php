<?php

declare(strict_types=1);

namespace Bachalang;

use Bachalang\Errors\InvalidSyntaxError;
use Bachalang\Nodes\BinOpNode;
use Bachalang\Nodes\IfNode;
use Bachalang\Nodes\NumberNode;
use Bachalang\Nodes\UnaryOpNode;
use Bachalang\Nodes\VarAccessNode;
use Bachalang\Nodes\VarAssignNode;

class Parser
{
    /**
     * @param Token[] $tokens
     */

    public function __construct(
        private array $tokens = [],
        private int $tokenIndex = 0,
        private ?Token $currentToken = null
    ) {
        $this->currentToken = $this->tokens[$this->tokenIndex];
    }

    public function setTokens(array $tokens)
    {
        $this->tokenIndex = 0;
        $this->tokens = $tokens;
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

    public function run()
    {
        $res = $this->expr();
        if($res->error == null && $this->currentToken->type != TokenType::EOF) {
            return $res->failure(new InvalidSyntaxError(
                $this->currentToken->posStart,
                $this->currentToken->posEnd,
                "Expected '+', '-', '*' or '/'"
            ));
        }
        return $res;
    }

    private function expr()
    {
        $result = new ParseResult();
        // #1 Priority - Variable definitions
        if($this->currentToken->matches(TokenType::KEYWORD, 'var')) {
            $result->registerAdvancement();
            $this->advance();

            if($this->currentToken->type != TokenType::IDENTIFIER) {
                return $result->failure(new InvalidSyntaxError(
                    $this->currentToken->posStart,
                    $this->currentToken->posEnd,
                    "Expected identifier"
                ));
            }

            $varName = $this->currentToken;
            $result->registerAdvancement();
            $this->advance();

            if($this->currentToken->type != TokenType::EQUALS) {
                return $result->failure(new InvalidSyntaxError(
                    $this->currentToken->posStart,
                    $this->currentToken->posEnd,
                    "Expected equal sign '='"
                ));
            }

            $result->registerAdvancement();
            $this->advance();

            $expr = $result->register($this->expr());

            if($result->error != null) {
                return $result;
            } else {
                return $result->success(new VarAssignNode($varName, $expr));
            }
        }
        // #2 Priority - Expressions
        else {
            $node = $result->register(
                $this->getBinaryOperation(
                    array($this, 'compExpr'),
                    [[TokenType::KEYWORD, '&&'], [TokenType::KEYWORD, '||']]
                )
            );
            if($result->error != null) {
                return $result->failure(new InvalidSyntaxError(
                    $this->currentToken->posStart,
                    $this->currentToken->posEnd,
                    "Expected 'INT', 'FLOAT', 'IDENTIFIER' '+', '-', '(', or '!'"
                ));
            } else {
                return $result->success($node);
            }
        }

    }

    private function compExpr()
    {
        $result = new ParseResult();
        // #3 Priority - Negate comparison expressions
        if($this->currentToken->matches(TokenType::NOT, null)) {
            $opToken = $this->currentToken;
            $result->registerAdvancement();
            $this->advance();
            $node = $result->register($this->compExpr());
            if($result->error != null) {
                return $result;
            } else {
                return $result->success(new UnaryOpNode($opToken, $node));
            }
        }
        // #4 Priority - Comparison expresssions
        else {
            $node = $result->register(
                $this->getBinaryOperation(
                    array($this, 'arithExpr'),
                    [
                        TokenType::EE, TokenType::NE,
                        TokenType::LT, TokenType::GT,
                        TokenType::LTE, TokenType::GTE
                    ]
                )
            );
            if($result->error != null) {
                return $result->failure(new InvalidSyntaxError(
                    $this->currentToken->posStart,
                    $this->currentToken->posEnd,
                    "Expected 'INT', 'FLOAT', 'IDENTIFIER' '+', '-', '(', or '!'"
                ));
            } else {
                return $result->success($node);
            }
        }
    }

    private function arithExpr()
    {
        // #5 Priority - Aritmethic expression between two terms
        return $this->getBinaryOperation(array($this, 'term'), [TokenType::PLUS, TokenType::MINUS]);
    }

    private function term()
    {
        // #6 Priority - Term operation between two factors
        return $this->getBinaryOperation(array($this, 'factor'), [TokenType::MUL, TokenType::DIV]);
    }

    private function factor()
    {
        $result = new ParseResult();
        $token = $this->currentToken;

        // #7 Priority - Positive/negative factor
        if(in_array($token->type, [TokenType::PLUS, TokenType::MINUS])) {
            $result->registerAdvancement();
            $this->advance();
            $factor = $result->register($this->factor());
            if($result->error != null) {
                return $result;
            } else {
                return $result->success(new UnaryOpNode($token, $factor));
            }
        }
        // #8 Priority - Exponential factor
        else {
            return $this->power();
        }
    }

    private function power()
    {
        // #9 Priority - Atomic value
        return $this->getBinaryOperation(array($this, 'atom'), [TokenType::POW]);
    }

    private function atom()
    {
        $result = new ParseResult();
        $token = $this->currentToken;

        // #10 Priority - Integer or Float
        if(in_array($token->type, [TokenType::FLOAT, TokenType::INT])) {
            $result->registerAdvancement();
            $this->advance();
            return $result->success(new NumberNode($token));
        }
        // #11 Priority - Identifier of a variable
        elseif($token->type == TokenType::IDENTIFIER) {
            $result->registerAdvancement();
            $this->advance();
            return $result->success(new VarAccessNode($token));
        }
        // #12 Priority - Expression between parenthesis
        elseif ($token->type == TokenType::LPAREN) {
            $result->registerAdvancement();
            $this->advance();
            $expr = $result->register($this->expr());
            if($result->error != null) {
                return $result;
            } elseif($this->currentToken->type == TokenType::RPAREN) {
                $result->registerAdvancement();
                $this->advance();
                return $result->success($expr);
            } else {
                return $result->failure(new InvalidSyntaxError(
                    $this->currentToken->posStart,
                    $this->currentToken->posEnd,
                    "Expected ')' closing parentesis"
                ));
            }
        }
        // #13 Priority - If expression
        elseif ($token->matches(TokenType::KEYWORD, 'if')) {
            $ifExpr = $result->register($this->ifExpr());
            if($result->error != null) {
                return $result;
            } else {
                return $result->success($ifExpr);
            }
        }
        // ERROR - if token order does not match grammar, throw an error
        else {
            return $result->failure(new InvalidSyntaxError(
                $token->posStart,
                $token->posEnd,
                "Expected '+', '-', 'IDENTIFIER', '(' 'INT' or 'FLOAT'"
            ));
        }
    }

    private function ifExpr()
    {
        $result = new ParseResult();
        $cases = [];
        $elseCase = null;

        if(!$this->currentToken->matches(TokenType::KEYWORD, 'if')) {
            return $result->failure(new InvalidSyntaxError(
                $this->currentToken->posStart,
                $this->currentToken->posEnd,
                "Expected 'if' keyword"
            ));
        }

        $result->registerAdvancement();
        $this->advance();

        $condition = $result->register($this->expr());
        if(!is_null($result->error)) {
            return $result;
        }

        if(!$this->currentToken->matches(TokenType::KEYWORD, 'then')) {
            return $result->failure(new InvalidSyntaxError(
                $this->currentToken->posStart,
                $this->currentToken->posEnd,
                "Expected 'then' keyword after 'if'"
            ));
        }

        $result->registerAdvancement();
        $this->advance();

        $expr = $result->register($this->expr());
        if(!is_null($result->error)) {
            return $result;
        }
        array_push($cases, [$condition, $expr]);

        while ($this->currentToken->matches(TokenType::KEYWORD, 'elseif')) {
            $result->registerAdvancement();
            $this->advance();

            $condition = $result->register($this->expr());
            if(!is_null($result->error)) {
                return $result;
            }

            if(!$this->currentToken->matches(TokenType::KEYWORD, 'then')) {
                return $result->failure(new InvalidSyntaxError(
                    $this->currentToken->posStart,
                    $this->currentToken->posEnd,
                    "Expected 'then' keyword after 'elseif'"
                ));
            }

            $result->registerAdvancement();
            $this->advance();

            $expr = $result->register($this->expr());
            if(!is_null($result->error)) {
                return $result;
            }
            array_push($cases, [$condition, $expr]);
        }

        if($this->currentToken->matches(TokenType::KEYWORD, 'else')) {
            $result->registerAdvancement();
            $this->advance();

            $expr = $result->register($this->expr());
            if(!is_null($result->error)) {
                return $result;
            }
            $elseCase = $expr;
        }

        return $result->success(new IfNode($cases, $elseCase));
    }

    private function getBinaryOperation(callable $funcA, array $operators, ?callable $funcB = null)
    {
        if($funcB == null) {
            $funcB = $funcA;
        }

        $result = new ParseResult();
        $left = $result->register($funcA());

        if($result->error != null) {
            return $result;
        }
        while(
            in_array($this->currentToken->type, $operators) ||
            in_array([$this->currentToken->type, $this->currentToken->value], $operators)
        ) {
            $opToken = $this->currentToken;
            $result->registerAdvancement();
            $this->advance();
            $right = $result->register($funcB());
            if($result->error != null) {
                return $result;
            } else {
                $left = new BinOpNode($left, $opToken, $right);
            }
        }

        return $result->success($left);
    }
}
