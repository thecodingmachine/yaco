<?php

declare (strict_types = 1);

namespace TheCodingMachine\Yaco\ServiceProvider;

use Interop\Container\Definition\DefinitionInterface;
use Interop\Container\ServiceProviderInterface;
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
     * @param ServiceProviderInterface $serviceProvider
     * @param int             $serviceProviderKey
     */
    private function loadServiceProvider(ServiceProviderInterface $serviceProvider, $serviceProviderKey)
    {
        $serviceFactories = $serviceProvider->getFactories();

        foreach ($serviceFactories as $serviceName => $callable) {
            $this->registerService($serviceName, $serviceProviderKey, $callable);
        }

        $serviceExtensions = $serviceProvider->getExtensions();

        foreach ($serviceExtensions as $serviceName => $callable) {
            $this->extendService($serviceName, $serviceProviderKey, $callable);
        }
    }

    /**
     * @param string $serviceName
     * @param int $serviceProviderKey
     * @param callable $callable
     *
     */
    private function registerService(string $serviceName, int $serviceProviderKey, callable $callable)
    {
        $definition = $this->getCreateServiceDefinitionFromCallable($serviceName, $serviceName, $serviceProviderKey, $callable, new ContainerDefinition());

        $this->compiler->addDumpableDefinition($definition);
    }

    /**
     * @param string $serviceName
     * @param int $serviceProviderKey
     * @param callable $callable
     *
     * @throws CompilerException
     */
    private function extendService(string $serviceName, int $serviceProviderKey, callable $callable)
    {
        // TODO: check if $callable as a nullable previous argument!

        if (!$this->compiler->has($serviceName)) {
            // TODO: if $callable as NOT a nullable previous argument, throw an exception.
        }

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
        //$innerName = $decoratedServiceName.'.inner';
        //$callbackWrapperName = $decoratedServiceName.'.callbackwrapper';

        // TODO: it would be way easier if we could simply rename a definition!!!
        if ($previousDefinition instanceof FactoryCallDefinition) {
            $innerDefinition = new FactoryCallDefinition(null /*$innerName*/, $previousDefinition->getFactory(), $previousDefinition->getMethodName(), $previousDefinition->getMethodArguments());
        } elseif ($previousDefinition instanceof CreateServiceFromRegistryDefinition || $previousDefinition instanceof ExtendServiceFromRegistryDefinition) {
            $innerDefinition = $previousDefinition;
        } else {
            // @codeCoverageIgnoreStart
            throw new CompilerException('Unable to rename definition from class '.get_class($previousDefinition));
            // @codeCoverageIgnoreEnd
        }

        $definition = $this->getExtendServiceDefinitionFromCallable($decoratedServiceName, $serviceName, $serviceProviderKey, $callable, new ContainerDefinition(), $innerDefinition);

        $this->compiler->addDumpableDefinition($definition);
        //$this->compiler->addDumpableDefinition($innerDefinition);
        //$this->compiler->addDumpableDefinition($callbackWrapperDefinition);
        $this->compiler->addDumpableDefinition(new AliasDefinition($oldServiceName, $decoratedServiceName));
    }

    /**
     * @param $serviceName
     * @param int                            $serviceProviderKey
     * @param callable                       $callable
     * @param ContainerDefinition            $containerDefinition
     *
     * @return DumpableInterface
     */
    private function getCreateServiceDefinitionFromCallable($decoratedServiceName, $serviceName, $serviceProviderKey, callable $callable, ContainerDefinition $containerDefinition): DumpableInterface
    {
        // FIXME: we must split this method in 2. One for the factories and one for the extensions!

        if ($callable instanceof DefinitionInterface) {
            return $this->converter->convert($decoratedServiceName, $callable);
        }
        if (is_array($callable) && is_string($callable[0])) {
            return new FactoryCallDefinition($decoratedServiceName, $callable[0], $callable[1], [$containerDefinition]);
        } elseif (is_string($callable) && strpos($callable, '::') !== false) {
            $pos = strpos($callable, '::');
            $className = substr($callable, 0, $pos);
            $methodName = substr($callable, $pos + 2);

            return new FactoryCallDefinition($decoratedServiceName, $className, $methodName, [$containerDefinition]);
        }

        // This is an object or a callback... we need to call the getServices method of the service provider at runtime.
        return new CreateServiceFromRegistryDefinition($decoratedServiceName, $serviceName, $serviceProviderKey);
    }

    /**
     * @param $serviceName
     * @param int                            $serviceProviderKey
     * @param callable                       $callable
     * @param ContainerDefinition            $containerDefinition
     * @param CallbackWrapperDefinition|null $previousDefinition
     *
     * @return DumpableInterface
     */
    private function getExtendServiceDefinitionFromCallable($decoratedServiceName, $serviceName, $serviceProviderKey, callable $callable, ContainerDefinition $containerDefinition, DumpableInterface $previousDefinition = null)
    {
        // FIXME: we must split this method in 2. One for the factories and one for the extensions!

        if ($callable instanceof DefinitionInterface) {
            return $this->converter->convert($decoratedServiceName, $callable);
        }
        if (is_array($callable) && is_string($callable[0])) {
            return new FactoryCallDefinition($decoratedServiceName, $callable[0], $callable[1], [$containerDefinition, $previousDefinition]);
        } elseif (is_string($callable) && strpos($callable, '::') !== false) {
            $pos = strpos($callable, '::');
            $className = substr($callable, 0, $pos);
            $methodName = substr($callable, $pos + 2);

            return new FactoryCallDefinition($decoratedServiceName, $className, $methodName, [$containerDefinition, $previousDefinition]);
        }

        // This is an object or a callback... we need to call the getServices method of the service provider at runtime.
        return new ExtendServiceFromRegistryDefinition($decoratedServiceName, $serviceName, $serviceProviderKey, $previousDefinition);
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
