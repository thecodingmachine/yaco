<?php

namespace TheCodingMachine\Yaco\Definition;

/**
 * Objects implementing this interface represent PHP code that can be used to create an entry.
 */
interface InlineEntryInterface
{
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
    public function getStatements();

    /**
     * Returns the PHP expression representing the entry.
     * This must be a string representing a valid PHP expression,
     * with no ending ;.
     *
     * For instance, "$service" is a valid PHP expression.
     *
     * @return string
     */
    public function getExpression();

    /**
     * Returns the list of variables used in the process of creating this
     * entry definition. These variables should not be used by other
     * definitions in the same scope.
     *
     * @return array
     */
    public function getUsedVariables();

    /**
     * If true, the entry will be evaluated when the `get` method is called (this is the default)
     * If false, the entry will be evaluated as soon as the container is constructed. This is useful
     * for entries that contain only parameters like strings, or constants.
     *
     * If false, a call to `getStatements` MUST return null.
     *
     * @return bool
     */
    public function isLazilyEvaluated();
}
