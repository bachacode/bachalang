<?php

declare(strict_types=1);

namespace Bachalang;

use Bachalang\Errors\RuntimeError;

class RuntimeResult
{
    public function __construct(
        public ?RuntimeError $error = null,
        public $value = null,
    ) {
    }

    public function register($res)
    {
        if($res->error === null) {
            return $res->value;
        } else {
            $this->error = $res->error;
            return $res;
        }
    }

    public function success($value)
    {
        $this->value = $value;
        return $this;
    }

    public function failure($error)
    {
        $this->error = $error;
        return $this;
    }
}
