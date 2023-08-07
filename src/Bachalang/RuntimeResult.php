<?php

declare(strict_types=1);

namespace Bachalang;

use Bachalang\Errors\RuntimeError;
use Bachalang\Values\Value;

class RuntimeResult
{
    public function __construct(
        public ?RuntimeError $error = null,
        public $result = null,
    ) {
    }

    public function register(RuntimeResult $res): RuntimeResult|Value
    {
        if($res->error == null) {
            return $res->result;
        } else {
            $this->error = $res->error;
            return $res;
        }
    }

    public function success(Value $result): RuntimeResult
    {
        $this->result = $result;
        return $this;
    }

    public function failure(RuntimeError $error): RuntimeResult
    {
        $this->error = $error;
        return $this;
    }
}
