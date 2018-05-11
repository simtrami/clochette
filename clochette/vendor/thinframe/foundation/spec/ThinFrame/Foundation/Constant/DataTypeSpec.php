<?php

namespace spec\ThinFrame\Foundation\Constant;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use ThinFrame\Foundation\Constant\DataType;

/**
 * Class DataTypeSpec
 * @package spec\ThinFrame\Foundation\Constant
 * @mixin DataType
 */
class DataTypeSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(DataType::FLOAT);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('ThinFrame\Foundation\Constant\DataType');
        $this->shouldBeAnInstanceOf('ThinFrame\Foundation\DataType\AbstractEnum');
    }

    function it_should_equal_the_right_value()
    {
        $this->equals(DataType::FLOAT)->shouldBe(true);
        $this->equals(DataType::RESOURCE)->shouldBe(false);
    }

    function it_should_check_for_valid_values()
    {
        $this->shouldBeValid(DataType::RESOURCE);
        $this->shouldBeValid(DataType::FLOAT);
        $this->shouldBeValid(DataType::CALLBACK);
        $this->shouldBeValid(DataType::BOOLEAN);
        $this->shouldBeValid(DataType::DOUBLE);
        $this->shouldBeValid(DataType::INT);
        $this->shouldBeValid(DataType::STRING);
        $this->shouldBeValid(DataType::SKIP);
        $this->shouldNotBeValid('someDummyType');
    }

    function it_should_implement_string_magic_method()
    {
        $this->__toString()->shouldReturn(DataType::FLOAT);
        $this->setValue(DataType::DOUBLE);
        $this->__toString()->shouldNotReturn(DataType::FLOAT);
        $this->__toString()->shouldReturn(DataType::DOUBLE);
    }

    function it_should_return_a_map_of_values()
    {
        $this->getMap()->shouldHaveType('PhpCollection\Map');

    }
}
