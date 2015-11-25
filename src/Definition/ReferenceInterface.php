<?php
namespace TheCodingMachine\Yaco\Definition;


/**
 * A reference to a service.
 */
interface ReferenceInterface extends \Interop\Container\Definition\ReferenceInterface
{

    /**
     * Returns an InlineEntryInterface object representing the PHP code necessary to generate
     * the container entry.
     *
     * @param string $containerVariable The name of the variable that allows access to the container instance. For instance: "$container", or "$this->container"
     * @param array $usedVariables An array of variables that are already used and that should not be used when generating this code.
     * @return InlineEntryInterface
     */
    public function toPhpCode($containerVariable, array $usedVariables = array());
}