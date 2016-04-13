<?php
declare(strict_types=1);

namespace TheCodingMachine\Yaco\ServiceProvider;


use Interop\Container\Definition\DefinitionInterface;
use Puli\Discovery\Api\Discovery;
use Puli\Discovery\Binding\ClassBinding;
use TheCodingMachine\Yaco\Compiler;
use TheCodingMachine\Yaco\Definition\AliasDefinition;
use TheCodingMachine\Yaco\Definition\DumpableInterface;
use TheCodingMachine\Yaco\Definition\FactoryCallDefinition;
use TheCodingMachine\Yaco\Definition\Reference;
use TheCodingMachine\Yaco\DefinitionConverter;
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
    public function __construct(Compiler $compiler, DefinitionConverterInterface $converter = null)
    {
        $this->compiler = $compiler;
        if ($converter === null) {
            $converter = new DefinitionConverter();
        }
        $this->converter = $converter;
    }

    /**
     * Discovers service provider class names using Puli.
     *
     * @param Discovery $discovery
     * @return string[] Returns an array of service providers.
     */
    public function discover(Discovery $discovery) : array {
        $bindings = $discovery->findBindings('container-interop/service-provider');
        $serviceProviders = [];

        foreach ($bindings as $binding) {
            if ($binding instanceof ClassBinding) {
                $serviceProviders[] = $binding->getClassName();
            }
        }
        return $serviceProviders;
    }

    /**
     * Discovers and loads the service providers using Puli.
     *
     * @param Discovery $discovery
     */
    public function discoverAndLoad(Discovery $discovery) {
        $serviceProviders = $this->discover($discovery);

        foreach ($serviceProviders as $serviceProvider) {
            $this->load($serviceProvider);
        }
    }

    public function load(string $serviceProviderClassName)
    {
        if (!class_exists($serviceProviderClassName)) {
            throw new InvalidArgumentException(sprintf('ServiceProviderLoader::load expects a valid class name. Could not find class "%s"', $serviceProviderClassName));
        }

        $serviceFactories = call_user_func([$serviceProviderClassName, 'getServices']);

        foreach ($serviceFactories as $serviceName => $callable) {
            $this->registerService($serviceName, $serviceProviderClassName, $callable);
        }
    }

    private function registerService(string $serviceName, string $className, callable $callable) {
        if (!$this->compiler->has($serviceName)) {
            $definition = $this->getServiceDefinitionFromCallable($serviceName, $className, $callable, [new ContainerDefinition()]);

            $this->compiler->addDumpableDefinition($definition);
        } else {
            // The new service will be created under the name 'xxx_decorated_y'
            // The old service will be moved to the name 'xxx_decorated_y.inner'
            // This old service will be accessible through a callback represented by 'xxx_decorated_y.callbackwrapper'
            // The $servicename becomes an alias pointing to 'xxx_decorated_y'

            $previousDefinition = $this->compiler->getDumpableDefinition($serviceName);
            while ($previousDefinition instanceof Reference) {
                $previousDefinition = $this->compiler->getDumpableDefinition($previousDefinition->getAlias());
            }

            $oldServiceName = $serviceName;
            $serviceName = $this->getDecoratedServiceName($serviceName);
            $innerName = $serviceName.'.inner';
            $callbackWrapperName = $serviceName.'.callbackwrapper';

            $innerDefinition = new FactoryCallDefinition($innerName, $previousDefinition->getFactory(), $previousDefinition->getMethodName(), $previousDefinition->getMethodArguments());


            $callbackWrapperDefinition = new CallbackWrapperDefinition($callbackWrapperName, $innerDefinition);

            $definition = $this->getServiceDefinitionFromCallable($serviceName, $className, $callable, [new ContainerDefinition(), $callbackWrapperDefinition]);

            $this->compiler->addDumpableDefinition($definition);
            $this->compiler->addDumpableDefinition($innerDefinition);
            $this->compiler->addDumpableDefinition($callbackWrapperDefinition);
            $this->compiler->addDumpableDefinition(new AliasDefinition($oldServiceName, $serviceName));
        }

    }

    private function getServiceDefinitionFromCallable(string $serviceName, string $className, callable $callable, array $params) : DumpableInterface {
        if ($callable instanceof DefinitionInterface) {
            return $this->converter->convert($serviceName, $callable);
        }
        if (is_array($callable) && is_string($callable[0])) {
            return new FactoryCallDefinition($serviceName, $callable[0], $callable[1], $params);
        }
        // This is an object or a callback... we need to call the getServices method of the service provider at runtime.
        $factoryParams = [
            $className,
            $serviceName,
            $params
        ];
        return new FactoryCallDefinition($serviceName, ServiceFactory::class, 'create', $factoryParams);
    }

    private function getDecoratedServiceName(string $serviceName) : string {
        $counter = 1;
        while ($this->compiler->has($serviceName.'_decorated_'.$counter)) {
            $counter++;
        }
        return $serviceName.'_decorated_'.$counter;
    }
}