<?php
namespace TheCodingMachine\Yaco\Definition;

use Mouf\Picotainer\Picotainer;

abstract class AbstractDefinitionTest extends \PHPUnit_Framework_TestCase
{
    protected function getContainer(array $definitions) {
        $closures = [];
        foreach ($definitions as $key => $definition) {
            $inlineCodeDefinition = $definition->toPhpCode('$container', ['$container']);
            $code = $inlineCodeDefinition->getStatements();
            $code .= "return ".$inlineCodeDefinition->getExpression().";\n";
            $closures[$key] = eval("return function(\$container) {\n".$code."};");
        }
        $picotainer = new Picotainer($closures);
        return $picotainer;
    }
}
