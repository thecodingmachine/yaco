<?php

namespace TheCodingMachine\Yaco;

use Interop\Container\Definition\DefinitionInterface;
use TheCodingMachine\Yaco\Definition\DumpableInterface;

/**
 * Classes implenting this interface are in charge of converting definitions from the
 * Interop\Container\DefinitionInterface to theinternal DumpableInterface format.
 */
interface DefinitionConverterInterface
{
    /**
     * Converts a definition from container-interop's definition format to Yaco internal format.
     *
     * @param string|null               $identifier The container entry identifier
     * @param DefinitionInterface|mixed $definition The container entry definition
     *
     * @return DumpableInterface
     */
    public function convert(?string $identifier, $definition);
}
