<?php

namespace TheCodingMachine\Yaco\ServiceProvider;

use TheCodingMachine\ServiceProvider\Registry;
use TheCodingMachine\Yaco\Compiler;
use TheCodingMachine\Yaco\Definition\ParameterDefinition;
use TheCodingMachine\Yaco\Fixtures\ServiceProvider\TestServiceProvider;
use TheCodingMachine\Yaco\Fixtures\ServiceProvider\TestServiceProviderOverride;
use TheCodingMachine\Yaco\Fixtures\ServiceProvider\TestServiceProviderOverride2;

class ServiceProviderLoaderTest extends \PHPUnit_Framework_TestCase
{
    public function testLoadServiceProvider()
    {
        $compiler = new Compiler(new Registry([TestServiceProvider::class]));
        $compiler->addDumpableDefinition(new ParameterDefinition('my_parameter', 'my_value'));

        $code = $compiler->compile('MyContainerServiceProvider');
        file_put_contents(__DIR__.'/../Fixtures/Generated/MyContainerServiceProvider.php', $code);
        require __DIR__.'/../Fixtures/Generated/MyContainerServiceProvider.php';

        $myContainer = new \MyContainerServiceProvider(new Registry([TestServiceProvider::class]));
        $result = $myContainer->get('serviceA');
        $this->assertInstanceOf('\\stdClass', $result);
        $this->assertEquals('my_value', $result->serviceB->parameter);

        $alias = $myContainer->get('alias');
        $this->assertSame($alias, $result);

        $param = $myContainer->get('param');
        $this->assertSame(42, $param);

        $result = $myContainer->get('serviceC');
        $this->assertInstanceOf('\\stdClass', $result);
    }

    public function testLoadServiceProviderWithOverride()
    {
        $registry = new Registry([
            TestServiceProvider::class,
            TestServiceProviderOverride::class,
        ]);
        $compiler = new Compiler($registry);
        $compiler->addDumpableDefinition(new ParameterDefinition('my_parameter', 'my_value'));

        $code = $compiler->compile('MyContainerServiceProviderWithOverride');
        file_put_contents(__DIR__.'/../Fixtures/Generated/MyContainerServiceProviderWithOverride.php', $code);
        require __DIR__.'/../Fixtures/Generated/MyContainerServiceProviderWithOverride.php';

        $myContainer = new \MyContainerServiceProviderWithOverride($registry);
        $result = $myContainer->get('serviceA');

        $this->assertInstanceOf('\\stdClass', $result);
        $this->assertEquals('my_value', $result->serviceB->parameter);
        $this->assertEquals('foo', $result->newProperty);

        $result = $myContainer->get('serviceC');

        $this->assertInstanceOf('\\stdClass', $result);
        $this->assertEquals('baz', $result->newProperty);
    }

    public function testLoadServiceProviderWithDoubleOverride()
    {
        $registry = new Registry([
            TestServiceProvider::class,
            TestServiceProviderOverride::class,
            TestServiceProviderOverride2::class,
        ]);
        $compiler = new Compiler($registry);
        $compiler->addDumpableDefinition(new ParameterDefinition('my_parameter', 'my_value'));

        $code = $compiler->compile('MyContainerServiceProviderWithOverride2');
        file_put_contents(__DIR__.'/../Fixtures/Generated/MyContainerServiceProviderWithOverride2.php', $code);
        require __DIR__.'/../Fixtures/Generated/MyContainerServiceProviderWithOverride2.php';

        $myContainer = new \MyContainerServiceProviderWithOverride2($registry);
        $result = $myContainer->get('serviceA');

        $this->assertInstanceOf('\\stdClass', $result);
        $this->assertEquals('my_value', $result->serviceB->parameter);
        $this->assertEquals('foo', $result->newProperty);
        $this->assertEquals('bar', $result->newProperty2);
    }
}
