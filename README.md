[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/thecodingmachine/yaco/badges/quality-score.png?b=0.3)](https://scrutinizer-ci.com/g/thecodingmachine/yaco/?branch=0.3)
[![Build Status](https://travis-ci.org/thecodingmachine/yaco.svg?branch=0.3)](https://travis-ci.org/thecodingmachine/yaco)
[![Coverage Status](https://coveralls.io/repos/thecodingmachine/yaco/badge.svg?branch=0.3&service=github)](https://coveralls.io/github/thecodingmachine/yaco?branch=0.3)

# YACO - Yet another compiler

YACO (Yet Another COmpiler) is a PHP tool that generates a PHP container based on entry definitions.
It is fully compatible with entry definitions from [*definition-interop*](https://github.com/container-interop/definition-interop/).

## Installation

You can install this package through Composer:

```json
{
    "require": {
        "thecodingmachine/yaco": "~0.3.0"
    }
}
```

The packages adheres to the [SemVer](http://semver.org/) specification, and there will be full backward compatibility
between minor versions.

## Usage

This package contains a `Compiler` class. The goal of this class is to take a number of "entry definitions"
(as defined in [*definition-interop*](https://github.com/container-interop/definition-interop/)) and to transform those
into a PHP class that implements the  [`ContainerInterface`](https://github.com/container-interop/container-interop/)

```php
use TheCodingMachine\Yaco\Compiler;

$compiler = new Compiler();

// ...

foreach ($definitions as $definition) {
    /* @var $definition Interop\Container\Definition\DefinitionInterface */
    $compiler->addDefinition($definition);
}

// Let's dump the code of the My\Container class.
file_put_contents("Container.php", $compiler->compile("My\\Container"));
```

You can also directly register a **definition provider** using the *register* method:

```php
use TheCodingMachine\Yaco\Compiler;

$compiler = new Compiler();

// ...

$compiler->register($definitionProvider);

// Let's dump the code of the My\Container class.
file_put_contents("Container.php", $compiler->compile("My\\Container"));
```
