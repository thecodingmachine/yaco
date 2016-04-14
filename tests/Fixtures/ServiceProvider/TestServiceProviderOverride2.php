<?php

namespace TheCodingMachine\Yaco\Fixtures\ServiceProvider;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;

class TestServiceProviderOverride2 implements ServiceProvider
{
    public static function getServices()
    {
        return [
            'serviceA' => [self::class, 'overrideServiceA'],
        ];
    }

    public static function overrideServiceA(ContainerInterface $container, callable $previousCallback = null)
    {
        $serviceA = $previousCallback();
        $serviceA->newProperty2 = 'bar';

        return $serviceA;
    }
}
