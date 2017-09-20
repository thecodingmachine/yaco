<?php

namespace TheCodingMachine\Yaco\Definition;

use SuperClosure\Analyzer\TokenAnalyzer;

/**
 * This class represents a closure.
 * Important! The code of the closure will be COPIED, not referenced.
 */
class ClosureDefinition implements DumpableInterface
{
    /**
     * The identifier of the instance in the container.
     *
     * @var string|null
     */
    private $identifier;

    /**
     * The closure.
     *
     * @var \Closure
     */
    private $closure;

    /**
     * Constructs an instance definition.
     *
     * @param string|null $identifier The identifier of the entry in the container. Can be null if the entry is anonymous (declared inline in other instances)
     * @param \Closure    $closure    The closure. It should not contain context (i.e. no "use" keyword in the closure definition). It should accept one compulsory parameter: the container.
     */
    public function __construct(?string $identifier, \Closure $closure)
    {
        $this->identifier = $identifier;
        $this->closure = $closure;
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
     * Returns the closure of the parameter.
     *
     * @return mixed
     */
    public function getClosure()
    {
        return $this->closure;
    }

    /**
     * Returns an InlineEntryInterface object representing the PHP code necessary to generate
     * the container entry.
     *
     * @param string $containerVariable The name of the variable that allows access to the container instance. For instance: "$container", or "$this->container"
     * @param array  $usedVariables     An array of variables that are already used and that should not be used when generating this code.
     *
     * @return InlineEntryInterface
     *
     * @throws DefinitionException
     */
    public function toPhpCode(string $containerVariable, array $usedVariables = array()): InlineEntryInterface
    {
        // TODO: not optimal compared to previous interface!!!
        $analyzer = new TokenAnalyzer();
        $analysis = $analyzer->analyze($this->closure);

        if ($analysis['hasThis']) {
            throw new DefinitionException('Your closure cannot call the $this keyword.');
        }
        if (!empty($analysis['context'])) {
            throw new DefinitionException('Your closure cannot have a context (i.e. cannot have a "use" keyword).');
        }
        $code = $analysis['code'];
        $variableName = VariableUtils::getNextAvailableVariableName("\$closure", $usedVariables);
        $usedVariables[] = $variableName;
        $assignClosure = sprintf('%s = %s;', $variableName, $code);

        return new InlineEntry($variableName.'('.$containerVariable.')', $assignClosure, $usedVariables);
    }
}
