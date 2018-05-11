<?php

/**
 * /src/ThinFrame/Applications/DependencyInjection/Extensions/AbstractConfigurationManager.php
 *
 * @copyright 2013 Sorin Badea <sorin.badea91@gmail.com>
 * @license   MIT license (see the license file in the root directory)
 */

namespace ThinFrame\Applications\DependencyInjection\Extensions;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

/**
 * Class AbstractConfigurationManager
 *
 * @package ThinFrame\Applications\DependencyInjection\Extensions
 * @since   0.2
 */
class ConfigurationManager implements ExtensionInterface, CompilerPassInterface
{
    /**
     * @var array
     */
    private $config = [];

    /**
     * @var string
     */
    private $namespace;

    /**
     * @var string
     */
    private $service;

    /**
     * Constructor
     *
     * @param string $namespace
     * @param string $service
     */
    public function __construct($namespace, $service)
    {
        $this->namespace = $namespace;
        $this->service   = $service;
    }

    /**
     * Loads a specific configuration.
     *
     * @param array            $config    An array of configuration values
     * @param ContainerBuilder $container A ContainerBuilder instance
     *
     * @throws \InvalidArgumentException When provided tag is not defined in this extension
     *
     * @api
     */
    public function load(array $config, ContainerBuilder $container)
    {
        foreach ($config as $configurationSet) {
            $this->config = array_replace_recursive($this->config, $configurationSet);
        }
    }

    /**
     * Returns the base path for the XSD files.
     *
     * @return string The XSD base path
     *
     * @api
     */
    public function getXsdValidationBasePath()
    {
        return false;
    }

    /**
     * Returns the recommended alias to use in XML.
     *
     * This alias is also the mandatory prefix to use when using YAML.
     *
     * @return string The alias
     *
     * @api
     */
    public function getAlias()
    {
        return $this->getNamespace();
    }

    /**
     * Returns the namespace to be used for this extension (XML namespace).
     *
     * @return string The XML namespace
     *
     * @api
     */
    public function getNamespace()
    {
        return $this->namespace;
    }


    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     *
     * @api
     */
    public function process(ContainerBuilder $container)
    {
        $container->getDefinition($this->service)->addMethodCall('setConfiguration', [$this->config]);
    }
}
