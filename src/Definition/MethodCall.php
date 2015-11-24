<?php
namespace TheCodingMachine\Yaco\Definition;

/**
 * Represents a call to a method.
 */
class MethodCall implements ActionInterface
{
    /**
     * The name of the method
     *
     * @var string
     */
    private $methodName;

    /**
     * A list of arguments passed to the constructor.
     *
     * @var array Array of scalars or ReferenceInterface, or array mixing scalars, arrays, and ReferenceInterface
     */
    private $arguments = array();

    /**
     * MethodCall constructor.
     * @param string $methodName
     * @param array $arguments
     */
    public function __construct($methodName, array $arguments = array())
    {
        $this->methodName = $methodName;
        $this->arguments = $arguments;
    }

    /**
     * Adds an argument to the list of arguments to be passed to the method.
     * @param mixed $argument
     * @return self
     */
    public function addArgument($argument) {
        $this->arguments[] = $argument;
        return $this;
    }


    /**
     * Generates PHP code for the line.
     * @param string $variableName
     * @param string $containerVariable
     * @param array $usedVariables
     * @return InlineEntryInterface
     */
    public function toPhpCode($variableName, $containerVariable, array $usedVariables) {
        $dumpedArguments = ValueUtils::dumpArguments($this->arguments, $containerVariable, $usedVariables);
        $codeLine = sprintf("%s->%s(%s);", $variableName, $this->methodName, $dumpedArguments->getExpression());

        return new InlineEntry("", $dumpedArguments->getStatements().$codeLine, $dumpedArguments->getUsedVariables());
    }
}
