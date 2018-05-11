<?php

/**
 * @author    Sorin Badea <sorin.badea91@gmail.com>
 * @license   MIT license (see the license file in the root directory)
 */

namespace ThinFrame\Foundation\Constant;

use ThinFrame\Foundation\DataType\AbstractEnum;

/**
 * DataType - list of data types supported by the TypeCheck
 *
 * @package ThinFrame\Foundation\Constants
 * @since   0.2
 */
final class DataType extends AbstractEnum
{
    const SKIP     = 'skip';
    const INT      = 'int';
    const STRING   = 'string';
    const BOOLEAN  = 'boolean';
    const CALLBACK = 'callback';
    const FLOAT    = 'float';
    const RESOURCE = 'resource';
    const DOUBLE   = 'double';
}
