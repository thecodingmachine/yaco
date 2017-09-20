<?php

namespace TheCodingMachine\Yaco\Fixtures\ServiceProvider;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Interop\Container\ServiceProviderInterface;

class TestServiceProviderOverride2 implements ServiceProviderInterface
{
    public function getFactories()
    {
        return [];
    }

    public function getExtensions()
    {
        return [
            'serviceA' => [self::class, 'overrideServiceA'],
        ];
    }

    public static function overrideServiceA(ContainerInterface $container, \stdClass $serviceA)
    {
        $serviceA->newProperty2 = 'bar';

        return $serviceA;
    }
}
