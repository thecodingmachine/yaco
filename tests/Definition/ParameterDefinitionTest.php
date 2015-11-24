<?php
namespace TheCodingMachine\Yaco\Definition;

class ParameterDefinitionTest extends AbstractDefinitionTest
{

    public function testGetters() {
        $parameterDefinition = new ParameterDefinition("test", "value");

        $this->assertEquals("test", $parameterDefinition->getIdentifier());
        $this->assertEquals("value", $parameterDefinition->getValue());
    }

    public function testSimpleEncode() {
        $parameterDefinition = new ParameterDefinition("test", "value");
        $inlineEntry = $parameterDefinition->toPhpCode('$container', []);
        $this->assertEquals("'value'", $inlineEntry->getExpression());
        $this->assertEquals(false, $inlineEntry->isLazilyEvaluated());
        $this->assertEquals(null, $inlineEntry->getStatements());
    }

    public function testInlineParameterDeclaration() {
        // null passed as first parameter. This will generate an inline declaration.
        $dependencyDefinition = new ParameterDefinition(null, "hello");

        $instanceDefinition = new InstanceDefinition("test", "TheCodingMachine\\Yaco\\Definition\\Fixtures\\Test");
        $instanceDefinition->addConstructorArgument($dependencyDefinition);

        $container = $this->getContainer([
            "test" => $instanceDefinition
        ]);
        $result = $container->get("test");

        $this->assertInstanceOf("TheCodingMachine\\Yaco\\Definition\\Fixtures\\Test", $result);
        $this->assertEquals("hello", $result->cArg1);
    }
}
