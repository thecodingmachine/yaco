<?php

namespace TheCodingMachine\Yaco\Definition;

use TheCodingMachine\Yaco\Definition\Fixtures\Test;

class FactoryDefinitionTest extends AbstractDefinitionTest
{
    public function testGetters()
    {
        $factoryDefinition = new FactoryDefinition('test', new Reference('factory'), 'getTest', [42]);

        $this->assertEquals('test', $factoryDefinition->getIdentifier());
        $this->assertEquals('factory', $factoryDefinition->getReference()->getTarget());
        $this->assertEquals('getTest', $factoryDefinition->getMethodName());
        $this->assertEquals([42], $factoryDefinition->getMethodArguments());
        $factoryDefinition->addMethodArgument(43);
        $this->assertEquals([42, 43], $factoryDefinition->getMethodArguments());
    }

    public function testFactory()
    {
        $factoryDefinition = new InstanceDefinition('factory', 'TheCodingMachine\\Yaco\\Definition\\Fixtures\\TestFactory', [43]);

        $instanceDefinition = new FactoryDefinition('test', new Reference('factory'), 'getTest', [42]);

        $container = $this->getContainer([
            'test' => $instanceDefinition,
            'factory' => $factoryDefinition,
        ]);
        $result = $container->get('test');

        $this->assertInstanceOf('TheCodingMachine\\Yaco\\Definition\\Fixtures\\Test', $result);
        $this->assertEquals(43, $result->cArg1);
        $this->assertEquals(42, $result->cArg2);
    }
}
