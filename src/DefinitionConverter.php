<?php

namespace TheCodingMachine\Yaco;

use Interop\Container\Definition\DefinitionInterface;
use Interop\Container\Definition\FactoryCallDefinitionInterface;
use Interop\Container\Definition\ObjectDefinitionInterface;
use Interop\Container\Definition\ParameterDefinitionInterface;
use Interop\Container\Definition\ReferenceDefinitionInterface;
use TheCodingMachine\Yaco\Definition\AliasDefinition;
use TheCodingMachine\Yaco\Definition\FactoryCallDefinition;
use TheCodingMachine\Yaco\Definition\ObjectDefinition;
use TheCodingMachine\Yaco\Definition\ParameterDefinition;
use TheCodingMachine\Yaco\Definition\Reference;

/**
 * This class is in charge of converting definitions from the Interop\Container\DefinitionInterface to the
 * internal DumpableInterface format.
 */
class DefinitionConverter implements DefinitionConverterInterface
{
    /**
     * @param string                    $identifier
     * @param DefinitionInterface|mixed $definition
     *
     * @return AliasDefinition|FactoryCallDefinition|ObjectDefinition|ParameterDefinition
     */
    public function convert($identifier, $definition)
    {
        if ($definition instanceof ObjectDefinitionInterface) {
            $yacoObjectDefinition = new ObjectDefinition($identifier,
                $definition->getClassName(),
                $this->convertArguments($definition->getConstructorArguments()));

            foreach ($definition->getPropertyAssignments() as $assignment) {
                $yacoObjectDefinition->setProperty($assignment->getPropertyName(), $this->convertValue($assignment->getValue()));
            }

            foreach ($definition->getMethodCalls() as $methodCall) {
                $yacoObjectDefinition->addMethodCall($methodCall->getMethodName(), $this->convertArguments($methodCall->getArguments()));
            }

            return $yacoObjectDefinition;
        } elseif ($definition instanceof FactoryCallDefinitionInterface) {
            return new FactoryCallDefinition($identifier,
                $this->convertValue($definition->getFactory()),
                $definition->getMethodName(),
                $this->convertArguments($definition->getArguments()));
        } elseif ($definition instanceof ParameterDefinitionInterface) {
            return new ParameterDefinition($identifier, $this->convertValue($definition->getValue()));
        } elseif ($definition instanceof ReferenceDefinitionInterface) {
            if ($identifier !== null) {
                return new AliasDefinition($identifier, $definition->getTarget());
            } else {
                return new Reference($definition->getTarget());
            }
        } elseif (is_scalar($definition) || is_array($definition)) {
            return new ParameterDefinition($identifier, $this->convertValue($definition));
        } else {
            throw new \RuntimeException(sprintf('Cannot convert object of type "%s"', get_class($definition)));
        }
    }

    /**
     * @param array $arguments
     *
     * @return array
     */
    private function convertArguments(array $arguments)
    {
        $yacoArguments = [];
        foreach ($arguments as $argument) {
            $yacoArguments[] = $this->convertValue($argument);
        }

        return $yacoArguments;
    }

    private function convertValue($value)
    {
        if (is_array($value)) {
            return array_map([$this, 'convertValue'], $value);
        } elseif ($value instanceof DefinitionInterface) {
            return $this->convert(null, $value);
        } elseif (is_object($value) || is_resource($value)) {
            throw new \RuntimeException('Unable to convert a definition. Parameters cannot be an object or a resource.');
        } else {
            return $value;
        }
    }
}
