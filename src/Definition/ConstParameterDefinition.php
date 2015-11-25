<?php

namespace TheCodingMachine\Yaco\Definition;

/**
 * This class represents a constant parameter (a "define" or a const in a class).
 */
class ConstParameterDefinition implements DumpableInterface
{
    /**
     * The identifier of the instance in the container.
     *
     * @var string
     */
    private $identifier;

    /**
     * The name of the constant. If it is a class constant, please pass the FQDN. For instance: "My\Class::CONSTANT".
     *
     * @var string
     */
    private $const;

    /**
     * Constructs an instance definition.
     *
     * @param string|null $identifier The identifier of the entry in the container. Can be null if the entry is anonymous (declared inline in other instances)
     * @param string      $const      The name of the constant. If it is a class constant, please pass the FQDN. For instance: "My\Class::CONSTANT"
     */
    public function __construct($identifier, $const)
    {
        $this->identifier = $identifier;
        $this->const = $const;
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
     * The name of the constant. If it is a class constant, please pass the FQDN. For instance: "My\Class::CONSTANT".
     *
     * @return mixed
     */
    public function getConst()
    {
        return $this->const;
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
        return new InlineEntry($this->const, null, $usedVariables, false);
    }
}
