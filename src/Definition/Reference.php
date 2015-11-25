<?php


namespace TheCodingMachine\Yaco\Definition;

/**
 * A reference to a service.
 */
class Reference implements ReferenceInterface
{
    /**
     * @var string
     */
    private $target;

    /**
     * Constructs a Reference object targeting the $target entry.
     *
     * @param string $target
     */
    public function __construct($target)
    {
        $this->target = $target;
    }


    /**
     * Returns the identifier for the object in the container.
     *
     * @return string
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * Returns an InlineEntryInterface object representing the PHP code necessary to generate
     * the container entry.
     *
     * @param string $containerVariable The name of the variable that allows access to the container instance. For instance: "$container", or "$this->container"
     * @param array $usedVariables An array of variables that are already used and that should not be used when generating this code.
     * @return InlineEntryInterface
     */
    public function toPhpCode($containerVariable, array $usedVariables = array())
    {
        return new InlineEntry(sprintf("%s->get(%s)", $containerVariable, var_export($this->getTarget(), true)), null, $usedVariables);
    }
}
