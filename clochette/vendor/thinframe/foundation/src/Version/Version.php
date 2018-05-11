<?php

/**
 * @author    Sorin Badea <sorin.badea91@gmail.com>
 * @license   MIT license (see the license file in the root directory)
 */

namespace ThinFrame\Foundation\Version;

use Stringy\StaticStringy;
use ThinFrame\Foundation\Exception\InvalidArgumentException;

/**
 * Class Version
 *
 * @package ThinFrame\Foundation\Version
 * @since   0.2
 */
class Version implements VersionInterface
{
    /**
     * @var string
     */
    protected $versionString;
    /**
     * @var int
     */
    protected $major = 0;
    /**
     * @var int
     */
    protected $minor = 0;
    /**
     * @var int
     */
    protected $release = 0;
    /**
     * @var string
     */
    protected $suffix = '';

    /**
     * Constructor
     *
     * @param string $versionString version string
     */
    public function __construct($versionString)
    {
        $this->versionString = trim($versionString);
        $this->parseVersion();
    }

    /**
     * Parse version string
     *
     * @throws \ThinFrame\Foundation\Exception\InvalidArgumentException
     */
    private function parseVersion()
    {
        if (StaticStringy::startsWith($this->versionString, 'v')) {
            $this->versionString = StaticStringy::substr($this->versionString, 1);
        }
        if (strstr($this->versionString, '-')) {
            if (sscanf(
                $this->versionString,
                '%d.%d.%d-%s',
                $this->major,
                $this->minor,
                $this->release,
                $this->suffix
            ) != 4
            ) {
                throw new InvalidArgumentException('Invalid version format ' . $this->versionString);
            }
        } else {
            if (sscanf($this->versionString, '%d.%d.%d', $this->major, $this->minor, $this->release) != 3) {
                throw new InvalidArgumentException('Invalid version format ' . $this->versionString);
            }
        }
    }

    /**
     * Get major version
     *
     * @return int
     */
    public function getMajorVersion()
    {
        return $this->major;
    }

    /**
     * Get minor version
     *
     * @return int
     */
    public function getMinorVersion()
    {
        return $this->minor;
    }

    /**
     * Get release version
     *
     * @return int
     */
    public function getReleaseVersion()
    {
        return $this->release;
    }

    /**
     * Get version suffix
     *
     * @return string
     */
    public function getSuffix()
    {
        return $this->suffix;
    }

    /**
     * Compare with another version
     *
     * @param VersionInterface $version
     *
     * @return int (-1,0,1)
     */
    public function compare(VersionInterface $version)
    {
        if ($this->getMajorVersion() < $version->getMajorVersion()) {
            return -1;
        } elseif ($this->getMajorVersion() > $version->getMajorVersion()) {
            return 1;
        } else {
            if ($this->getMinorVersion() < $version->getMinorVersion()) {
                return -1;
            } elseif ($this->getMinorVersion() > $version->getMinorVersion()) {
                return 1;
            } else {
                if ($this->getReleaseVersion() < $version->getReleaseVersion()) {
                    return -1;
                } elseif ($this->getReleaseVersion() > $version->getReleaseVersion()) {
                    return 1;
                } else {
                    return 0;
                }
            }
        }
    }

    /**
     * Outputs the original version
     *
     * @return string
     */
    public function __toString()
    {
        return $this->versionString;
    }
}
