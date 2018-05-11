<?php

/**
 * /src/ThinFrame/Applications/DependencyInjection/Extensions/ImportExtension.php
 *
 * @copyright 2013 Sorin Badea <sorin.badea91@gmail.com>
 * @license   MIT license (see the license file in the root directory)
 */

namespace ThinFrame\Applications\DependencyInjection\Extensions;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use ThinFrame\Applications\DependencyInjection\ApplicationContainerBuilder;

/**
 * Class ImportExtension
 *
 * @package ThinFrame\Applications\DependencyInjection\Extensions
 * @since   0.2
 */
class ImportExtension implements ExtensionInterface
{
    /**
     * @var ApplicationContainerBuilder
     */
    private $applicationContainer;

    /**
     * Constructor
     *
     * @param ApplicationContainerBuilder $container
     */
    public function __construct(ApplicationContainerBuilder $container)
    {
        $this->applicationContainer = $container;
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
        if (isseT($config[0])) {
            foreach ($config[0] as $configFile) {
                $this->applicationContainer->import($configFile);
            }
        }
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
        return null;
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
        return null;
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
        return 'import';
    }
}
