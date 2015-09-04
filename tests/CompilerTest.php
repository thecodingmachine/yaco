<?php
namespace TheCodingMachine\Yaco;


use Mouf\Container\Definition\InstanceDefinition;
use Mouf\Container\Definition\ParameterDefinition;

class CompilerTest extends \PHPUnit_Framework_TestCase
{

    public function testContainer() {
        $instanceDefinition = new InstanceDefinition("test", "\\stdClass");

        $compiler = new Compiler();
        $compiler->addDefinition($instanceDefinition);

        $code = $compiler->compile("MyNamespace\\MyContainer");
        file_put_contents(__DIR__.'/Fixtures/Generated/MyContainer.php', $code);
        require __DIR__.'/Fixtures/Generated/MyContainer.php';

        $myContainer = new \MyNamespace\MyContainer();
        $result = $myContainer->get("test");
        $this->assertInstanceOf("\\stdClass", $result);

        $code = $compiler->compile("MyContainerNoNamespace");
        file_put_contents(__DIR__.'/Fixtures/Generated/MyContainerNoNamespace.php', $code);
        require __DIR__.'/Fixtures/Generated/MyContainerNoNamespace.php';

        $myContainer = new \MyContainerNoNamespace();
        $result = $myContainer->get("test");
        $this->assertInstanceOf("\\stdClass", $result);
    }

    public function testParameter() {
        $parameterDefinition = new ParameterDefinition("test", "value");

        $compiler = new Compiler();
        $compiler->addDefinition($parameterDefinition);

        $code = $compiler->compile("MyNamespace\\MyContainerWithParameters");
        file_put_contents(__DIR__.'/Fixtures/Generated/MyContainerWithParameters.php', $code);
        require __DIR__.'/Fixtures/Generated/MyContainerWithParameters.php';

        $myContainer = new \MyNamespace\MyContainerWithParameters();
        $result = $myContainer->get("test");
        $this->assertEquals("value", $result);
    }

    /**
     * @expectedException \TheCodingMachine\Yaco\CompilerException
     */
    public function testException() {
        $compiler = new Compiler();
        $compiler->addDefinition(new InvalidEntryDefinition());

        $code = $compiler->compile("MyNamespace\\MyContainerWithParameters");

    }
}

