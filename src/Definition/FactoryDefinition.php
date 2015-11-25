<?php

namespace TheCodingMachine\Yaco\Definition;

/**
 * This class represents an instance declared using a call to a method (a factory) from an existing service.
 * The method can be passed any number of arguments.
 */
class FactoryDefinition implements DumpableInterface
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
     * @var ReferenceInterface
     */
    private $reference;

    /**
     * The name of the method to be called.
     *
     * @var string
     */
    private $methodName;

    /**
     * A list of arguments passed to the constructor.
     *
     * @var array Array of scalars or ReferenceInterface, or array mixing scalars, arrays, and ReferenceInterface
     */
    private $methodArguments = array();

    /**
     * Constructs an factory definition.
     *
     * @param string|null        $identifier      The identifier of the instance in the container. Can be null if the instance is anonymous (declared inline of other instances)
     * @param ReferenceInterface $reference       A pointer to the service that the factory method will be called upon
     * @param string             $methodName      The name of the factory method
     * @param array              $methodArguments The parameters of the factory method
     */
    public function __construct($identifier, ReferenceInterface $reference, $methodName, array $methodArguments = [])
    {
        $this->identifier = $identifier;
        $this->reference = $reference;
        $this->methodName = $methodName;
        $this->methodArguments = $methodArguments;
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
     * Returns a pointer to the service that the factory method will be called upon.
     *
     * @return ReferenceInterface
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * Returns the name of the factory method.
     *
     * @return string
     */
    public function getMethodName()
    {
        return $this->methodName;
    }

    /**
     * Returns the parameters of the factory method.
     *
     * @return array
     */
    public function getMethodArguments()
    {
        return $this->methodArguments;
    }

    /**
     * Adds an argument to the method.
     *
     * @param mixed $argument
     */
    public function addMethodArgument($argument)
    {
        $this->methodArguments[] = $argument;
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
        $dumpedArguments = ValueUtils::dumpArguments($this->methodArguments, $containerVariable, $usedVariables);
        $prependedCode = $dumpedArguments->getStatements();
        $code = sprintf("%s->get(%s)->%s(%s);\n", $containerVariable, var_export($this->reference->getTarget(), true), $this->methodName, $dumpedArguments->getExpression());

        return new InlineEntry($code, $prependedCode, $usedVariables);
    }
}
