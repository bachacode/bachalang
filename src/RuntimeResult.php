<?php

declare(strict_types=1);

namespace Bachalang;

use Bachalang\Errors\RuntimeError;
use Bachalang\Values\Value;

class RuntimeResult
{
    public function __construct(
        public ?RuntimeError $error = null,
        public ?Value $result = null,
        public $funcReturnValue = null,
        public bool $loopShouldContinue = false,
        public bool $loopShouldBreak = false,
    ) {
    }

    public function reset(): void
    {
        $this->error = null;
        $this->result = null;
        $this->funcReturnValue = null;
        $this->loopShouldContinue = false;
        $this->loopShouldBreak = false;
    }

    public function register(RuntimeResult $res): ?Value
    {
        $this->error = $res->error;
        $this->funcReturnValue = $res->funcReturnValue;
        $this->loopShouldContinue = $res->loopShouldContinue;
        $this->loopShouldBreak = $res->loopShouldBreak;
        return $res->result;
    }

    public function success(?Value $result): RuntimeResult
    {
        $this->reset();
        $this->result = $result;
        return $this;
    }

    public function successReturn($result): RuntimeResult
    {
        $this->reset();
        $this->funcReturnValue = $result;
        return $this;
    }

    public function successContinue(): RuntimeResult
    {
        $this->reset();
        $this->loopShouldContinue = true;
        return $this;
    }

    public function successBreak(): RuntimeResult
    {
        $this->reset();
        $this->loopShouldBreak = true;
        return $this;
    }

    public function failure(RuntimeError $error): RuntimeResult
    {
        $this->reset();
        $this->error = $error;
        return $this;
    }

    public function shouldReturn()
    {
        return (
            !is_null($this->error) ||
            !is_null($this->funcReturnValue) ||
            $this->loopShouldContinue ||
            $this->loopShouldBreak
        );
    }
}
