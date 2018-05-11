<?php

namespace spec\ThinFrame\Foundation\Version;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use ThinFrame\Foundation\Version\Version;
use ThinFrame\Foundation\Version\VersionInterface;

/**
 * Class VersionSpec
 * @package spec\ThinFrame\Foundation\Version
 * @mixin Version
 */
class VersionSpec extends ObjectBehavior
{

    function let()
    {
        $this->beConstructedWith('2.0.1-alpha-1');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('ThinFrame\Foundation\Version\Version');
    }

    function it_should_return_a_correct_major_version()
    {
        $this->getMajorVersion()->shouldBe(2);
    }

    function it_should_return_a_correct_minor_version()
    {
        $this->getMinorVersion()->shouldBe(0);
    }

    function it_should_return_a_correct_release_version()
    {
        $this->getReleaseVersion()->shouldBe(1);
    }

    function it_should_return_a_correct_suffix()
    {
        $this->getSuffix()->shouldBe('alpha-1');
    }

    function it_should_have_a_string_representation()
    {
        $this->__toString()->shouldBe('2.0.1-alpha-1');
    }

    function it_should_be_compared_correctly_with_another_version(
        VersionInterface $smaller,
        VersionInterface $equal,
        VersionInterface $bigger
    ) {
        $smaller->getMajorVersion()->willReturn(0);
        $smaller->getMinorVersion()->willReturn(5);
        $smaller->getReleaseVersion()->willReturn(3);

        $equal->getMajorVersion()->willReturn(2);
        $equal->getMinorVersion()->willReturn(0);
        $equal->getReleaseVersion()->willReturn(1);

        $bigger->getMajorVersion()->willReturn(4);
        $bigger->getMinorVersion()->willReturn(3);
        $bigger->getReleaseVersion()->willReturn(0);

        $this->compare($smaller)->shouldReturn(1);
        $this->compare($equal)->shouldReturn(0);
        $this->compare($bigger)->shouldReturn(-1);
    }
}
