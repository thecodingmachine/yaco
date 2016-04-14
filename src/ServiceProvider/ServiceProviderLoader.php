<?php

declare (strict_types = 1);

namespace TheCodingMachine\Yaco\ServiceProvider;

use Interop\Container\Definition\DefinitionInterface;
use Interop\Container\ServiceProvider;
use TheCodingMachine\ServiceProvider\Registry;
use TheCodingMachine\Yaco\Compiler;
use TheCodingMachine\Yaco\CompilerException;
use TheCodingMachine\Yaco\Definition\AliasDefinition;
use TheCodingMachine\Yaco\Definition\DumpableInterface;
use TheCodingMachine\Yaco\Definition\FactoryCallDefinition;
use TheCodingMachine\Yaco\Definition\Reference;
use TheCodingMachine\Yaco\DefinitionConverterInterface;

class ServiceProviderLoader
{
    /**
     * @var Compiler
     */
    private $compiler;

    /**
     * @var DefinitionConverterInterface
     */
    private $converter;

    /**
     * @param Compiler $compiler
     */
    public function __construct(Compiler $compiler, DefinitionConverterInterface $converter)
    {
        $this->compiler = $compiler;
        $this->converter = $converter;
    }

    /**
     * Loads the registry into the container.
     *
     * @param Registry $registry
     */
    public function loadFromRegistry(Registry $registry)
    {
        foreach ($registry as $key => $serviceProvider) {
            $this->loadServiceProvider($serviceProvider, $key);
        }
    }

    /**
     * @param ServiceProvider $serviceProvider
     * @param int             $serviceProviderKey
     */
    private function loadServiceProvider(ServiceProvider $serviceProvider, $serviceProviderKey)
    {
        $serviceFactories = $serviceProvider->getServices();

        foreach ($serviceFactories as $serviceName => $callable) {
            $this->registerService($serviceName, $serviceProviderKey, $callable);
        }
    }

    /**
     * @param string          $serviceName
     * @param ServiceProvider $serviceProvider
     * @param int             $serviceProviderKey
     * @param callable        $callable
     *
     * @throws \TheCodingMachine\Yaco\CompilerException
     */
    private function registerService($serviceName, $serviceProviderKey, callable $callable)
    {
        if (!$this->compiler->has($serviceName)) {
            $definition = $this->getServiceDefinitionFromCallable($serviceName, $serviceName, $serviceProviderKey, $callable, new ContainerDefinition());

            $this->compiler->addDumpableDefinition($definition);
        } else {
            // The new service will be created under the name 'xxx_decorated_y'
            // The old service will be moved to the name 'xxx_decorated_y.inner'
            // This old service will be accessible through a callback represented by 'xxx_decorated_y.callbackwrapper'
            // The $servicename becomes an alias pointing to 'xxx_decorated_y'

            $previousDefinition = $this->compiler->getDumpableDefinition($serviceName);
            /*while ($previousDefinition instanceof Reference) {
                $previousDefinition = $this->compiler->getDumpableDefinition($previousDefinition->getAlias());
            }*/

            while ($previousDefinition instanceof AliasDefinition) {
                $previousDefinition = $this->compiler->getDumpableDefinition($previousDefinition->getAlias());
            }

            $oldServiceName = $serviceName;
            $decoratedServiceName = $this->getDecoratedServiceName($serviceName);
            $innerName = $decoratedServiceName.'.inner';
            $callbackWrapperName = $decoratedServiceName.'.callbackwrapper';

            // TODO: it would be way easier if we could simply rename a definition!!!
            if ($previousDefinition instanceof FactoryCallDefinition) {
                $innerDefinition = new FactoryCallDefinition($innerName, $previousDefinition->getFactory(), $previousDefinition->getMethodName(), $previousDefinition->getMethodArguments());
                // @codeCoverageIgnoreStart
            } elseif ($previousDefinition instanceof ServiceFromRegistryDefinition) {
                // @codeCoverageIgnoreEnd
                $innerDefinition = new ServiceFromRegistryDefinition($innerName, $previousDefinition->getServiceName(), $previousDefinition->getServiceProviderKey(), $previousDefinition->getCallbackWrapperDefinition());
            } else {
                // @codeCoverageIgnoreStart
                throw new CompilerException('Unable to rename definition from class '.get_class($previousDefinition));
                // @codeCoverageIgnoreEnd
            }

            $callbackWrapperDefinition = new CallbackWrapperDefinition($callbackWrapperName, $innerDefinition);

            $definition = $this->getServiceDefinitionFromCallable($decoratedServiceName, $serviceName, $serviceProviderKey, $callable, new ContainerDefinition(), $callbackWrapperDefinition);

            $this->compiler->addDumpableDefinition($definition);
            $this->compiler->addDumpableDefinition($innerDefinition);
            $this->compiler->addDumpableDefinition($callbackWrapperDefinition);
            $this->compiler->addDumpableDefinition(new AliasDefinition($oldServiceName, $decoratedServiceName));
        }
    }

    /**
     * @param $serviceName
     * @param int                            $serviceProviderKey
     * @param callable                       $callable
     * @param ContainerDefinition            $containerDefinition
     * @param CallbackWrapperDefinition|null $callbackWrapperDefinition
     *
     * @return DumpableInterface
     */
    private function getServiceDefinitionFromCallable($decoratedServiceName, $serviceName, $serviceProviderKey, callable $callable, ContainerDefinition $containerDefinition, CallbackWrapperDefinition $callbackWrapperDefinition = null)
    {
        if ($callable instanceof DefinitionInterface) {
            return $this->converter->convert($decoratedServiceName, $callable);
        }
        if (is_array($callable) && is_string($callable[0])) {
            $params = [$containerDefinition];
            if ($callbackWrapperDefinition) {
                $params[] = $callbackWrapperDefinition;
            }

            return new FactoryCallDefinition($decoratedServiceName, $callable[0], $callable[1], $params);
        } elseif (is_string($callable) && strpos($callable, '::') !== false) {
            $pos = strpos($callable, '::');
            $className = substr($callable, 0, $pos);
            $methodName = substr($callable, $pos + 2);
            $params = [$containerDefinition];
            if ($callbackWrapperDefinition) {
                $params[] = $callbackWrapperDefinition;
            }

            return new FactoryCallDefinition($decoratedServiceName, $className, $methodName, $params);
        }

        // This is an object or a callback... we need to call the getServices method of the service provider at runtime.
        return new ServiceFromRegistryDefinition($decoratedServiceName, $serviceName, $serviceProviderKey, $callbackWrapperDefinition);
    }

    /**
     * @param string $serviceName
     *
     * @return string
     */
    private function getDecoratedServiceName($serviceName)
    {
        $counter = 1;
        while ($this->compiler->has($serviceName.'_decorated_'.$counter)) {
            ++$counter;
        }

        return $serviceName.'_decorated_'.$counter;
    }
}
