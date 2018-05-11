<?php

/**
 * /src/Dispatcher.php
 *
 * @copyright 2013 Sorin Badea <sorin.badea91@gmail.com>
 * @license   MIT license (see the license file in the root directory)
 */

namespace ThinFrame\Events;

use PhpCollection\Map;
use ThinFrame\Events\Constants\Priority;
use ThinFrame\Foundation\Constants\DataType;
use ThinFrame\Foundation\Helpers\TypeCheck;

/**
 * Class Dispatcher
 *
 * @package ThinFrame\Events
 * @since   0.2
 */
class Dispatcher
{
    /**
     * @var Map
     */
    private $queues;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->queues = new Map();
    }

    /**
     * Attach listener to dispatcher
     *
     * @param ListenerInterface $listener
     */
    public function attachListener(ListenerInterface $listener)
    {
        foreach ($listener->getEventMappings() as $eventId => $mapping) {
            $mapping['priority'] = isset($mapping['priority']) ? $mapping['priority'] : Priority::LOW;
            list($method, $priority) = array_values($mapping);
            $this->attachTo($eventId, [$listener, $method], $priority);
        }
    }

    /**
     * Attach callback to eventId
     *
     * @param string   $eventId
     * @param callable $callback will be triggered when event triggered
     * @param int      $priority - must be a Priority constant
     */
    public function attachTo($eventId, $callback, $priority = Priority::LOW)
    {
        TypeCheck::doCheck(DataType::STRING, DataType::CALLBACK, Priority::type());

        $queue = $this->queues->get($eventId)->getOrElse(new \SplPriorityQueue());
        /* @var $queue \SplPriorityQueue */

        $queue->insert($callback, $priority);

        if ($queue->count() == 1) {
            $this->queues->set($eventId, $queue);
        }
    }

    /**
     * Get binding to specific event
     *
     * @param string $eventId that will be triggered when binding is called
     *
     * @return callable
     */
    public function bindTo($eventId)
    {
        TypeCheck::doCheck(DataType::STRING);

        $context = $this;

        return function () use ($context, $eventId) {
            $context->trigger(new SimpleEvent($eventId, func_get_args()));
        };
    }

    /**
     * Trigger event.
     *
     * @param AbstractEvent $event
     */
    public function trigger(AbstractEvent $event)
    {
        $queue = clone $this->queues->get($event->getEventId())->getOrElse((new \SplPriorityQueue()));
        /* @var $queue \SplPriorityQueue */

        while ($queue->valid()) {
            $callback = $queue->extract();
            call_user_func_array(
                $callback,
                ['event' => $event]
            );
            if (!$event->shouldPropagate()) {
                break;
            }
        }
    }
}
