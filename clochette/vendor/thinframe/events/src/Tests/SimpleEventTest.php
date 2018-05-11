<?php

/**
 * /src/ThinFrame/Events/Tests/SimpleEventTest.php
 *
 * @copyright 2013 Sorin Badea <sorin.badea91@gmail.com>
 * @license   MIT license (see the license file in the root directory)
 */

namespace ThinFrame\Events\Tests;

use ThinFrame\Events\SimpleEvent;
use ThinFrame\Foundation\Exceptions\InvalidArgumentException;

/**
 * Class SimpleEventTest
 *
 * @package ThinFrame\Events\Tests
 * @since   0.2
 */
class SimpleEventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test constructor
     */
    public function testConstructor()
    {
        try {
            new SimpleEvent(['blablabla']);
            $this->assertFalse(
                true,
                'Constructor should accept only string as first parameters'
            );
        } catch (\Exception $e) {
            $this->assertTrue(
                $e instanceof InvalidArgumentException,
                'Constructor should throw the right exception'
            );
        }
    }

    /**
     * Test event payload
     */
    public function testPayload()
    {
        $event = new SimpleEvent('some_event', ['environment' => 'development']);

        $this->assertEquals('some_event', $event->getEventId(), 'Event should have the right id');

        $this->assertEquals(
            'development',
            $event->getPayload()->get('environment')->get(),
            'Event should carry the right payload'
        );
    }

    /**
     * Test event propagation
     */
    public function testPropagation()
    {
        $event = new SimpleEvent('some_other_event');

        $this->assertTrue($event->shouldPropagate(), 'Event should propagate by default');

        $event->stopPropagation();

        $this->assertFalse($event->shouldPropagate(), 'Event shouldn\'t propagate');
    }
}
