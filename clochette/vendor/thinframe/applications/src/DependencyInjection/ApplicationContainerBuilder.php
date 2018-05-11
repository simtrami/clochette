<?php

/**
 * /src/DependencyInjection/ApplicationContainerBuilder.php
 *
 * @copyright 2013 Sorin Badea <sorin.badea91@gmail.com>
 * @license   MIT license (see the license file in the root directory)
 */

namespace ThinFrame\Applications\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use ThinFrame\Applications\DependencyInjection\Extensions\ImportExtension;

/**
 * ApplicationContainerBuilder - wrapper over ContainerBuilder to add support for aware interfaces
 *
 * @package ThinFrame\Applications\DependencyInjection
 * @since   0.2
 */
class ApplicationContainerBuilder extends ContainerBuilder
{
    /**
     * @var FileLocator
     */
    private $importLocator;
    /**
     * @var YamlFileLoader
     */
    private $loader;
    /**
     * @var AwareDefinition[]
     */
    private $awareDefinitions = [];

    /**
     * Constructor
     */
    public function __construct(FileLocator $importLocator)
    {
        $this->registerExtension(new ImportExtension($this));
        $this->setImportLocator($importLocator);
        parent::__construct();
    }

    /**
     * Get service
     *
     * @param string $id
     * @param int    $invalidBehavior
     *
     * @return object|void
     */
    public function get($id, $invalidBehavior = ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE)
    {
        $object = parent::get($id, $invalidBehavior);
        foreach ($this->awareDefinitions as $definition) {
            $definition->configureObject($object, $this);
        }
        return $object;
    }

    /**
     * Add aware interface definition
     *
     * @param AwareDefinition $definition
     */
    public function addAwareDefinition(AwareDefinition $definition)
    {
        $this->awareDefinitions[] = $definition;
    }

    /**
     * Set file locator for imports
     *
     * @return FileLocator
     */
    public function getImportLocator()
    {
        return $this->importLocator;
    }

    /**
     * Set file locator for imports
     *
     * @param FileLocator $importLocator
     */
    public function setImportLocator(FileLocator $importLocator)
    {
        $this->importLocator = $importLocator;
        $this->loader        = new YamlFileLoader($this, $importLocator);
    }

    /**
     * Import configuration file
     *
     * @param $configurationFile
     */
    public function import($configurationFile)
    {
        $this->loader->load($configurationFile);
    }
}
