<?php

/**
 * /src/ThinFrame/Applications/DependencyInjection/Extensions/ConfigurationAwareInterface.php
 *
 * @copyright 2013 Sorin Badea <sorin.badea91@gmail.com>
 * @license   MIT license (see the license file in the root directory)
 */

namespace ThinFrame\Applications\DependencyInjection\Extensions;

/**
 * Interface ConfigurationAwareInterface
 *
 * @package ThinFrame\Applications\DependencyInjection\Extensions
 * @since   0.2
 */
interface ConfigurationAwareInterface
{
    /**
     * @param array $configuration
     *
     */
    public function setConfiguration(array $configuration);
}
