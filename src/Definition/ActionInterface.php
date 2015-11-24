<?php
namespace TheCodingMachine\Yaco\Definition;


/**
 * Classes implementing ActionInterface represent a line of PHP code that is an action performed on an object.
 * This can be a method call or a public property assignment.
 */
interface ActionInterface
{

    /**
     * Generates PHP code for the line.
     * @param string $variableName
     * @param string $containerVariable
     * @param array $usedVariables
     * @return InlineEntryInterface
     */
    public function toPhpCode($variableName, $containerVariable, array $usedVariables);
}
