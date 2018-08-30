<?php

namespace TheCodingMachine\Yaco\ServiceProvider;

use TheCodingMachine\Yaco\Definition\DumpableInterface;
use TheCodingMachine\Yaco\Definition\InlineEntry;
use TheCodingMachine\Yaco\Definition\InlineEntryInterface;

/**
 * Fetches a service from the service-providers registry.
 */
class ExtendServiceFromRegistryDefinition implements DumpableInterface
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
     * @var DumpableInterface|null
     */
    private $previousDefinition;

    /**
     * @var string
     */
    private $serviceName;

    /**
     * @param string|null                    $identifier
     * @param string                         $serviceName
     * @param int                            $serviceProviderKey
     * @param DumpableInterface|null $previousDefinition
     */
    public function __construct(?string $identifier, string $serviceName, int $serviceProviderKey, DumpableInterface $previousDefinition = null)
    {
        $this->identifier = $identifier;
        $this->serviceName = $serviceName;
        $this->serviceProviderKey = $serviceProviderKey;
        $this->previousDefinition = $previousDefinition;
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
        $previousCode = '';
        if ($this->previousDefinition) {
            $previousCode = ', '.$this->previousDefinition->toPhpCode($containerVariable, $usedVariables)->getExpression();
        } else {
            $previousCode = ', null';
        }
        $code = sprintf('$this->registry->extendService(%s, %s, $this->delegateLookupContainer%s)', var_export($this->serviceProviderKey, true),
            var_export($this->serviceName, true), $previousCode);

        return new InlineEntry($code, null, $usedVariables);
    }

    public function cloneWithoutIdentifier(): self
    {
        return new self(null, $this->serviceName, $this->serviceProviderKey, $this->previousDefinition);
    }
}
