[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/thecodingmachine/yaco/badges/quality-score.png?b=0.2)](https://scrutinizer-ci.com/g/thecodingmachine/yaco/?branch=0.2)
[![Build Status](https://travis-ci.org/thecodingmachine/yaco.svg?branch=0.2)](https://travis-ci.org/thecodingmachine/yaco)
[![Coverage Status](https://coveralls.io/repos/thecodingmachine/yaco/badge.svg?branch=0.2&service=github)](https://coveralls.io/github/thecodingmachine/yaco?branch=0.2)

# YACO - Yet another compiler

YACO (Yet Another COmpiler) is a PHP tool that generates a PHP container based on entry definitions.
Entry definitions must be compatible with interfaces defined in [*compiler-interop*](https://github.com/container-interop/compiler-interop/)

## Installation

You can install this package through Composer:

```json
{
    "require": {
        "thecodingmachine/yaco": "~0.2.0"
    }
}
```

The packages adheres to the [SemVer](http://semver.org/) specification, and there will be full backward compatibility
between minor versions.

## Usage

This package contains a `Compiler` class. The goal of this class is to take a number of "entry definitions"
(as defined in [*compiler-interop*](https://github.com/container-interop/compiler-interop/)) and to transform those
into a PHP class that implements the  [`ContainerInterface`](https://github.com/container-interop/container-interop/)

```php
use TheCodingMachine\Yaco\Compiler;

$compiler = new Compiler();

// ...

foreach ($definitions as $definition) {
    /* @var $definition TheCodingMachine\Yaco\Definition\DumpableInterface */
    $compiler->addDefinition($definition);
}

// Let's dump the code of the My\Container class.
file_put_contents("Container.php", $compiler->compile("My\\Container"));
```
