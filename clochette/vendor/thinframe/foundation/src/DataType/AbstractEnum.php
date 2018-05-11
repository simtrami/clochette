<?php

/**
 * @author    Sorin Badea <sorin.badea91@gmail.com>
 * @license   MIT license (see the license file in the root directory)
 */

namespace ThinFrame\Foundation\DataType;

use PhpCollection\Map;
use ThinFrame\Foundation\Exception\InvalidArgumentException;

/**
 * AbstractEnum - emulates the functionality of an enum
 *
 * @package ThinFrame\Foundation\DataTypes
 * @since   0.2
 */
abstract class AbstractEnum
{
    /**
     * @var string instance value
     */
    private $value;

    /**
     * Constructor
     *
     * @param string|number|bool $value value of the enum
     *
     * @throws InvalidArgumentException in case of invalid enum value
     */
    final public function __construct($value)
    {
        $this->setValue($value);
    }

    /**
     * Set a new value to the enum instance
     *
     * @param string $value enum value
     *
     * @throws InvalidArgumentException in case of invalid enum value
     */
    final public function setValue($value)
    {
        if (self::isValid($value)) {
            $this->value = $value;
        } else {
            throw new InvalidArgumentException("Invalid enum value supplied");
        }
    }

    /**
     * Check if provided value is a valid constant value
     *
     * @param string $value value to be checked
     *
     * @return bool
     */
    final public static function isValid($value)
    {
        return self::getMap()->contains($value);
    }

    /**
     * Get a Map with constant name/value pairs
     *
     * @return Map
     */
    final public static function getMap()
    {
        $reflector = new \ReflectionClass(get_called_class());

        return new Map($reflector->getConstants());
    }

    /**
     * TypeHint support
     *
     * @return callable
     */
    final public static function type()
    {
        $class = get_called_class();

        return function ($value) use ($class) {
            return $class::isValid($value);
        };
    }

    /**
     * Check if current instance match provided value
     *
     * @param mixed $value value to compare against
     *
     * @return bool
     */
    final public function equals($value)
    {
        return ($this->__toString() == $value);
    }

    /**
     * toString magic method
     *
     * @return string
     */
    final public function __toString()
    {
        return (string)$this->value;
    }
}
