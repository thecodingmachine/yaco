<?php
namespace TheCodingMachine\Yaco\Fixtures\ServiceProvider;

use Assembly\ParameterDefinition;
use Assembly\Reference;
use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;

class TestServiceProvider implements ServiceProvider
{
    public static function getServices()
    {
        return [
            'serviceA' => function(ContainerInterface $container) {
                $instance = new \stdClass();
                $instance->serviceB = $container->get('serviceB');
                return $instance;
            },
            'serviceB' => [ TestServiceProvider::class, 'createServiceB' ],
            'serviceC' => 'TheCodingMachine\\Yaco\\Fixtures\\ServiceProvider\\TestServiceProvider::createServiceC',
            'alias' => new Reference('serviceA'),
            'param' => new ParameterDefinition(42)
        ];
    }

    public static function createServiceB(ContainerInterface $container)
    {
        $instance = new \stdClass();
        // Test getting the database_host parameter.
        $instance->parameter = $container->get('my_parameter');
        return $instance;
    }

    public static function createServiceC()
    {
        return new \stdClass();
    }
}
