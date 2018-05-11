<?php

/**
 * @author    Sorin Badea <sorin.badea91@gmail.com>
 * @license   MIT license (see the license file in the root directory)
 */

namespace ThinFrame\Foundation\Helper;

use ThinFrame\Foundation\Constant\OS;

/**
 * System system helper
 *
 * @package ThinFrame\Foundation\Helpers
 * @since   0.2
 */
class System
{
    /**
     * @var null|OS
     */
    private static $operatingSystem = null;

    /**
     * Get terminal width and height
     *
     * @return array
     */
    public static function getTerminalSizes()
    {
        if (self::getOperatingSystem()->equals(OS::WINDOWS) || self::getOperatingSystem()->equals(OS::UNKNOWN)) {
            return ['width' => 100, 'height' => 100];
        }
        preg_match_all("/rows.([0-9]+);.columns.([0-9]+);/", strtolower(exec('stty -a |grep columns')), $output);
        if (sizeof($output) == 3) {
            return [
                "height" => @$output[1][0],
                "width"  => @$output[2][0]
            ];
        } else {
            return [
                "width"  => 100,
                "height" => 100
            ];
        }
    }

    /**
     * Get the current operating system
     *
     * @return OS
     */
    public static function getOperatingSystem()
    {
        if (is_null(self::$operatingSystem)) {
            $name = strtolower(php_uname());
            $operatingSystem   = OS::UNKNOWN;
            if (strpos($name, 'darwin') !== false) {
                $operatingSystem = OS::DARWIN;
            } elseif (strpos($name, 'win') !== false) {
                $operatingSystem = OS::WINDOWS;
            } elseif (strpos($name, 'linux') !== false) {
                $operatingSystem = OS::LINUX;
            }
            self::$operatingSystem = new OS($operatingSystem);
        }


        return self::$operatingSystem;
    }
}
