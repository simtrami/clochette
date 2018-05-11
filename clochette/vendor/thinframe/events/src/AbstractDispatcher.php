<?php

/**
 * /src/AbstractDispatcher.php
 *
 * @copyright 2013 Sorin Badea <sorin.badea91@gmail.com>
 * @license   MIT license (see the license file in the root directory)
 */

namespace ThinFrame\Events;

use Stringy\StaticStringy;
use ThinFrame\Events\Constants\Priority;
use ThinFrame\Foundation\Exceptions\InvalidArgumentException;

/**
 * Class AbstractDispatcher
 *
 * @package ThinFrame\Events
 * @since   0.2
 */
abstract class AbstractDispatcher extends Dispatcher
{
    /**
     * @param       $name
     * @param array $arguments
     *
     * @throws \ThinFrame\Foundation\Exceptions\InvalidArgumentException
     * @throws \BadMethodCallException
     */
    public function __call($name, array $arguments)
    {
        if (StaticStringy::startsWith($name, 'on')) {
            $eventId = StaticStringy::camelize(substr($name, 2));
            if (!isset($arguments[0]) || !is_callable($arguments[0])) {
                throw new InvalidArgumentException('Invalid or missing callback for event');
            }
            $priority = isset($arguments[1]) ? intval($arguments[1]) : Priority::LOW;
            $this->attachTo($eventId, $arguments[0], $priority);
        } else {
            throw new \BadMethodCallException('Method ' . $name . ' is not defined under ' . get_called_class());
        }
    }
}