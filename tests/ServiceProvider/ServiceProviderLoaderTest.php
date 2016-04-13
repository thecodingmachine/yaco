<?php


namespace TheCodingMachine\Yaco\ServiceProvider;

use Puli\Discovery\Api\Type\BindingType;
use Puli\Discovery\Binding\ClassBinding;
use Puli\Discovery\InMemoryDiscovery;
use TheCodingMachine\Yaco\Compiler;
use TheCodingMachine\Yaco\Definition\ParameterDefinition;
use TheCodingMachine\Yaco\Fixtures\ServiceProvider\TestServiceProvider;
use TheCodingMachine\Yaco\Fixtures\ServiceProvider\TestServiceProviderOverride;

class ServiceProviderLoaderTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @expectedException \TheCodingMachine\Yaco\ServiceProvider\InvalidArgumentException
     */
    public function testLoadWrongClass()
    {
        $compiler = new Compiler();

        $serviceProviderLoader = new ServiceProviderLoader($compiler);
        $serviceProviderLoader->load('ThatClassDoesNotExists');
    }

    public function testLoadServiceProvider()
    {
        $compiler = new Compiler();
        $compiler->addDumpableDefinition(new ParameterDefinition('my_parameter', 'my_value'));
        $serviceProviderLoader = new ServiceProviderLoader($compiler);
        $serviceProviderLoader->load(TestServiceProvider::class);

        $code = $compiler->compile('MyContainerServiceProvider');
        file_put_contents(__DIR__.'/../Fixtures/Generated/MyContainerServiceProvider.php', $code);
        require __DIR__.'/../Fixtures/Generated/MyContainerServiceProvider.php';

        $myContainer = new \MyContainerServiceProvider();
        $result = $myContainer->get('serviceA');
        $this->assertInstanceOf('\\stdClass', $result);
        $this->assertEquals('my_value', $result->serviceB->parameter);

        $alias = $myContainer->get('alias');
        $this->assertSame($alias, $result);

        $param = $myContainer->get('param');
        $this->assertSame(42, $param);
    }

    public function testLoadServiceProviderWithOverride()
    {
        $compiler = new Compiler();
        $compiler->addDumpableDefinition(new ParameterDefinition('my_parameter', 'my_value'));
        $serviceProviderLoader = new ServiceProviderLoader($compiler);
        $serviceProviderLoader->load(TestServiceProvider::class);
        $serviceProviderLoader->load(TestServiceProviderOverride::class);

        $code = $compiler->compile('MyContainerServiceProviderWithOverride');
        file_put_contents(__DIR__.'/../Fixtures/Generated/MyContainerServiceProviderWithOverride.php', $code);
        require __DIR__.'/../Fixtures/Generated/MyContainerServiceProviderWithOverride.php';

        $myContainer = new \MyContainerServiceProviderWithOverride();
        $result = $myContainer->get('serviceA');

        $this->assertInstanceOf('\\stdClass', $result);
        $this->assertEquals('my_value', $result->serviceB->parameter);
        $this->assertEquals('foo', $result->newProperty);
    }

    public function testDiscoveryAndLoad()
    {
        $discovery = new InMemoryDiscovery();
        $discovery->addBindingType(new BindingType('container-interop/service-provider'));
        $classBinding = new ClassBinding(TestServiceProvider::class, 'container-interop/service-provider');
        $discovery->addBinding($classBinding);

        $compiler = new Compiler();
        $serviceProviderLoader = new ServiceProviderLoader($compiler);
        $serviceProviderLoader->discoverAndLoad($discovery);

        $this->assertTrue($compiler->has('serviceA'));
    }
}
