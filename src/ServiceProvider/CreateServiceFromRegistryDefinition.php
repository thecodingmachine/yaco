<?php

namespace TheCodingMachine\Yaco\ServiceProvider;

use TheCodingMachine\Yaco\Definition\DumpableInterface;
use TheCodingMachine\Yaco\Definition\InlineEntry;
use TheCodingMachine\Yaco\Definition\InlineEntryInterface;

/**
 * Fetches a service from the service-providers registry.
 */
class CreateServiceFromRegistryDefinition implements DumpableInterface
{
    /**
     * The identifier of the instance in the container.
     *
     * @var string|null
     */
    private $identifier;

    /**
     * The key of the service provider in the registry.
     *
     * @var int
     */
    private $serviceProviderKey;

    /**
     * @var string
     */
    private $serviceName;

    /**
     * @param string|null                    $identifier
     * @param string                         $serviceName
     * @param int                            $serviceProviderKey
     */
    public function __construct($identifier, string $serviceName, int $serviceProviderKey)
    {
        $this->identifier = $identifier;
        $this->serviceName = $serviceName;
        $this->serviceProviderKey = $serviceProviderKey;
    }

    /**
     * Returns the identifier for this object in the container.
     * If null, classes consuming this definition should assume the definition must be inlined.
     *
     * @return string|null
     */
    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    /**
     * Returns an InlineEntryInterface object representing the PHP code necessary to generate
     * the container entry.
     *
     * @param string $containerVariable The name of the variable that allows access to the container instance. For instance: "$container", or "$this->container"
     * @param array  $usedVariables     An array of variables that are already used and that should not be used when generating this code.
     *
     * @return InlineEntryInterface
     */
    public function toPhpCode(string $containerVariable, array $usedVariables = array()): InlineEntryInterface
    {
        $code = sprintf('$this->registry->createService(%s, %s, $this->delegateLookupContainer)', var_export($this->serviceProviderKey, true),
            var_export($this->serviceName, true));

        return new InlineEntry($code, null, $usedVariables);
    }

    public function cloneWithoutIdentifier(): self
    {
        return new self(null, $this->serviceName, $this->serviceProviderKey);
    }
}
