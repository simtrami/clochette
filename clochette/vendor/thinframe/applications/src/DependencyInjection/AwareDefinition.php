<?php

/**
 * /src/DependencyInjection/ApplicationAwareTrait.php
 *
 * @copyright 2013 Sorin Badea <sorin.badea91@gmail.com>
 * @license   MIT license (see the license file in the root directory)
 */

namespace ThinFrame\Applications\DependencyInjection;

/**
 * AwareDefinition - definition of an aware objects
 *
 * @package ThinFrame\Applications\DependencyInjection
 * @since   0.2
 */
class AwareDefinition
{
    /**
     * @var string
     */
    private $awareIdentifier;
    /**
     * @var string
     */
    private $method;
    /**
     * @var string
     */
    private $service;

    /**
     * Class constructor
     *
     * @param string $awareIdentifier
     * @param string $method
     * @param string $service
     */
    public function __construct($awareIdentifier, $method, $service)
    {
        $this->awareIdentifier = $awareIdentifier;
        $this->method = $method;
        $this->service = $service;
    }

    /**
     * Get interface
     *
     * @return string
     */
    public function getAwareIdentifier()
    {
        return $this->awareIdentifier;
    }

    /**
     * Set interface
     *
     * @param string $interface
     */
    public function setAwareIdentifier($interface)
    {
        $this->awareIdentifier = $interface;
    }

    /**
     * Get method
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Set method
     *
     * @param string $method
     */
    public function setMethod($method)
    {
        $this->method = $method;
    }

    /**
     * Get service id
     *
     * @return string
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * Set service id
     *
     * @param string $service
     */
    public function setService($service)
    {
        $this->service = $service;
    }

    /**
     * Configure application container
     *
     * @param                             $serviceObject
     * @param ApplicationContainerBuilder $builder
     */
    public function configureObject($serviceObject, ApplicationContainerBuilder $builder)
    {
        if ($serviceObject instanceof $this->awareIdentifier
            || in_array(ltrim($this->awareIdentifier, '\\'), class_uses($serviceObject))
        ) {
            call_user_func_array([$serviceObject, $this->method], [$builder->get($this->service)]);
        }
    }
}
