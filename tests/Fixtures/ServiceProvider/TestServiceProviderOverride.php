<?php

namespace TheCodingMachine\Yaco\Fixtures\ServiceProvider;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;

class TestServiceProviderOverride implements ServiceProvider
{
    public function getServices()
    {
        return [
            'serviceA' => function (ContainerInterface $container, callable $previousCallback = null) {
                $serviceA = $previousCallback();
                $serviceA->newProperty = 'foo';

                return $serviceA;
            },
            'serviceC' => 'TheCodingMachine\\Yaco\\Fixtures\\ServiceProvider\\TestServiceProviderOverride::overrideServiceC',
        ];
    }

    public static function overrideServiceC(ContainerInterface $container, callable $previousCallback = null)
    {
        $serviceC = $previousCallback();
        $serviceC->newProperty = 'baz';

        return $serviceC;
    }
}
