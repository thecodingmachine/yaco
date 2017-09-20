<?php

namespace TheCodingMachine\Yaco\Fixtures\ServiceProvider;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Interop\Container\ServiceProviderInterface;

class TestServiceProviderOverride implements ServiceProviderInterface
{
    public function getFactories()
    {
        return [];
    }

    public function getExtensions()
    {
        return [
            'serviceA' => function (ContainerInterface $container, \stdClass $serviceA) {
                $serviceA->newProperty = 'foo';

                return $serviceA;
            },
            'serviceC' => 'TheCodingMachine\\Yaco\\Fixtures\\ServiceProvider\\TestServiceProviderOverride::overrideServiceC',
        ];
    }

    public static function overrideServiceC(ContainerInterface $container, \stdClass $serviceC)
    {
        $serviceC->newProperty = 'baz';

        return $serviceC;
    }
}
