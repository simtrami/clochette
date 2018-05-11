<?php

/**
 * /src/ThinFrame/Events/Tests/ApplicationTest.php
 *
 * @copyright 2013 Sorin Badea <sorin.badea91@gmail.com>
 * @license   MIT license (see the license file in the root directory)
 */

namespace ThinFrame\Events\Tests;

use ThinFrame\Events\Dispatcher;
use ThinFrame\Events\EventsApplication;

/**
 * Class ApplicationTest
 *
 * @package ThinFrame\Events\Tests
 * @since   0.2
 */
class ApplicationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * test dispatcher presence
     */
    public function testDispatcher()
    {
        $application = new EventsApplication();

        $this->assertEquals(
            $application->getApplicationName(),
            'ThinFrameEvents',
            'Application name should be correct'
        );

        $this->assertTrue(
            $application->getApplicationContainer()->get('thinframe.events.dispatcher') instanceof Dispatcher,
            'Application should return the correct service'
        );
    }
}
