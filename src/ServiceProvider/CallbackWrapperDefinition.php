<?php
namespace TheCodingMachine\Yaco\ServiceProvider;

use TheCodingMachine\Yaco\Definition\DumpableInterface;
use TheCodingMachine\Yaco\Definition\InlineEntry;
use TheCodingMachine\Yaco\Definition\InlineEntryInterface;

/**
 * Wraps a definition into a callback (to lazy load it easily)
 */
class CallbackWrapperDefinition implements DumpableInterface
{
    /**
     * The identifier of the instance in the container.
     *
     * @var string|null
     */
    private $identifier;

    /**
     * @var DumpableInterface
     */
    private $wrappedDefinition;

    /**
     * @param null|string $identifier
     * @param DumpableInterface $wrappedDefinition
     */
    public function __construct($identifier, DumpableInterface $wrappedDefinition)
    {
        $this->identifier = $identifier;
        $this->wrappedDefinition = $wrappedDefinition;
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
        $innerEntry = $this->wrappedDefinition->toPhpCode($containerVariable, $usedVariables);
        $code = sprintf('function() use (%s) {
    %s
    return %s;
}', $containerVariable, $innerEntry->getStatements(), $innerEntry->getExpression());
        return new InlineEntry($code, null, $usedVariables);
    }
}
