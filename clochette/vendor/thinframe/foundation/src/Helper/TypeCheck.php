<?php

/**
 * @author    Sorin Badea <sorin.badea91@gmail.com>
 * @license   MIT license (see the license file in the root directory)
 */

namespace ThinFrame\Foundation\Helper;

use ThinFrame\Foundation\Constant\DataType;
use ThinFrame\Foundation\Exception\InvalidArgumentException;

/**
 * Class TypeCheck
 *
 * @package ThinFrame\Foundation\Helpers
 * @since   0.2
 */
final class TypeCheck
{
    const MESSAGE = 'Argument %s passed to %s() must be of type %s, %s given';
    /**
     * Validators for each data type
     *
     * @var array
     */
    protected static $validators = [
        DataType::INT      => 'is_int',
        DataType::BOOLEAN  => 'is_bool',
        DataType::CALLBACK => 'is_callable',
        DataType::FLOAT    => 'is_float',
        DataType::RESOURCE => 'is_resource',
        DataType::DOUBLE   => 'is_double',
        DataType::STRING   => 'ThinFrame\Foundation\Helper\TypeCheck::isString'
    ];

    /**
     * Check if function arguments match the provided types
     */
    public static function doCheck()
    {
        $backtrace         = debug_backtrace();
        $functionArguments = $backtrace[1]['args'];
        $functionName      = $backtrace[1]['function'];
        $doChecks          = func_get_args();

        unset($backtrace);

        array_walk(
            $functionArguments,
            function ($value, $key) use (&$doChecks, $functionName) {
                if (is_null($value) || !isset($doChecks[$key]) || $doChecks[$key] == DataType::SKIP) {
                    return;
                }
                if (!TypeCheck::checkValue($value, $doChecks[$key])) {
                    if (is_callable($doChecks[$key])) {
                        $doChecks[$key] = 'unknown (custom validator)';
                    }
                    if (is_object($value)) {
                        $message = sprintf(
                            TypeCheck::MESSAGE,
                            $key,
                            $functionName,
                            $doChecks[$key],
                            'instance of ' . get_class($value)
                        );
                    } else {
                        $message = sprintf(
                            TypeCheck::MESSAGE,
                            $key,
                            $functionName,
                            $doChecks[$key],
                            gettype($value)
                        );
                    }
                    throw new InvalidArgumentException($message);
                }

            }
        );
    }

    /**
     * Check value against the provided type
     *
     * @param mixed  $value value to be checked
     * @param string $type  value type
     *
     * @return bool
     */
    public static function checkValue($value, $type)
    {
        $validator = is_callable($type) ? $type : (isset(self::$validators[$type]) ? self::$validators[$type] : null);

        if (!is_null($value)) {
            return call_user_func($validator, $value);
        }

        return false;
    }

    /**
     * Check if provided value is a string
     *
     * @param mixed $string string to be checked
     *
     * @return bool
     */
    public static function isString($string)
    {
        if (intval($string) == $string || is_string($string)) {
            return true;
        }

        return false;
    }
}
