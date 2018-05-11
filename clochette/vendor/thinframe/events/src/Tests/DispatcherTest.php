<?php

/**
 * /src/ThinFrame/Events/Tests/DispatcherTest.php
 *
 * @copyright 2013 Sorin Badea <sorin.badea91@gmail.com>
 * @license   MIT license (see the license file in the root directory)
 */

namespace ThinFrame\Events\Tests;

use ThinFrame\Events\Constants\Priority;
use ThinFrame\Events\Dispatcher;
use ThinFrame\Events\SimpleEvent;
use ThinFrame\Events\Tests\Samples\SampleListener;
use ThinFrame\Foundation\Exceptions\InvalidArgumentException;

/**
 * Class DispatcherTest
 *
 * @package ThinFrame\Events\Tests
 * @since   0.2
 */
class DispatcherTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Dispatcher
     */
    private $dispatcher;

    /**
     * Test dispatcher attachTo method arguments validation
     */
    public function testAttachToArgumentsValidation()
    {
        try {
            $this->dispatcher->attachTo([], 'var_dump');
            $this->assertFalse(true, 'Method should throw an invalid argument exception');
        } catch (\Exception $e) {
            $this->assertTrue(
                $e instanceof InvalidArgumentException,
                'Method should throw an invalid argument exception'
            );
        }

        try {
            $this->dispatcher->attachTo('some_event', 'invalid_callback');
            $this->assertFalse(true, 'Method should throw an invalid argument exception');
        } catch (\Exception $e) {
            $this->assertTrue(
                $e instanceof InvalidArgumentException,
                'Method should throw an invalid argument exception'
            );
        }

        try {
            $this->dispatcher->attachTo('some_event', 'var_dump', 12345);
            $this->assertFalse(true, 'Method should throw an invalid argument exception');
        } catch (\Exception $e) {
            $this->assertTrue(
                $e instanceof InvalidArgumentException,
                'Method should throw an invalid argument exception'
            );
        }

        try {
            $this->dispatcher->attachTo('some_event', 'var_dump', Priority::CRITICAL);
        } catch (\Exception $e) {
            $this->assertFalse(true, 'Method should\'t throw an exception');
        }
    }

    /**
     * Test a simple event
     */
    public function testSimpleEvents()
    {
        $triggered = false;

        $callback = function (SimpleEvent $e) use (&$triggered) {
            $triggered = true;
        };

        $this->dispatcher->attachTo('test.simple.events', $callback);

        $this->dispatcher->trigger(new SimpleEvent('test.simple.events'));

        $this->assertTrue($triggered, 'Callback should have been triggered');
    }

    /**
     * Test events priorities and propagation
     */
    public function testEventsPriorityAndPropagation()
    {
        $critical_triggered = false;
        $medium_triggered   = false;

        $critical_callback = function (SimpleEvent $e) use (&$critical_triggered) {
            $critical_triggered = true;
            $e->stopPropagation();
        };

        $medium_callback = function (SimpleEvent $e) use (&$medium_triggered) {
            $medium_triggered = true;
        };

        $this->dispatcher->attachTo('test.priority.propagation', $medium_callback, Priority::MEDIUM);
        $this->dispatcher->attachTo('test.priority.propagation', $critical_callback, Priority::CRITICAL);

        $this->dispatcher->trigger(new SimpleEvent('test.priority.propagation'));

        $this->assertTrue($critical_triggered, 'Critical callback should have been triggered');

        $this->assertFalse($medium_triggered, 'Medium callback shouldn\'t have been triggered');
    }

    /**
     * Test event bindings
     */
    public function testEventBindings()
    {
        $triggered = false;

        $callback = function (SimpleEvent $e) use (&$triggered) {
            $triggered = true;
        };

        $this->dispatcher->attachTo('test.event.bindings', $callback);

        $binding = $this->dispatcher->bindTo('test.event.bindings');

        $binding();

        $this->assertTrue($triggered, 'Binding should have triggered event');
    }

    /**
     * Test Listener
     */
    public function testListener()
    {
        $listener = new SampleListener();

        $this->dispatcher->attachListener($listener);

        $this->dispatcher->trigger(new SimpleEvent('test.listener'));

        $this->assertTrue($listener->triggered, 'Listener should have been triggered');
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->dispatcher = new Dispatcher();
    }

}