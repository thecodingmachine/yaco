<?php


namespace TheCodingMachine\Yaco\Definition\Fixtures;


use Assembly\InstanceDefinition;
use Interop\Container\Definition\DefinitionInterface;
use Interop\Container\Definition\DefinitionProviderInterface;

class TestDefinitionProvider implements DefinitionProviderInterface
{

    /**
     * Returns the definition to register in the container.
     *
     * @return DefinitionInterface[]
     */
    public function getDefinitions()
    {
        return [
            new InstanceDefinition('test', '\\stdClass')
        ];
    }
}