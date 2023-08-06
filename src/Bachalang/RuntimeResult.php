<?php

declare(strict_types=1);

namespace Bachalang;

use Bachalang\Errors\RuntimeError;
use Bachalang\Values\Number;

class RuntimeResult
{
    public function __construct(
        public ?RuntimeError $error = null,
        public $result = null,
    ) {
    }

    public function register(RuntimeResult $res)
    {
        if($res->error === null) {
            return $res->result;
        } else {
            $this->error = $res->error;
            return $res;
        }
    }

    public function success($result)
    {
        $this->result = $result;
        return $this;
    }

    public function failure($error)
    {
        $this->error = $error;
        return $this;
    }
}
