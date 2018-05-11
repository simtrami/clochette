<?php

namespace spec\ThinFrame\Foundation\Helper;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use ThinFrame\Foundation\Helper\System;

/**
 * Class SystemSpec
 * @package spec\ThinFrame\Foundation\Helper
 * @mixin System
 */
class SystemSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('ThinFrame\Foundation\Helper\System');
    }

    /**
     * It should return an array with terminal screen sizes
     */
    function it_should_return_terminal_screen_size()
    {
        self::getTerminalSizes()->shouldBeArray();
        self::getTerminalSizes()->shouldHaveKey("width");
        self::getTerminalSizes()->shouldHaveKey("height");
        self::getTerminalSizes()->shouldHaveCount(2);
    }

    /**
     * It should return the host operating system
     */
    function it_should_return_correct_os()
    {
        self::getOperatingSystem()->shouldHaveType('ThinFrame\Foundation\Constant\OS');
    }
}
