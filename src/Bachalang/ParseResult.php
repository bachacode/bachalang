<?php

declare(strict_types=1);

namespace Bachalang;

use Bachalang\Errors\InvalidSyntaxError;
use Bachalang\Nodes\BinOpNode;
use Bachalang\Nodes\NumberNode;

class ParseResult
{
    public function __construct(
        public ?InvalidSyntaxError $error = null,
        public NumberNode|BinOpNode|null $node = null,
    ) {
    }

    public function register($res)
    {
        if($res instanceof ParseResult) {
            if($res->error === null) {
                return $res->node;
            } else {
                $this->error = $res->error;
                return $res;
            }
        }
        return $res;
    }

    public function success($node)
    {
        $this->node = $node;
        return $this;
    }

    public function failure($error)
    {
        $this->error = $error;
        return $this;
    }
}
