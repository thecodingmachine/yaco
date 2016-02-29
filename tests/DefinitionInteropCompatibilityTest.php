<?php

namespace TheCodingMachine\Yaco;

use Interop\Container\ContainerInterface;
use Interop\Container\Definition\DefinitionProviderInterface;
use Interop\Container\Definition\Test\AbstractDefinitionCompatibilityTest;
use Mouf\Picotainer\Picotainer;

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
     *
     * @return ContainerInterface
     */
    protected function getContainer(DefinitionProviderInterface $definitionProvider)
    {
        $definitions = $definitionProvider->getDefinitions();
        $closures = [];
        foreach ($definitions as $key => $definition) {
            $yacoDefinition = $this->converter->convert($key, $definition);
            $inlineCodeDefinition = $yacoDefinition->toPhpCode('$container', ['$container']);
            $code = $inlineCodeDefinition->getStatements();
            $code .= 'return '.$inlineCodeDefinition->getExpression().";\n";
            $closures[$key] = eval("return function(\$container) {\n".$code.'};');
        }
        $picotainer = new Picotainer($closures);

        return $picotainer;
    }
}
