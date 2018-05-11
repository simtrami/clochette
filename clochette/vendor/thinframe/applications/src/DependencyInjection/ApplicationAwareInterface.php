<?php

/**
 * /src/DependencyInjection/ApplicationAwareInterface.php
 *
 * @copyright 2013 Sorin Badea <sorin.badea91@gmail.com>
 * @license   MIT license (see the license file in the root directory)
 */

namespace ThinFrame\Applications\DependencyInjection;

use ThinFrame\Applications\AbstractApplication;

/**
 * Interface ApplicationAwareInterface
 *
 * @package ThinFrame\Applications\DependencyInjection
 * @since   0.2
 */
interface ApplicationAwareInterface
{
    /**
     * Attach application to current instance
     *
     * @param AbstractApplication $application
     *
     * @return mixed
     */
    public function setApplication(AbstractApplication $application);
}