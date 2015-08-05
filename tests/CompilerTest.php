<?php
namespace TheCodingMachine\Yaco;


use Mouf\Container\Definition\InstanceDefinition;

class CompilerTest extends \PHPUnit_Framework_TestCase
{

    public function testContainer() {
        $instanceDefinition = new InstanceDefinition("test", "\\stdClass");

        $compiler = new Compiler();
        $compiler->addDefinition("test", $instanceDefinition);

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
}

