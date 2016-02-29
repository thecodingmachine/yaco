<?php

namespace TheCodingMachine\Yaco\Definition;

use TheCodingMachine\Yaco\Definition\Fixtures\Test;

class FactoryCallDefinitionTest extends AbstractDefinitionTest
{
    public function testGetters()
    {
        $factoryDefinition = new FactoryCallDefinition('test', new Reference('factory'), 'getTest', [42]);

        $this->assertEquals('test', $factoryDefinition->getIdentifier());
        $this->assertEquals('factory', $factoryDefinition->getFactory()->getTarget());
        $this->assertEquals('getTest', $factoryDefinition->getMethodName());
        $this->assertEquals([42], $factoryDefinition->getMethodArguments());
        $factoryDefinition->addMethodArgument(43);
        $this->assertEquals([42, 43], $factoryDefinition->getMethodArguments());
    }

    public function testFactory()
    {
        $factoryDefinition = new ObjectDefinition('factory', 'TheCodingMachine\\Yaco\\Definition\\Fixtures\\TestFactory', [43]);

        $instanceDefinition = new FactoryCallDefinition('test', new Reference('factory'), 'getTest', [42]);

        $container = $this->getContainer([
            'test' => $instanceDefinition,
            'factory' => $factoryDefinition,
        ]);
        $result = $container->get('test');

        $this->assertInstanceOf('TheCodingMachine\\Yaco\\Definition\\Fixtures\\Test', $result);
        $this->assertEquals(43, $result->cArg1);
        $this->assertEquals(42, $result->cArg2);
    }

    public function testStaticFactory()
    {
        $instanceDefinition = new FactoryCallDefinition('test', 'TheCodingMachine\Yaco\Definition\Fixtures\TestFactory', 'getStaticTest', [42]);

        $container = $this->getContainer([
            'test' => $instanceDefinition,
        ]);
        $result = $container->get('test');

        $this->assertInstanceOf('TheCodingMachine\\Yaco\\Definition\\Fixtures\\Test', $result);
        $this->assertEquals(42, $result->cArg1);
    }
}
