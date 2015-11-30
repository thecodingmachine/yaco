<?php

namespace TheCodingMachine\Yaco;

use Interop\Container\Definition\DefinitionInterface;
use Interop\Container\Definition\DefinitionProviderInterface;
use TheCodingMachine\Yaco\Definition\DumpableInterface;
use TheCodingMachine\Yaco\Definition\InlineEntryInterface;

/**
 * A class that generates a PHP class (a container) from definitions.
 */
class Compiler
{
    /**
     * @var DefinitionInterface[]
     */
    private $definitions = [];

    /**
     * @var DumpableInterface[]
     */
    private $dumpableDefinitions = [];

    /**
     * The object in charge of converting container-interop definitions to our internal standard.
     *
     * @var DefinitionConverterInterface
     */
    private $converter;

    /**
     * @param DefinitionConverterInterface $converter The object in charge of converting container-interop definitions to our internal standard.
     */
    public function __construct(DefinitionConverterInterface $converter = null)
    {
        if ($converter === null) {
            $converter = new DefinitionConverter();
        }
        $this->converter = $converter;
    }

    /**
     * Adds a definition to the list of definitions managed by this compiler.
     *
     * @param DefinitionInterface $definition
     */
    public function addDefinition(DefinitionInterface $definition)
    {
        $this->definitions[$definition->getIdentifier()] = $definition;
        unset($this->dumpableDefinitions[$definition->getIdentifier()]);
    }

    /**
     * Registers a new definition provider.
     *
     * @param DefinitionProviderInterface $definitionProvider
     */
    public function register(DefinitionProviderInterface $definitionProvider) {
        foreach ($definitionProvider->getDefinitions() as $definition) {
            $this->addDefinition($definition);
        }
    }

    /**
     * Adds a dumpable definition to the list of definitions managed by this compiler.
     * Note: a "dumpable" definition is a definition represented in Yaco internal format.
     *
     * @param DumpableInterface $dumpableDefinition
     */
    public function addDumpableDefinition(DumpableInterface $dumpableDefinition)
    {
        $this->dumpableDefinitions[$dumpableDefinition->getIdentifier()] = $dumpableDefinition;
        unset($this->definitions[$dumpableDefinition->getIdentifier()]);
    }

    /**
     * @param string $className
     *
     * @return string
     */
    public function compile($className)
    {
        $classCode = <<<EOF
<?php
%s

use Mouf\Picotainer\Picotainer;

class %s extends Picotainer
{
    public function __construct(ContainerInterface \$delegateLookupContainer = null) {
        parent::__construct([
%s        ], \$delegateLookupContainer);
        \$this->objects = [
%s        ];
    }
}

EOF;

        list($shortClassName, $namespaceLine) = $this->splitFQCN($className);

        $closuresCode = '';
        $parametersCode = '';

        // Let's merge dumpable definitions with standard definitions.
        $convertedDefinitions = array_map([$this->converter, 'convert'], $this->definitions);
        $allDefinitions = $convertedDefinitions + $this->dumpableDefinitions;

        foreach ($allDefinitions as $identifier => $definition) {
            $inlineEntry = $definition->toPhpCode('$container', ['$container']);

            if ($inlineEntry->isLazilyEvaluated()) {
                $closuresCode .= '            '.var_export($identifier, true).' => '.$this->getClosureCode($inlineEntry).",\n";
            } else {
                $parametersCode .= '            '.var_export($identifier, true).' => '.$this->getParametersCode($inlineEntry).",\n";
            }
        }

        return sprintf($classCode, $namespaceLine, $shortClassName, $closuresCode, $parametersCode);
    }

    private function splitFQCN($className)
    {
        $pos = strrpos($className, '\\');
        if ($pos !== false) {
            $shortClassName = substr($className, $pos + 1);
            $namespaceLine = 'namespace '.substr($className, 0, $pos).';';
        } else {
            $shortClassName = $className;
            $namespaceLine = '';
        }

        return [
            $shortClassName,
            $namespaceLine,
        ];
    }

    private function getParametersCode(InlineEntryInterface $inlineEntry)
    {
        if (!empty($inlineEntry->getStatements())) {
            throw new CompilerException('An entry that contains parameters (not lazily loaded) cannot return statements.');
        }

        return $inlineEntry->getExpression();
    }

    private function getClosureCode(InlineEntryInterface $inlineEntry)
    {
        $code = $inlineEntry->getStatements();
        $code .= 'return '.$inlineEntry->getExpression().";\n";

        return sprintf("function(\$container) {\n%s}", $code);
    }
}
