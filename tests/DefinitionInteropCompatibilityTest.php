<?php

namespace TheCodingMachine\Yaco;

use Assembly\ObjectInitializer\MethodCall;
use Assembly\ObjectInitializer\PropertyAssignment;
use Assembly\Reference;
use Interop\Container\ContainerInterface;
use Interop\Container\Definition\DefinitionProviderInterface;
use Interop\Container\Definition\Test\AbstractDefinitionCompatibilityTest;
use Mouf\Picotainer\Picotainer;
use TheCodingMachine\Yaco\Definition\AbstractDefinitionTest;

class DefinitionInteropCompatibilityTest extends AbstractDefinitionCompatibilityTest
{
    /**
     * @var DefinitionConverterInterface
     */
    private $converter;

    public function setUp()
    {
        parent::setUp();
        $this->converter = new DefinitionConverter();
    }

    /**
     * Takes a definition provider in parameter and returns a container containing the entries.
     *
     * @param DefinitionProviderInterface $definitionProvider
     * @return ContainerInterface
     */
    protected function getContainer(DefinitionProviderInterface $definitionProvider)
    {
        $definitions = $definitionProvider->getDefinitions();
        $closures = [];
        foreach ($definitions as $definition) {
            $key = $definition->getIdentifier();
            $yacoDefinition = $this->converter->convert($definition);
            $inlineCodeDefinition = $yacoDefinition->toPhpCode('$container', ['$container']);
            $code = $inlineCodeDefinition->getStatements();
            $code .= 'return '.$inlineCodeDefinition->getExpression().";\n";
            $closures[$key] = eval("return function(\$container) {\n".$code.'};');
        }
        $picotainer = new Picotainer($closures);

        return $picotainer;
    }
}
