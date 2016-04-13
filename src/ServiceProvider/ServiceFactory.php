<?php


namespace TheCodingMachine\Yaco\ServiceProvider;

/**
 * This utility class is used to instantiate services from a service provider (if the services are not
 * static methods).
 *
 * Note: this calls the 'getServices' method of the service provider. Hence, this is suboptimal compared to
 * a call to a static method
 */
class ServiceFactory
{
    public static function create(string $serviceProviderClassName, string $serviceName, array $params)
    {
        $services = call_user_func([$serviceProviderClassName, 'getServices']);

        return call_user_func_array($services[$serviceName], $params);
    }
}
