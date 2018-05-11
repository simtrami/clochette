<?php

/**
 * /src/AbstractEvent.php
 *
 * @copyright 2013 Sorin Badea <sorin.badea91@gmail.com>
 * @license   MIT license (see the license file in the root directory)
 */

namespace ThinFrame\Events;

use PhpCollection\Map;
use ThinFrame\Foundation\Constants\DataType;
use ThinFrame\Foundation\Helpers\TypeCheck;

/**
 * Class AbstractEvent
 *
 * @package ThinFrame\Events
 * @since   0.2
 */
abstract class AbstractEvent
{
    /**
     * @var bool
     */
    private $propagate = true;
    /**
     * @var string
     */
    private $eventId;
    /**
     * @var Map
     */
    private $payload;

    /**
     * Constructor
     *
     * @param string $eventId
     * @param array  $payload
     */
    public function __construct($eventId, array $payload = [])
    {
        TypeCheck::doCheck(DataType::STRING);
        $this->eventId = $eventId;
        $this->payload = new Map($payload);
    }

    /**
     * Get event payload
     *
     * @return Map
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * Get event id
     *
     * @return mixed
     */
    public function getEventId()
    {
        return $this->eventId;
    }

    /**
     * Should this event propagate
     *
     * @return bool
     */
    public function shouldPropagate()
    {
        return $this->propagate;
    }

    /**
     * Stop Event Propagation
     */
    public function stopPropagation()
    {
        $this->propagate = false;
    }
}
