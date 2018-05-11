<?php

/**
 * /src/DependencyInjection/ContainerAwareTrait.php
 *
 * @copyright 2013 Sorin Badea <sorin.badea91@gmail.com>
 * @license   MIT license (see the license file in the root directory)
 */

namespace ThinFrame\Applications\DependencyInjection;

/**
 * Class ContainerAwareTrait
 * @package ThinFrame\Applications\DependencyInjection
 * @since 0.2
 */
trait ContainerAwareTrait
{
    /**
     * @var ApplicationContainerBuilder
     */
    protected $container;

    /**
     * Attach the container instance
     *
     * @param ApplicationContainerBuilder $container
     */
    public function setContainer(ApplicationContainerBuilder $container)
    {
        $this->container = $container;
    }
}