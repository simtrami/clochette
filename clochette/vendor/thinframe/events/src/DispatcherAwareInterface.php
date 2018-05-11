<?php

/**
 * /src/DispatcherAwareInterface.php
 *
 * @copyright 2013 Sorin Badea <sorin.badea91@gmail.com>
 * @license   MIT license (see the license file in the root directory)
 */

namespace ThinFrame\Events;

/**
 * Interface DispatcherAwareInterface - describes a dispatcher aware object
 *
 * @package ThinFrame\Events
 * @since   0.2
 */
interface DispatcherAwareInterface
{
    /**
     * @param Dispatcher $dispatcher
     */
    public function setDispatcher(Dispatcher $dispatcher);
}