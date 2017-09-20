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
     * @var string|null
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
     * @param mixed      $value      The value of the parameter. It is expected to be a scalar or an array (or more generally anything that can be `var_export`ed)
     */
    public function __construct(?string $identifier, $value)
    {
        $this->identifier = $identifier;
        $this->value = $value;
    }

    /**
     * Returns the identifier of the instance.
     *
     * @return string
     */
    public function getIdentifier(): ?string
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
    public function toPhpCode(string $containerVariable, array $usedVariables = array()): InlineEntryInterface
    {
        return ValueUtils::dumpValue($this->value, $containerVariable, $usedVariables);
    }
}
