<?php

/**
 * /src/DependencyInjection/ApplicationAwareTrait.php
 *
 * @copyright 2013 Sorin Badea <sorin.badea91@gmail.com>
 * @license   MIT license (see the license file in the root directory)
 */

namespace ThinFrame\Applications\DependencyInjection;

use ThinFrame\Applications\AbstractApplication;

/**
 * Class ApplicationAwareTrait
 * @package ThinFrame\Applications\DependencyInjection
 * @since 0.2
 */
trait ApplicationAwareTrait
{
    /**
     * @var AbstractApplication
     */
    protected $application;

    /**
     * Attach application instance
     *
     * @param AbstractApplication $application
     */
    public function setApplication(AbstractApplication $application)
    {
        $this->application = $application;
    }
}
