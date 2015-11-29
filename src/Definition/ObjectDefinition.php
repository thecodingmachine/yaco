<?php

namespace TheCodingMachine\Yaco\Definition;

/**
 * This class represents an instance declared using the "new" keyword followed by an optional list of
 * method calls and properties assignations.
 */
class ObjectDefinition implements DumpableInterface
{
    /**
     * The identifier of the instance in the container.
     *
     * @var string
     */
    private $identifier;

    /**
     * The fully qualified class name of this instance.
     *
     * @var string
     */
    private $className;

    /**
     * A list of arguments passed to the constructor.
     *
     * @var array Array of scalars or ReferenceInterface, or array mixing scalars, arrays, and ReferenceInterface
     */
    private $constructorArguments = array();

    /**
     * A list of actions to be executed (can be either a method call or a public property assignation).
     *
     * @var ActionInterface[]
     */
    private $actions = array();

    /**
     * Constructs an instance definition.
     *
     * @param string|null $identifier           The identifier of the instance in the container. Can be null if the instance is anonymous (declared inline of other instances)
     * @param string      $className            The fully qualified class name of this instance.
     * @param array       $constructorArguments A list of constructor arguments.
     */
    public function __construct($identifier, $className, array $constructorArguments = array())
    {
        $this->identifier = $identifier;
        $this->className = '\\'.ltrim($className, '\\');
        $this->constructorArguments = $constructorArguments;
    }

    /**
     * Returns the identifier of the instance.
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * @return array
     */
    public function getConstructorParameters()
    {
        return $this->constructorArguments;
    }

    /**
     * Adds an argument to the list of arguments to be passed to the constructor.
     *
     * @param mixed $argument
     *
     * @return self
     */
    public function addConstructorArgument($argument)
    {
        $this->constructorArguments[] = $argument;

        return $this;
    }

    /**
     * Adds a method call.
     *
     * @param string $methodName
     * @param array  $arguments
     *
     * @return MethodCall
     */
    public function addMethodCall($methodName, array $arguments = array())
    {
        $this->actions[] = $methodCall = new MethodCall($methodName, $arguments);

        return $methodCall;
    }

    /**
     * Adds a method call.
     *
     * @param string $propertyName
     * @param mixed  $value
     *
     * @return self
     */
    public function setProperty($propertyName, $value)
    {
        $this->actions[] = new PropertyAssignment($propertyName, $value);

        return $this;
    }

    /**
     * Returns an InlineEntryInterface object representing the PHP code necessary to generate
     * the container entry.
     *
     * @param string $containerVariable The name of the variable that allows access to the container instance. For instance: "$container", or "$this->container"
     * @param array  $usedVariables     An array of variables that are already used and that should not be used when generating this code.
     *
     * @return InlineEntryInterface
     */
    public function toPhpCode($containerVariable, array $usedVariables = array())
    {
        if ($this->identifier !== null) {
            $variableName = $this->getIdentifier();
        } else {
            $variableName = $this->className;
        }
        $variableName = VariableUtils::getNextAvailableVariableName(lcfirst($variableName), $usedVariables);

        $usedVariables[] = $variableName;
        $dumpedArguments = ValueUtils::dumpArguments($this->constructorArguments, $containerVariable, $usedVariables);
        $prependedCode = $dumpedArguments->getStatements();
        $code = sprintf("%s = new %s(%s);\n", $variableName, $this->className, $dumpedArguments->getExpression());
        foreach ($this->actions as $action) {
            $inlineCode = $action->toPhpCode($variableName, $containerVariable, $usedVariables);
            $code .= $inlineCode->getStatements()."\n";
            $usedVariables = $inlineCode->getUsedVariables();
        }

        return new InlineEntry($variableName, $prependedCode.$code, $usedVariables);
    }
}
