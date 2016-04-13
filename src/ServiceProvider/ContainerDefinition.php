<?php


namespace TheCodingMachine\Yaco\ServiceProvider;


use TheCodingMachine\Yaco\Definition\DumpableInterface;
use TheCodingMachine\Yaco\Definition\InlineEntry;
use TheCodingMachine\Yaco\Definition\InlineEntryInterface;

/**
 * A definition that points to "$this" container.
 */
class ContainerDefinition implements DumpableInterface
{
    /**
     * The identifier of the instance in the container.
     *
     * @var string|null
     */
    private $identifier;

    /**
     * @param null|string $identifier
     */
    public function __construct($identifier = null)
    {
        $this->identifier = $identifier;
    }

    /**
     * Returns the identifier for this object in the container.
     * If null, classes consuming this definition should assume the definition must be inlined.
     *
     * @return string|null
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Returns an InlineEntryInterface object representing the PHP code necessary to generate
     * the container entry.
     *
     * @param string $containerVariable The name of the variable that allows access to the container instance. For instance: "$container", or "$this->container"
     * @param array $usedVariables An array of variables that are already used and that should not be used when generating this code.
     *
     * @return InlineEntryInterface
     */
    public function toPhpCode($containerVariable, array $usedVariables = array())
    {
        return new InlineEntry($containerVariable, null, $usedVariables);
    }
}
