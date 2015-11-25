<?php

namespace TheCodingMachine\Yaco\Definition;

/**
 *
 */
class InlineEntry implements InlineEntryInterface
{
    /**
     * @var string
     */
    private $expression;

    /**
     * @var string
     */
    private $statements;

    /**
     * @var string[]
     */
    private $usedVariables;

    /**
     * @var bool
     */
    private $lazyEvaluation;

    /**
     * @param string   $expression
     * @param string   $statements
     * @param string[] $usedVariables
     * @param bool     $lazyEvaluation
     */
    public function __construct($expression, $statements, array $usedVariables, $lazyEvaluation = true)
    {
        $this->expression = $expression;
        $this->statements = $statements;
        $this->usedVariables = $usedVariables;
        $this->lazyEvaluation = $lazyEvaluation;
    }

    /**
     * Returns a list of PHP statements (ending with a ;) that are necessary to
     * build the entry.
     * For instance, these are valid PHP statements:.
     *
     * "$service = new MyService($container->get('my_dependency'));
     * $service->setStuff('foo');"
     *
     * Can be null or empty if no statements need to be returned.
     *
     * @return string|null
     */
    public function getStatements()
    {
        return $this->statements;
    }

    /**
     * Returns the PHP expression representing the entry.
     * This must be a string representing a valid PHP expression,
     * with no ending ;.
     *
     * For instance, "$service" is a valid PHP expression.
     *
     * @return string
     */
    public function getExpression()
    {
        return $this->expression;
    }

    /**
     * Returns the list of variables used in the process of creating this
     * entry definition. These variables should not be used by other
     * definitions in the same scope.
     *
     * @return array
     */
    public function getUsedVariables()
    {
        return $this->usedVariables;
    }

    /**
     * If true, the entry will be evaluated when the `get` method is called (this is the default)
     * If false, the entry will be evaluated as soon as the container is constructed. This is useful
     * for entries that contain only parameters like strings, or constants.
     *
     * @return bool
     */
    public function isLazilyEvaluated()
    {
        return $this->lazyEvaluation;
    }
}
