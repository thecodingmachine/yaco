<?php

namespace TheCodingMachine\Yaco;

use Interop\Container\Definition\DefinitionInterface;
use Interop\Container\Definition\DefinitionProviderInterface;
use TheCodingMachine\ServiceProvider\Registry;
use TheCodingMachine\Yaco\Definition\DumpableInterface;
use TheCodingMachine\Yaco\Definition\InlineEntryInterface;
use TheCodingMachine\Yaco\ServiceProvider\ServiceProviderLoader;

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
     * A registry for registering container-interop's service-providers.
     *
     * @var Registry
     */
    private $registry;

    /**
     * @param Registry|null                     $registry  A registry for registering container-interop's service-providers
     * @param DefinitionConverterInterface|null $converter The object in charge of converting container-interop definitions to our internal standard.
     */
    public function __construct(Registry $registry = null, DefinitionConverterInterface $converter = null)
    {
        if ($registry === null) {
            $registry = new Registry();
        }
        $this->registry = $registry;
        if ($converter === null) {
            $converter = new DefinitionConverter();
        }
        $this->converter = $converter;
    }

    /**
     * Adds a definition to the list of definitions managed by this compiler.
     *
     * @param string              $identifier
     * @param DefinitionInterface $definition
     */
    public function addDefinition($identifier, DefinitionInterface $definition)
    {
        $this->definitions[$identifier] = $definition;
        unset($this->dumpableDefinitions[$identifier]);
    }

    /**
     * Registers a new definition provider.
     *
     * @param DefinitionProviderInterface $definitionProvider
     */
    public function register(DefinitionProviderInterface $definitionProvider)
    {
        foreach ($definitionProvider->getDefinitions() as $identifier => $definition) {
            $this->addDefinition($identifier, $definition);
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
        $id = $dumpableDefinition->getIdentifier();
        if ($id === null) {
            throw new CompilerException('Anonymous instances cannot be directly added to a container.');
        }
        $this->dumpableDefinitions[$id] = $dumpableDefinition;
        unset($this->definitions[$id]);
    }

    /**
     * @param string $identifier
     *
     * @return bool
     */
    public function has($identifier)
    {
        return isset($this->dumpableDefinitions[$identifier]) || isset($this->definitions[$identifier]);
    }

    /**
     * Returns the dumpable definition matching the $identifier.
     *
     * @param string $identifier
     *
     * @return Definition\AliasDefinition|DumpableInterface|Definition\FactoryCallDefinition|Definition\ObjectDefinition|Definition\ParameterDefinition
     *
     * @throws CompilerException
     */
    public function getDumpableDefinition($identifier)
    {
        if (isset($this->dumpableDefinitions[$identifier])) {
            return $this->dumpableDefinitions[$identifier];
        } elseif (isset($this->definitions[$identifier])) {
            return $this->converter->convert($identifier, $this->definitions[$identifier]);
        } else {
            throw new CompilerException(sprintf('Unknown identifier in compiler: "%s"', $identifier));
        }
    }

    /**
     * @param string $className
     *
     * @return string
     */
    public function compile($className)
    {
        // FIXME: 2 successive calls to compile will yield invalid results as the state is modified by "compile".

        // Let's fill the definitions from service providers:
        $serviceProviderLoader = new ServiceProviderLoader($this, $this->converter);
        $serviceProviderLoader->loadFromRegistry($this->registry);

        $classCode = <<<EOF
<?php
%s

use Mouf\Picotainer\Picotainer;
use TheCodingMachine\ServiceProvider\Registry;

class %s extends Picotainer
{
    /**
     * The registry containing service providers.
     * @var Registry
     */
    protected \$registry;

    public function __construct(Registry \$registry = null, ContainerInterface \$delegateLookupContainer = null) {
        parent::__construct([
%s        ], \$delegateLookupContainer);
        \$this->objects = [
%s        ];
        if (\$registry === null) {
            \$registry = new Registry();
        }
        \$this->registry = \$registry;
    }
}

EOF;

        list($shortClassName, $namespaceLine) = $this->splitFQCN($className);

        $closuresCode = '';
        $parametersCode = '';

        $convertedDefinitions = [];
        // Let's merge dumpable definitions with standard definitions.
        foreach ($this->definitions as $identifier => $definition) {
            $convertedDefinitions[$identifier] = $this->converter->convert($identifier, $definition);
        }

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
