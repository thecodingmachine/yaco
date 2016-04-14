<?php
namespace TheCodingMachine\Yaco\ServiceProvider;

use TheCodingMachine\Yaco\Definition\DumpableInterface;
use TheCodingMachine\Yaco\Definition\InlineEntry;
use TheCodingMachine\Yaco\Definition\InlineEntryInterface;

/**
 * Fetches a service from the service-providers registry
 */
class ServiceFromRegistryDefinition implements DumpableInterface
{
    /**
     * The identifier of the instance in the container.
     *
     * @var string|null
     */
    private $identifier;

    /**
     * The key of the service provider in the registry
     * @var int
     */
    private $serviceProviderKey;

    /**
     * @var CallbackWrapperDefinition
     */
    private $callbackWrapperDefinition;

    /**
     * @var string
     */
    private $serviceName;

    /**
     * @param string|null $identifier
     * @param string $serviceName
     * @param int $serviceProviderKey
     * @param CallbackWrapperDefinition|null $callbackWrapperDefinition
     */
    public function __construct($identifier, $serviceName, $serviceProviderKey, CallbackWrapperDefinition $callbackWrapperDefinition = null)
    {
        $this->identifier = $identifier;
        $this->serviceName = $serviceName;
        $this->serviceProviderKey = $serviceProviderKey;
        $this->callbackWrapperDefinition = $callbackWrapperDefinition;
    }


    /**
     * Returns the identifier for this object in the container.
     * If null, classes consuming this definition should assume the definition must be inlined.
     *
     * @return string|null
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @return int
     */
    public function getServiceProviderKey()
    {
        return $this->serviceProviderKey;
    }

    /**
     * @return CallbackWrapperDefinition
     */
    public function getCallbackWrapperDefinition()
    {
        return $this->callbackWrapperDefinition;
    }

    /**
     * @return string
     */
    public function getServiceName()
    {
        return $this->serviceName;
    }

    /**
     * Returns an InlineEntryInterface object representing the PHP code necessary to generate
     * the container entry.
     *
     * @param string $containerVariable The name of the variable that allows access to the container instance. For instance: "$container", or "$this->container"
     * @param array $usedVariables An array of variables that are already used and that should not be used when generating this code.
     *
     * @return InlineEntryInterface
     */
    public function toPhpCode($containerVariable, array $usedVariables = array())
    {
        $previousCode = '';
        if ($this->callbackWrapperDefinition) {
            $previousCode = ', '.$this->callbackWrapperDefinition->toPhpCode($containerVariable, $usedVariables)->getExpression();
        }
        $code = sprintf('$this->registry->createService(%s, %s, $this->delegateLookupContainer%s)', var_export($this->serviceProviderKey, true),
            var_export($this->serviceName, true), $previousCode);
        return new InlineEntry($code, null, $usedVariables);
    }
}
