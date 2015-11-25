<?php

namespace TheCodingMachine\Yaco\Definition\Fixtures;

class Test
{
    public $cArg1;
    public $cArg2;

    public function __construct($cArg1 = null, $cArg2 = null)
    {
        $this->cArg1 = $cArg1;
        $this->cArg2 = $cArg2;
    }

    public function setArg1($arg1)
    {
        $this->cArg1 = $arg1;
    }
}
