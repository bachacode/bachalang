<?php

declare(strict_types=1);

namespace Bachalang;

use Bachalang\Errors\InvalidSyntaxError;
use Bachalang\Nodes\ArrayNode;
use Bachalang\Nodes\BinOpNode;
use Bachalang\Nodes\CallNode;
use Bachalang\Nodes\ForNode;
use Bachalang\Nodes\FuncDefNode;
use Bachalang\Nodes\IfNode;
use Bachalang\Nodes\NumberNode;
use Bachalang\Nodes\StringNode;
use Bachalang\Nodes\UnaryOpNode;
use Bachalang\Nodes\VarAccessNode;
use Bachalang\Nodes\VarAssignNode;
use Bachalang\Nodes\WhileNode;

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
        if($this->currentToken->matches(TokenType::KEYWORD, 'let')) {
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
                    "Expected 'INT', 'FLOAT', 'IDENTIFIER', 'FOR', 'WHILE', 'FUNCTION', '+', '-', '(', '[', or '!'"
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
                    "Expected 'INT', 'FLOAT', 'IDENTIFIER' '+', '-', '(', '[', or '!'"
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
        // #9 Priority - Callable Function
        return $this->getBinaryOperation(array($this, 'call'), [TokenType::POW]);
    }

    private function call()
    {
        $result = new ParseResult();
        $atom = $result->register($this->atom());
        if(!is_null($result->error)) {
            return $result;
        }

        if($this->currentToken->type == TokenType::LPAREN) {
            $result->registerAdvancement();
            $this->advance();
            $argNodes = [];

            if($this->currentToken->type == TokenType::RPAREN) {
                $result->registerAdvancement();
                $this->advance();
            } else {
                array_push($argNodes, $result->register($this->expr()));
                if(!is_null($result->error)) {
                    return $result->failure(new InvalidSyntaxError(
                        $this->currentToken->posStart,
                        $this->currentToken->posEnd,
                        "Expected ')', 'INT', 'FLOAT', 'IDENTIFIER', 'FOR', 'WHILE', 'FUNCTION', '+', '-', '(', '[', or '!'"
                    ));
                }

                while ($this->currentToken->type == TokenType::COMMA) {
                    $result->registerAdvancement();
                    $this->advance();

                    array_push($argNodes, $result->register($this->expr()));
                    if(!is_null($result->error)) {
                        return $result;
                    }
                }

                if($this->currentToken->type != TokenType::RPAREN) {
                    return $result->failure(new InvalidSyntaxError(
                        $this->currentToken->posStart,
                        $this->currentToken->posEnd,
                        "Expected ')' or ','"
                    ));
                }

                $result->registerAdvancement();
                $this->advance();

            }
            return $result->success(new CallNode($atom, $argNodes));
        }
        return $result->success($atom);
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
        // #12 Priority - String
        elseif($token->type == TokenType::STRING) {
            $result->registerAdvancement();
            $this->advance();
            return $result->success(new StringNode($token));
        }
        // #13 Priority - Expression between parenthesis
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
        // #14 Priority - Expression between square brackets
        elseif ($token->type == TokenType::LSQUARE) {
            $listExpr = $result->register($this->arrayExpr());
            if($result->error != null) {
                return $result;
            }
            return $result->success($listExpr);
        }
        // #15 Priority - If expression
        elseif ($token->matches(TokenType::KEYWORD, 'if')) {
            $ifExpr = $result->register($this->ifExpr());
            if($result->error != null) {
                return $result;
            } else {
                return $result->success($ifExpr);
            }
        }
        // #16 Priority - For expression
        elseif ($token->matches(TokenType::KEYWORD, 'for')) {
            $forExpr = $result->register($this->forExpr());
            if($result->error != null) {
                return $result;
            } else {
                return $result->success($forExpr);
            }
        }
        // #17 Priority - For expression
        elseif ($token->matches(TokenType::KEYWORD, 'while')) {
            $whileExpr = $result->register($this->whileExpr());
            if($result->error != null) {
                return $result;
            } else {
                return $result->success($whileExpr);
            }
        }
        // #18 Priority - For expression
        elseif ($token->matches(TokenType::KEYWORD, 'function')) {
            $funcDef = $result->register($this->funcDef());
            if($result->error != null) {
                return $result;
            } else {
                return $result->success($funcDef);
            }
        }
        // ERROR - if token order does not match grammar, throw an error
        else {
            return $result->failure(new InvalidSyntaxError(
                $token->posStart,
                $token->posEnd,
                "Expected '+', '-', 'IDENTIFIER', '(', '[', 'INT', 'FLOAT', 'if', 'for', 'while', or 'function'"
            ));
        }
    }

    private function arrayExpr()
    {
        $result = new ParseResult();
        $elementNodes = [];
        $posStart = clone $this->currentToken->posStart;

        if($this->currentToken->type != TokenType::LSQUARE) {
            return $result->failure(new InvalidSyntaxError(
                $this->currentToken->posStart,
                $this->currentToken->posEnd,
                "Expected '['"
            ));
        }

        $result->registerAdvancement();
        $this->advance();

        if($this->currentToken->type == TokenType::RSQUARE) {
            $result->registerAdvancement();
            $this->advance();
        } else {
            array_push($elementNodes, $result->register($this->expr()));
            if(!is_null($result->error)) {
                return $result->failure(new InvalidSyntaxError(
                    $this->currentToken->posStart,
                    $this->currentToken->posEnd,
                    "Expected ']', 'INT', 'FLOAT', 'IDENTIFIER', 'FOR', 'WHILE', 'FUNCTION', '+', '-', '(', or '!'"
                ));
            }

            while ($this->currentToken->type == TokenType::COMMA) {
                $result->registerAdvancement();
                $this->advance();

                array_push($elementNodes, $result->register($this->expr()));
                if(!is_null($result->error)) {
                    return $result;
                }
            }

            if($this->currentToken->type != TokenType::RSQUARE) {
                return $result->failure(new InvalidSyntaxError(
                    $this->currentToken->posStart,
                    $this->currentToken->posEnd,
                    "Expected ']' or ','"
                ));
            }

            $result->registerAdvancement();
            $this->advance();

        }
        return $result->success(new ArrayNode($elementNodes, $posStart, clone $this->currentToken->posEnd));
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

        if($this->currentToken->type != TokenType::LCURLY) {
            return $result->failure(new InvalidSyntaxError(
                $this->currentToken->posStart,
                $this->currentToken->posEnd,
                "Expected '{' keyword after 'if'"
            ));
        }

        $result->registerAdvancement();
        $this->advance();

        $expr = $result->register($this->expr());
        if(!is_null($result->error)) {
            return $result;
        }

        if($this->currentToken->type != TokenType::RCURLY) {
            return $result->failure(new InvalidSyntaxError(
                $this->currentToken->posStart,
                $this->currentToken->posEnd,
                "Expected '}' keyword after if expression"
            ));
        }

        $result->registerAdvancement();
        $this->advance();

        $cases[] = [$condition, $expr];

        while ($this->currentToken->matches(TokenType::KEYWORD, 'elseif')) {
            $result->registerAdvancement();
            $this->advance();

            $condition = $result->register($this->expr());
            if(!is_null($result->error)) {
                return $result;
            }

            if($this->currentToken->type != TokenType::LCURLY) {
                return $result->failure(new InvalidSyntaxError(
                    $this->currentToken->posStart,
                    $this->currentToken->posEnd,
                    "Expected '{' keyword after 'elseif'"
                ));
            }

            $result->registerAdvancement();
            $this->advance();

            $expr = $result->register($this->expr());
            if(!is_null($result->error)) {
                return $result;
            }

            if($this->currentToken->type != TokenType::RCURLY) {
                return $result->failure(new InvalidSyntaxError(
                    $this->currentToken->posStart,
                    $this->currentToken->posEnd,
                    "Expected '}' keyword after elseif expression"
                ));
            }

            $result->registerAdvancement();
            $this->advance();

            $cases[] = [$condition, $expr];
        }

        if($this->currentToken->matches(TokenType::KEYWORD, 'else')) {
            $result->registerAdvancement();
            $this->advance();

            if($this->currentToken->type != TokenType::LCURLY) {
                return $result->failure(new InvalidSyntaxError(
                    $this->currentToken->posStart,
                    $this->currentToken->posEnd,
                    "Expected '{' keyword after 'elseif'"
                ));
            }

            $result->registerAdvancement();
            $this->advance();

            $expr = $result->register($this->expr());
            if(!is_null($result->error)) {
                return $result;
            }

            if($this->currentToken->type != TokenType::RCURLY) {
                return $result->failure(new InvalidSyntaxError(
                    $this->currentToken->posStart,
                    $this->currentToken->posEnd,
                    "Expected '}' keyword after elseif expression"
                ));
            }

            $result->registerAdvancement();
            $this->advance();
            $elseCase = $expr;
        }

        return $result->success(new IfNode($cases, $elseCase));
    }

    private function forExpr()
    {
        $result = new ParseResult();

        if(!$this->currentToken->matches(TokenType::KEYWORD, 'for')) {
            return $result->failure(new InvalidSyntaxError(
                $this->currentToken->posStart,
                $this->currentToken->posEnd,
                "Expected 'for' keyword"
            ));
        }

        $result->registerAdvancement();
        $this->advance();

        if($this->currentToken->type != TokenType::IDENTIFIER) {
            return $result->failure(new InvalidSyntaxError(
                $this->currentToken->posStart,
                $this->currentToken->posEnd,
                "Expected indentifier"
            ));
        }

        $varName = $this->currentToken;
        $result->registerAdvancement();
        $this->advance();

        if($this->currentToken->type != TokenType::EQUALS) {
            return $result->failure(new InvalidSyntaxError(
                $this->currentToken->posStart,
                $this->currentToken->posEnd,
                "Expected '=' after 'identifier'"
            ));
        }

        $result->registerAdvancement();
        $this->advance();

        $startValue = $result->register($this->expr());
        if(!is_null($result->error)) {
            return $result;
        }

        if(!$this->currentToken->matches(TokenType::KEYWORD, 'to')) {
            return $result->failure(new InvalidSyntaxError(
                $this->currentToken->posStart,
                $this->currentToken->posEnd,
                "Expected 'to' after '='"
            ));
        }

        $result->registerAdvancement();
        $this->advance();

        $endValue = $result->register($this->expr());
        if(!is_null($result->error)) {
            return $result;
        }

        if($this->currentToken->matches(TokenType::KEYWORD, 'step')) {
            $result->registerAdvancement();
            $this->advance();

            $stepValue = $result->register($this->expr());
            if(!is_null($result->error)) {
                return $result;
            }
        } else {
            $stepValue = null;
        }

        if($this->currentToken->type != TokenType::LCURLY) {
            return $result->failure(new InvalidSyntaxError(
                $this->currentToken->posStart,
                $this->currentToken->posEnd,
                "Expected '{' keyword after expression"
            ));
        }

        $result->registerAdvancement();
        $this->advance();

        $bodyNode = $result->register($this->expr());
        if(!is_null($result->error)) {
            return $result;
        }

        if($this->currentToken->type != TokenType::RCURLY) {
            return $result->failure(new InvalidSyntaxError(
                $this->currentToken->posStart,
                $this->currentToken->posEnd,
                "Expected '}' keyword after expression"
            ));
        }

        $result->registerAdvancement();
        $this->advance();

        return $result->success(new ForNode($varName, $startValue, $endValue, $stepValue, $bodyNode));
    }

    private function whileExpr()
    {
        $result = new ParseResult();

        if(!$this->currentToken->matches(TokenType::KEYWORD, 'while')) {
            return $result->failure(new InvalidSyntaxError(
                $this->currentToken->posStart,
                $this->currentToken->posEnd,
                "Expected 'while' keyword"
            ));
        }

        $result->registerAdvancement();
        $this->advance();

        $condition = $result->register($this->expr());
        if(!is_null($result->error)) {
            return $result;
        }

        if($this->currentToken->type != TokenType::LCURLY) {
            return $result->failure(new InvalidSyntaxError(
                $this->currentToken->posStart,
                $this->currentToken->posEnd,
                "Expected '{' keyword after expression"
            ));
        }

        $result->registerAdvancement();
        $this->advance();

        $bodyNode = $result->register($this->expr());
        if(!is_null($result->error)) {
            return $result;
        }

        if($this->currentToken->type != TokenType::RCURLY) {
            return $result->failure(new InvalidSyntaxError(
                $this->currentToken->posStart,
                $this->currentToken->posEnd,
                "Expected '}' keyword after expression"
            ));
        }

        $result->registerAdvancement();
        $this->advance();

        return $result->success(new WhileNode($condition, $bodyNode));
    }

    private function funcDef()
    {
        $result = new ParseResult();

        if(!$this->currentToken->matches(TokenType::KEYWORD, 'function')) {
            return $result->failure(new InvalidSyntaxError(
                $this->currentToken->posStart,
                $this->currentToken->posEnd,
                "Expected 'function' keyword"
            ));
        }

        $result->registerAdvancement();
        $this->advance();

        if($this->currentToken->type == TokenType::IDENTIFIER) {
            $varNameToken = $this->currentToken;
            $result->registerAdvancement();
            $this->advance();

            if($this->currentToken->type != TokenType::LPAREN) {
                return $result->failure(new InvalidSyntaxError(
                    $this->currentToken->posStart,
                    $this->currentToken->posEnd,
                    "Expected '(' after 'IDENTIFIER'"
                ));
            }
        } else {
            $varNameToken = null;
            if($this->currentToken->type != TokenType::LPAREN) {
                return $result->failure(new InvalidSyntaxError(
                    $this->currentToken->posStart,
                    $this->currentToken->posEnd,
                    "Expected '(' or 'IDENTIFIER'"
                ));
            }
        }

        $result->registerAdvancement();
        $this->advance();

        $argNameTokens = [];

        if($this->currentToken->type == TokenType::IDENTIFIER) {
            array_push($argNameTokens, $this->currentToken);
            $result->registerAdvancement();
            $this->advance();

            while ($this->currentToken->type == TokenType::COMMA) {
                $result->registerAdvancement();
                $this->advance();
                if($this->currentToken->type != TokenType::IDENTIFIER) {
                    return $result->failure(new InvalidSyntaxError(
                        $this->currentToken->posStart,
                        $this->currentToken->posEnd,
                        "Expected 'IDENTIFIER'"
                    ));
                }
                array_push($argNameTokens, $this->currentToken);
                $result->registerAdvancement();
                $this->advance();

                if($this->currentToken->type != TokenType::RPAREN) {
                    return $result->failure(new InvalidSyntaxError(
                        $this->currentToken->posStart,
                        $this->currentToken->posEnd,
                        "Expected ',' or ')'"
                    ));
                }
            }
        } else {
            if($this->currentToken->type != TokenType::RPAREN) {
                return $result->failure(new InvalidSyntaxError(
                    $this->currentToken->posStart,
                    $this->currentToken->posEnd,
                    "Expected 'identifier' or ')'"
                ));
            }
        }

        $result->registerAdvancement();
        $this->advance();

        if($this->currentToken->type != TokenType::ARROW) {
            return $result->failure(new InvalidSyntaxError(
                $this->currentToken->posStart,
                $this->currentToken->posEnd,
                "Expected '=>'"
            ));
        }

        $result->registerAdvancement();
        $this->advance();
        $nodeToReturn = $result->register($this->expr());
        if(!is_null($result->error)) {
            return $result;
        }

        return $result->success(new FuncDefNode($varNameToken, $argNameTokens, $nodeToReturn));
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
