<?php

/**
 * /src/ListenerInterface.php
 *
 * @copyright 2013 Sorin Badea <sorin.badea91@gmail.com>
 * @license   MIT license (see the license file in the root directory)
 */

namespace ThinFrame\Events;

/**
 * Interface ListenerInterface
 *
 * @package ThinFrame\Events
 * @since   0.2
 */
interface ListenerInterface
{
    /**
     * Get event mappings ["event"=>["method"=>"methodName","priority"=>1]]
     *
     * @return array
     */
    public function getEventMappings();
}