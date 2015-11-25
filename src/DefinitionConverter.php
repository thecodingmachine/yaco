<?php

namespace TheCodingMachine\Yaco;

use Interop\Container\Definition\AliasDefinitionInterface;
use Interop\Container\Definition\DefinitionInterface;
use Interop\Container\Definition\FactoryDefinitionInterface;
use Interop\Container\Definition\InstanceDefinitionInterface;
use Interop\Container\Definition\ParameterDefinitionInterface;
use Interop\Container\Definition\ReferenceInterface;
use TheCodingMachine\Yaco\Definition\AliasDefinition;
use TheCodingMachine\Yaco\Definition\DumpableInterface;
use TheCodingMachine\Yaco\Definition\FactoryDefinition;
use TheCodingMachine\Yaco\Definition\InstanceDefinition;
use TheCodingMachine\Yaco\Definition\ParameterDefinition;
use TheCodingMachine\Yaco\Definition\Reference;

/**
 * This class is in charge of converting definitions from the Interop\Container\DefinitionInterface to the
 * internal DumpableInterface format.
 */
class DefinitionConverter implements DefinitionConverterInterface
{
    /**
     * @param DefinitionInterface $definition
     *
     * @return DumpableInterface
     */
    public function convert(DefinitionInterface $definition)
    {
        if ($definition instanceof InstanceDefinitionInterface) {
            $yacoInstanceDefinition = new InstanceDefinition($definition->getIdentifier(),
                $definition->getClassName(),
                $this->convertArguments($definition->getConstructorArguments()));

            foreach ($definition->getPropertyAssignments() as $assignment) {
                $yacoInstanceDefinition->setProperty($assignment->getPropertyName(), $this->convertValue($assignment->getValue()));
            }

            foreach ($definition->getMethodCalls() as $methodCall) {
                $yacoInstanceDefinition->addMethodCall($methodCall->getMethodName(), $this->convertArguments($methodCall->getArguments()));
            }

            return $yacoInstanceDefinition;
        } elseif ($definition instanceof FactoryDefinitionInterface) {
            return new FactoryDefinition($definition->getIdentifier(),
                $this->convertReference($definition->getReference()),
                $definition->getMethodName());
        } elseif ($definition instanceof ParameterDefinitionInterface) {
            return new ParameterDefinition($definition->getIdentifier(), $definition->getValue());
        } elseif ($definition instanceof AliasDefinitionInterface) {
            return new AliasDefinition($definition->getIdentifier(), $definition->getTarget());
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
            return $this->convertArray($value);
        } elseif ($value instanceof DefinitionInterface) {
            return $this->convert($value);
        } elseif ($value instanceof ReferenceInterface) {
            return $this->convertReference($value);
        } elseif (is_object($value) || is_resource($value)) {
            throw new \RuntimeException('Unable to convert a definition. Parameters cannot be an object or a resource.');
        } else {
            return $value;
        }
    }

    private function convertArray(array $values)
    {
        $result = [];

        foreach ($values as $k => $v) {
            $result[$k] = $this->convertValue($v);
        }

        return $result;
    }

    private function convertReference(ReferenceInterface $reference)
    {
        return new Reference($reference->getTarget());
    }
}
