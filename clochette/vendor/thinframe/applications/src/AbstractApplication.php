<?php

/**
 * /src/AbstractApplication.php
 *
 * @copyright 2013 Sorin Badea <sorin.badea91@gmail.com>
 * @license   MIT license (see the license file in the root directory)
 */

namespace ThinFrame\Applications;

use PhpCollection\Map;
use PhpCollection\Sequence;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;
use ThinFrame\Applications\DependencyInjection\ApplicationContainerBuilder;
use ThinFrame\Applications\DependencyInjection\AwareDefinition;
use ThinFrame\Applications\DependencyInjection\ContainerConfigurator;

/**
 * Class AbstractApplication
 *
 * @package ThinFrame\Applications
 * @since   0.2
 */
abstract class AbstractApplication
{
    /**
     * @var \ReflectionClass
     */
    private $reflector;
    /**
     * @var ContainerConfigurator
     */
    private $containerConfigurator;
    /**
     * @var Sequence<AbstractApplication>
     */
    private $parentApplications;
    /**
     * @var ApplicationContainerBuilder
     */
    private $containerBuilder;
    /**
     * @var bool
     */
    private $containerBuilderCompiled = false;
    /**
     * @var Map
     */
    private $metadata;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->reflector = new \ReflectionClass(get_called_class());

        $this->parentApplications = new Sequence();

        $this->parentApplications = $this->getParentApplications();

        $this->containerConfigurator = new ContainerConfigurator();

        $this->containerConfigurator->addAwareDefinition(
            new AwareDefinition(
                '\ThinFrame\Applications\DependencyInjection\ApplicationAwareInterface',
                'setApplication',
                'application'
            )
        );

        $this->containerConfigurator->addAwareDefinition(
            new AwareDefinition(
                '\ThinFrame\Applications\DependencyInjection\ApplicationAwareTrait',
                'setApplication',
                'application'
            )
        );

        $this->containerConfigurator->addAwareDefinition(
            new AwareDefinition(
                '\Symfony\Component\DependencyInjection\ContainerAwareInterface',
                'setContainer',
                'container'
            )
        );
        $this->containerConfigurator->addAwareDefinition(
            new AwareDefinition(
                '\ThinFrame\Applications\DependencyInjection\ContainerAwareTrait',
                'setContainer',
                'container'
            )
        );

        $this->initializeConfigurator($this->containerConfigurator);

        $this->containerBuilder = new ApplicationContainerBuilder(new FileLocator($this->getApplicationPath()));

    }

    /**
     * Get parent applications
     *
     * @return AbstractApplication[]
     */
    abstract protected function getParentApplications();

    /**
     * initialize configurator
     *
     * @param ContainerConfigurator $configurator
     *
     * @return mixed
     */
    abstract public function initializeConfigurator(ContainerConfigurator $configurator);

    /**
     * Get application path
     *
     * @return string
     */
    public function getApplicationPath()
    {
        return dirname($this->reflector->getFileName());
    }

    /**
     * @param array $loadedApplications
     *
     * @return ApplicationContainerBuilder
     */
    public function getApplicationContainer(&$loadedApplications = array())
    {
        if (!$this->containerBuilderCompiled) {
            if (count($loadedApplications) == 0) {
                $parent = true;
            } else {
                $parent = false;
            }
            foreach ($this->parentApplications as $app) {
                if (in_array(get_class($app), $loadedApplications)) {
                    continue;
                }
                $loadedApplications[] = get_class($app);
                $this->containerBuilder->merge($app->getApplicationContainer($loadedApplications));
            }

            $this->configure($this->containerBuilder);

            foreach ($this->getConfigurationFiles() as $file) {
                $this->containerBuilder->import($file);
            }
            if ($parent) {
                //setting syntetic services
                $definition = new Definition();
                $definition->setSynthetic(true);
                //inserting container as service
                $this->containerBuilder->setDefinition('container', $definition);
                $this->containerBuilder->set('container', $this->containerBuilder);

                //inserting application as service
                $this->containerBuilder->setDefinition('application', clone $definition);
                $this->containerBuilder->set('application', $this);

                $this->containerBuilder->compile();
            }

        }
        return $this->containerBuilder;
    }

    /**
     * Configure Application container
     *
     * @param ApplicationContainerBuilder $container
     * @param array $configuredApplications
     */
    public function configure(ApplicationContainerBuilder $container, $configuredApplications = [])
    {
        foreach ($this->parentApplications as $application) {
            if (in_array(get_class($application), $configuredApplications)) {
                continue;
            }
            $configuredApplications[] = get_class($application);
            /* @var $application AbstractApplication */
            $application->configure($container, $configuredApplications);
        }
        $this->getContainerConfigurator()->configureContainer($container);
    }

    /**
     * Get container configurator
     *
     * @return ContainerConfigurator
     */
    public function getContainerConfigurator()
    {
        return $this->containerConfigurator;
    }

    /**
     * Get configuration files
     *
     * @return mixed
     */
    abstract public function getConfigurationFiles();

    /**
     * Get application metadata
     *
     * @return Map
     */
    public function getMetadata()
    {
        if (is_null($this->metadata)) {
            $this->metadata = new Map();
            $this->processMetadata($this->metadata);
        }
        return $this->metadata;
    }

    /**
     * Process application metadata
     *
     * @param Map $metadata
     */
    public function processMetadata(Map &$metadata)
    {
        $metadata->set($this->getApplicationName(), $appMetadata = new Map($this->metaData()));

        $appMetadata->set('application_name', $this->getApplicationName());
        $appMetadata->set('application_path', $this->getApplicationPath());
        $appMetadata->set('application_namespace', $this->getNamespace());

        foreach ($this->getParentApplications() as $app) {
            $app->processMetadata($metadata);
        }
    }

    /**
     * Get application name
     *
     * @return string
     */
    abstract public function getApplicationName();

    /**
     * Get package metadata
     */
    protected function metaData()
    {
        return [];
    }

    /**
     * Get namespace
     *
     * @return string
     */
    public function getNamespace()
    {
        return $this->reflector->getNamespaceName();
    }
}
