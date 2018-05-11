<?php

/**
 * @author    Sorin Badea <sorin.badea91@gmail.com>
 * @license   MIT license (see the license file in the root directory)
 */

namespace ThinFrame\Foundation\Constant;

use ThinFrame\Foundation\DataType\AbstractEnum;

/**
 * Operating systems
 *
 * @package ThinFrame\Foundation\Constants
 * @since   0.2
 */
final class OS extends AbstractEnum
{
    const WINDOWS = 'windows';
    const LINUX   = 'linux';
    const DARWIN  = 'darwin';
    const UNKNOWN = 'unknown';
}
