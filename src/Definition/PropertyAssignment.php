<?php
namespace TheCodingMachine\Yaco\Definition;

/**
 * Represents an assignment of a property
 */
class PropertyAssignment implements ActionInterface
{
    /**
     * The name of the property
     *
     * @var string
     */
    private $propertyName;

    /**
     * The value to assign to the property.
     *
     * @var mixed
     */
    private $value;

    /**
     * @param string $propertyName
     * @param mixed $value
     */
    public function __construct($propertyName, $value)
    {
        $this->propertyName = $propertyName;
        $this->value = $value;
    }

    /**
     * Generates PHP code for the line.
     * @param string $variableName
     * @param string $containerVariable
     * @param array $usedVariables
     * @return InlineEntryInterface
     */
    public function toPhpCode($variableName, $containerVariable, array $usedVariables)
    {
        $inlineEntry = ValueUtils::dumpValue($this->value, $containerVariable, $usedVariables);
        $codeLine = sprintf("%s->%s = %s;", $variableName, $this->propertyName, $inlineEntry->getExpression());
        return new InlineEntry("", $inlineEntry->getStatements().$codeLine, $inlineEntry->getUsedVariables());
    }
}
