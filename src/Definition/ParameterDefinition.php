<?php

namespace TheCodingMachine\Yaco\Definition;

/**
 * This class represents a parameter.
 */
class ParameterDefinition implements DumpableInterface
{
    /**
     * The identifier of the instance in the container.
     *
     * @var string
     */
    private $identifier;

    /**
     * The value of the parameter.
     * It is expected to be a scalar or an array (or more generally anything that can be `var_export`ed).
     *
     * @var mixed
     */
    private $value;

    /**
     * Constructs an instance definition.
     *
     * @param string|null $identifier The identifier of the entry in the container. Can be null if the entry is anonymous (declared inline in other instances)
     * @param string      $value      The value of the parameter. It is expected to be a scalar or an array (or more generally anything that can be `var_export`ed)
     */
    public function __construct($identifier, $value)
    {
        $this->identifier = $identifier;
        $this->value = $value;
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
     * Returns the value of the parameter.
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
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
        return ValueUtils::dumpValue($this->value, $containerVariable, $usedVariables);
        //return new InlineEntry(ValueUtils::dumpValue($this->value, $containerVariable, $usedVariables), null, $usedVariables, false);
    }
}
