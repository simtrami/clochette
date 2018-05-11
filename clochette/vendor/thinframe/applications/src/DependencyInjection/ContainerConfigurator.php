<?php

/**
 * /src/DependencyInjection/ContainerConfigurator.php
 *
 * @copyright 2013 Sorin Badea <sorin.badea91@gmail.com>
 * @license   MIT license (see the license file in the root directory)
 */

namespace ThinFrame\Applications\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use ThinFrame\Applications\DependencyInjection\Extensions\AbstractConfigurationManager;
use ThinFrame\Applications\DependencyInjection\Extensions\ConfigurationManager;

/**
 * ContainerConfigurator - configure container builder with extensions and compiler passes
 *
 * @package ThinFrame\Applications\DependencyInjection
 * @since   0.2
 */
class ContainerConfigurator
{
    /**
     * @var AwareDefinition[]
     */
    private $awareDefinitions = [];
    /**
     * @var ExtensionInterface[]
     */
    private $extensions = [];
    /**
     * @var CompilerPassInterface[]
     */
    private $compilerPasses = [];

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
     * Add configurator
     *
     * @param ConfigurationManager $configurator
     */
    public function addConfigurationManager(ConfigurationManager $configurator)
    {
        $this->addExtension($configurator);
        $this->addCompilerPass($configurator);
    }

    /**
     * Add extension
     *
     * @param ExtensionInterface $extension
     */
    public function addExtension(ExtensionInterface $extension)
    {
        $this->extensions[] = $extension;
    }

    /**
     * Add compiler pass
     *
     * @param CompilerPassInterface $compilerPass
     */
    public function addCompilerPass(CompilerPassInterface $compilerPass)
    {
        $this->compilerPasses[] = $compilerPass;
    }

    /**
     * Configure container
     *
     * @param ApplicationContainerBuilder $container
     */
    public function configureContainer(ApplicationContainerBuilder $container)
    {
        foreach ($this->getExtensions() as $extension) {
            $container->registerExtension($extension);
        }
        foreach ($this->getCompilerPasses() as $compilerPass) {
            $container->addCompilerPass($compilerPass);
        }
        foreach ($this->getAwareDefinitions() as $definition) {
            $container->addAwareDefinition($definition);
        }
    }

    /**
     * Get all extensions
     *
     * @return ExtensionInterface[]
     */
    public function getExtensions()
    {
        return $this->extensions;
    }

    /**
     * Get all compiler passes
     *
     * @return CompilerPassInterface[]
     */
    public function getCompilerPasses()
    {
        return $this->compilerPasses;
    }

    /**
     * Get all aware interface definitions
     *
     * @return AwareDefinition[]
     */
    public function getAwareDefinitions()
    {
        return $this->awareDefinitions;
    }
}
