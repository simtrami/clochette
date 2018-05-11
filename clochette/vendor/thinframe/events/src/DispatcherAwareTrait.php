<?php

/**
 * /src/DispatcherAwareTrait.php
 *
 * @copyright 2013 Sorin Badea <sorin.badea91@gmail.com>
 * @license   MIT license (see the license file in the root directory)
 */

namespace ThinFrame\Events;

/**
 * Class DispatcherAwareTrait
 *
 * @package ThinFrame\Events
 * @since 0.2
 */
trait DispatcherAwareTrait
{
    /**
     * @var Dispatcher
     */
    protected $dispatcher;

    /**
     * @param Dispatcher $dispatcher
     */
    public function setDispatcher(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }
}
