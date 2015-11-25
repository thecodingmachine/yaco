<?php
namespace TheCodingMachine\Yaco\Definition\Fixtures;


class TestFactory
{

    private $arg;

    public function __construct($arg) {
        $this->arg = $arg;
    }

    public function getTest($arg2 = null) {
        return new Test($this->arg, $arg2);
    }
}
