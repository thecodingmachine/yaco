<?php

namespace TheCodingMachine\Yaco;

use TheCodingMachine\Yaco\Definition\Fixtures\TestDefinitionProvider;
use TheCodingMachine\Yaco\Definition\ObjectDefinition;
use TheCodingMachine\Yaco\Definition\ParameterDefinition;

class CompilerTest extends \PHPUnit_Framework_TestCase
{
    public function testContainer()
    {
        $instanceDefinition = new ObjectDefinition('test', '\\stdClass');

        $compiler = new Compiler();
        $compiler->addDumpableDefinition($instanceDefinition);

        $code = $compiler->compile('MyNamespace\\MyContainer');
        file_put_contents(__DIR__.'/Fixtures/Generated/MyContainer.php', $code);
        require __DIR__.'/Fixtures/Generated/MyContainer.php';

        $myContainer = new \MyNamespace\MyContainer();
        $result = $myContainer->get('test');
        $this->assertInstanceOf('\\stdClass', $result);

        $code = $compiler->compile('MyContainerNoNamespace');
        file_put_contents(__DIR__.'/Fixtures/Generated/MyContainerNoNamespace.php', $code);
        require __DIR__.'/Fixtures/Generated/MyContainerNoNamespace.php';

        $myContainer = new \MyContainerNoNamespace();
        $result = $myContainer->get('test');
        $this->assertInstanceOf('\\stdClass', $result);
    }

    public function testParameter()
    {
        $parameterDefinition = new ParameterDefinition('test', 'value');

        $compiler = new Compiler();
        $compiler->addDumpableDefinition($parameterDefinition);

        $code = $compiler->compile('MyNamespace\\MyContainerWithParameters');
        file_put_contents(__DIR__.'/Fixtures/Generated/MyContainerWithParameters.php', $code);
        require __DIR__.'/Fixtures/Generated/MyContainerWithParameters.php';

        $myContainer = new \MyNamespace\MyContainerWithParameters();
        $result = $myContainer->get('test');
        $this->assertEquals('value', $result);
    }

    public function testStandardDefinition()
    {
        $instanceDefinition = new \Assembly\ObjectDefinition('\\stdClass');

        $compiler = new Compiler();
        $compiler->addDefinition('test', $instanceDefinition);

        $code = $compiler->compile('MyNamespace\\MyContainerStandardDefinition');
        file_put_contents(__DIR__.'/Fixtures/Generated/MyContainerStandardDefinition.php', $code);
        require __DIR__.'/Fixtures/Generated/MyContainerStandardDefinition.php';

        $myContainer = new \MyNamespace\MyContainerStandardDefinition();
        $result = $myContainer->get('test');
        $this->assertInstanceOf('\\stdClass', $result);
    }

    /**
     * @expectedException \TheCodingMachine\Yaco\CompilerException
     */
    public function testException()
    {
        $compiler = new Compiler();
        $compiler->addDumpableDefinition(new InvalidEntryDefinition());

        $code = $compiler->compile('MyNamespace\\MyContainerWithParameters');
    }

    /**
     * @expectedException \TheCodingMachine\Yaco\CompilerException
     */
    public function testExceptionOnAnonymousDefinition()
    {
        $compiler = new Compiler();
        $compiler->addDumpableDefinition(new ObjectDefinition(null, '\\stdClass'));
    }

    public function testRegister()
    {
        $compiler = new Compiler();
        $compiler->register(new TestDefinitionProvider());

        $code = $compiler->compile('MyNamespace\\MyContainerRegister');
        file_put_contents(__DIR__.'/Fixtures/Generated/MyContainerRegister.php', $code);
        require __DIR__.'/Fixtures/Generated/MyContainerRegister.php';

        $myContainer = new \MyNamespace\MyContainerRegister();
        $result = $myContainer->get('test');
        $this->assertInstanceOf('\\stdClass', $result);
    }

    public function testHas()
    {
        $compiler = new Compiler();
        $compiler->register(new TestDefinitionProvider());

        $this->assertTrue($compiler->has('test'));
        $this->assertFalse($compiler->has('not_found'));
    }

    public function testGetDumpableDefinitionFromDefinitionProvider()
    {
        $compiler = new Compiler();
        $compiler->register(new TestDefinitionProvider());

        $this->assertInstanceOf('TheCodingMachine\\Yaco\\Definition\\ObjectDefinition', $compiler->getDumpableDefinition('test'));
    }

    public function testGetDumpableDefinition()
    {
        $instanceDefinition = new ObjectDefinition('test', '\\stdClass');

        $compiler = new Compiler();
        $compiler->addDumpableDefinition($instanceDefinition);

        $this->assertInstanceOf('TheCodingMachine\\Yaco\\Definition\\ObjectDefinition', $compiler->getDumpableDefinition('test'));
    }

    /**
     * @expectedException \TheCodingMachine\Yaco\CompilerException
     *
     * @throws CompilerException
     */
    public function testGetDumpableDefinitionException()
    {
        $compiler = new Compiler();

        $compiler->getDumpableDefinition('test');
    }
}
