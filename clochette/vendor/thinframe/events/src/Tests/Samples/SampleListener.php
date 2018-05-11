<?php

/**
 * /src/ThinFrame/Events/Tests/Samples/SampleListener.php
 *
 * @copyright 2013 Sorin Badea <sorin.badea91@gmail.com>
 * @license   MIT license (see the license file in the root directory)
 */

namespace ThinFrame\Events\Tests\Samples;

use ThinFrame\Events\Constants\Priority;
use ThinFrame\Events\ListenerInterface;
use ThinFrame\Events\SimpleEvent;

/**
 * Class SampleListener
 *
 * @package ThinFrame\Events\Tests\Samples
 * @since   0.2
 */
class SampleListener implements ListenerInterface
{
    public $triggered = false;

    /**
     * Get event mappings ["event"=>["method"=>"methodName","priority"=>1]]
     *
     * @return array
     */
    public function getEventMappings()
    {
        return [
            'test.listener' => [
                'method'   => 'onListenerTriggered',
                'priority' => Priority::MEDIUM
            ]
        ];
    }

    public function onListenerTriggered(SimpleEvent $e)
    {
        $this->triggered = true;
    }
}
