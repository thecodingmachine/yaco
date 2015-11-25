<?php

namespace TheCodingMachine\Yaco;

use Assembly\MethodCall;
use Assembly\PropertyAssignment;
use Assembly\Reference;
use TheCodingMachine\Yaco\Definition\AbstractDefinitionTest;

class DefinitionConverterTest extends AbstractDefinitionTest
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

    public function testInstanceConverter()
    {
        $referenceDefinition = new \Assembly\InstanceDefinition('foo', '\\stdClass');

        $assemblyDefinition = new \Assembly\InstanceDefinition('bar', 'TheCodingMachine\\Yaco\\Definition\\Fixtures\\Test');
        $assemblyDefinition->addConstructorArgument(42);
        $assemblyDefinition->addConstructorArgument(['hello' => 'world', 'foo' => new Reference('foo'), 'fooDirect' => $referenceDefinition]);

        $container = $this->getContainer([
            'bar' => $this->converter->convert($assemblyDefinition),
            'foo' => $this->converter->convert($referenceDefinition),
        ]);
        $result = $container->get('bar');

        $this->assertInstanceOf('TheCodingMachine\\Yaco\\Definition\\Fixtures\\Test', $result);
        $this->assertEquals(42, $result->cArg1);
        $this->assertEquals('world', $result->cArg2['hello']);
        $this->assertInstanceOf('stdClass', $result->cArg2['foo']);
        $this->assertInstanceOf('stdClass', $result->cArg2['fooDirect']);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testParameterException()
    {
        $assemblyDefinition = new \Assembly\InstanceDefinition('foo', 'TheCodingMachine\\Yaco\\Definition\\Fixtures\\Test');
        $assemblyDefinition->addConstructorArgument(new \stdClass());

        $this->converter->convert($assemblyDefinition);
    }

    public function testInstanceConverterPropertiesAndMethodCalls()
    {
        $assemblyDefinition = new \Assembly\InstanceDefinition('bar', 'TheCodingMachine\\Yaco\\Definition\\Fixtures\\Test');
        $assemblyDefinition->addMethodCall(new MethodCall('setArg1', [42]));
        $assemblyDefinition->addPropertyAssignment(new PropertyAssignment('cArg2', 43));

        $container = $this->getContainer([
            'bar' => $this->converter->convert($assemblyDefinition),
        ]);
        $result = $container->get('bar');

        $this->assertInstanceOf('TheCodingMachine\\Yaco\\Definition\\Fixtures\\Test', $result);
        $this->assertEquals(42, $result->cArg1);
        $this->assertEquals(43, $result->cArg2);
    }

    public function testParameterConverter()
    {
        $assemblyDefinition = new \Assembly\ParameterDefinition('foo', '42');

        $container = $this->getContainer([
            'foo' => $this->converter->convert($assemblyDefinition),
        ]);
        $result = $container->get('foo');

        $this->assertEquals(42, $result);
    }

    public function testAliasConverter()
    {
        $aliasDefinition = new \Assembly\AliasDefinition('foo', 'bar');

        $assemblyDefinition = new \Assembly\InstanceDefinition('bar', 'TheCodingMachine\\Yaco\\Definition\\Fixtures\\Test');

        $container = $this->getContainer([
            'bar' => $this->converter->convert($assemblyDefinition),
            'foo' => $this->converter->convert($aliasDefinition),
        ]);
        $result = $container->get('foo');
        $result2 = $container->get('bar');

        $this->assertTrue($result === $result2);
    }

    public function testFactoryConverter()
    {
        $factoryAssemblyDefinition = new \Assembly\InstanceDefinition('factory', 'TheCodingMachine\\Yaco\\Definition\\Fixtures\\TestFactory');
        $factoryAssemblyDefinition->addConstructorArgument(42);

        $assemblyDefinition = new \Assembly\FactoryDefinition('test', new Reference('factory'), 'getTest');

        $container = $this->getContainer([
            'factory' => $this->converter->convert($factoryAssemblyDefinition),
            'test' => $this->converter->convert($assemblyDefinition),
        ]);
        $result = $container->get('test');

        $this->assertInstanceOf('TheCodingMachine\\Yaco\\Definition\\Fixtures\\Test', $result);
        $this->assertEquals(42, $result->cArg1);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testUnsupportedDefinitionConverter()
    {
        $definition = $this->getMock('Interop\\Container\\Definition\\DefinitionInterface');

        $this->converter->convert($definition);
    }
}
