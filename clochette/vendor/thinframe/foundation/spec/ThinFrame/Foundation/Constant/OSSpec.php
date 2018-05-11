<?php

namespace spec\ThinFrame\Foundation\Constant;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use ThinFrame\Foundation\Constant\OS;

/**
 * Class OSSpec
 * @package spec\ThinFrame\Foundation\Constant
 * @mixin OS
 */
class OSSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(OS::DARWIN);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('ThinFrame\Foundation\Constant\OS');
        $this->shouldBeAnInstanceOf('ThinFrame\Foundation\DataType\AbstractEnum');
    }

    function it_should_equal_the_right_value()
    {
        $this->equals(OS::DARWIN)->shouldBe(true);
        $this->equals(OS::UNKNOWN)->shouldBe(false);
    }

    function it_should_check_for_valid_values()
    {
        $this->shouldBeValid(OS::DARWIN);
        $this->shouldBeValid(OS::UNKNOWN);
        $this->shouldBeValid(OS::LINUX);
        $this->shouldBeValid(OS::WINDOWS);
        $this->shouldNotBeValid('mac-os');
    }

    function it_should_implement_string_magic_method()
    {
        $this->__toString()->shouldReturn(OS::DARWIN);
        $this->setValue(OS::LINUX);
        $this->__toString()->shouldNotReturn(OS::DARWIN);
        $this->__toString()->shouldReturn(OS::LINUX);
    }

    function it_should_return_a_map_of_values()
    {
        $this->getMap()->shouldHaveType('PhpCollection\Map');
    }
}
