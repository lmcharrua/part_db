<?php
/*
    part-db version 0.1
    Copyright (C) 2005 Christoph Lechner
    http://www.cl-projects.de/

    part-db version 0.2+
    Copyright (C) 2009 K. Jacobs and others (see authors.php)
    http://code.google.com/p/part-db/

    This program is free software; you can redistribute it and/or
    modify it under the terms of the GNU General Public License
    as published by the Free Software Foundation; either version 2
    of the License, or (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA
*/

namespace PartDB\Tools;

use Exception;

/**
 * @file SystemVersion.php
 * @brief class SystemVersion
 *
 * @class SystemVersion
 * @brief Class SystemVersion
 *
 * A SystemVersion object represents a system version with the following attributes:
 *  - major version
 *  - minor version
 *  - update version
 *  - (release candidate number)
 *  - type ('stable' or 'unstable', depends on release candidate number)
 *
 * A version string has this structure:
 *      "major.minor.update.[RC]" --> "#.#.#[.RC#]" (brackets means "optional", # stands for numbers)
 *
 * @note    We will always use the format "#.#.#[.RC#]" for handling with version numbers!
 *          Also the filenames of update packages and their version descriptions uses that format!
 *          Only for displaying the version number in a HTML output, we use the format "#.#.# [RC#]" (space instead of dot).
 *
 * @par Examples:
 *  - "0.2.3":          stable version 0.2.3
 *  - "0.2.3.RC4":      unstable version 0.2.3, release candidate 4
 *
 * @author kami89
 */
class SystemVersion
{
    /********************************************************************************
     *
     *   Normal Attributes
     *
     *********************************************************************************/

    /** @var  integer */
    private $major_version      = null;
    /** @var integer */
    private $minor_version      = null;
    /** @var integer */
    private $update_version     = null;
    /** @var integer Release Candidate number, zero means "stable version" */
    private $release_candidate  = null;
    /** @var string the version type ('stable' or 'unstable') */
    private $type               = null;

    /********************************************************************************
     *
     *   Static Attributes ("cached" Attributes)
     *
     *********************************************************************************/

    /** @var SystemVersion the latest stable version which is available */
    private static $latest_stable_version      = null;
    /** @var SystemVersion the latest unstable version which is available */
    private static $latest_unstable_version    = null;

    /********************************************************************************
     *
     *   Constructor / Destructor
     *
     *********************************************************************************/

    /**
     * Constructor
     *
     * @param string $version_string        @li here we have to supply the version string
     *                                      @li Format: "#.#.#[.RC#]" (brackets means "optional", # stands for numbers)
     *                                      @li Examples see in the description of this class SystemVersion.
     *
     * @throws Exception if the parameter was not valid
     */
    public function __construct(string $version_string)
    {
        $version = str_replace(' ', '.', strtolower(trim($version_string)));

        $dev_version = false;
        if (strpos($version, 'dev')) {
            $version =  str_replace('dev', 'rc0', $version);
            $dev_version = true;
        }

        // if $version has no "RC" or "dev", we will add it
        if (strpos($version, 'rc') === false) {
            $version .= '.rc0';
        }

        $version = str_replace('rc', '', $version);
        $array = explode('.', $version);

        if ((count($array) != 4)
            || ((! \is_int($array[0])) && (! ctype_digit($array[0])))
            || ((! \is_int($array[1])) && (! ctype_digit($array[1])))
            || ((! \is_int($array[2])) && (! ctype_digit($array[2])))
            || ((! \is_int($array[3])) && (! ctype_digit($array[3])))) {
            debug('error', sprintf(_('Fehlerhafte Version: "%s"', $version)), __FILE__, __LINE__, __METHOD__);
            throw new Exception(_('Es gab ein Fehler bei der Auswertung des Version-Strings!'));
        }

        $this->major_version = $array[0];
        $this->minor_version = $array[1];
        $this->update_version = $array[2];
        $this->release_candidate = $array[3];

        if ($this->release_candidate == 0) {
            $this->type = 'stable';
        } else {
            $this->type = 'unstable';
        }

        if ($dev_version) {
            $this->type = 'development';
        }
    }

    /********************************************************************************
     *
     *   Basic Methods
     *
     *********************************************************************************/

    /**
     * Generate a string of the version
     *
     * @param boolean $internal_format  If true, the internal format (with points instead of spaces) will be used.
     *                                  All other parameters will be ignored if this is true.
     * @param boolean $hide_rc          if true, the release candidate number will never be printed
     * @param boolean $hide_rev         if true, the svn revision number will never be printed @deprecated
     * @param boolean $show_type        if true, the type (stable or unstable) will be printed (in brackets)
     *
     * @return string       the version string, like "0.2.3.RC2" (internal format), "0.2.3", "0.2.3 RC5", "0.2.3 (stable)", and so on...
     *
     * @note    The release candidate number won't be printed if it is zero (even if "$hide_rc == false")!
     */
    public function asString(bool $internal_format = true, bool $hide_rc = false, bool $hide_rev = false, bool $show_type = false) : string
    {
        $string = $this->major_version.'.'.$this->minor_version.'.'.$this->update_version;

        if ($internal_format) {
            if ($this->release_candidate > 0) {
                $string .= '.RC'.$this->release_candidate;
            }

            if ($this->type == 'development') {
                $string .= '.dev';
            }

            return $string;
        } else {
            if ($this->type == 'development') {
                $string .= '-dev';
            }

            if (($this->release_candidate > 0) && (! $hide_rc)) {
                $string .= ' RC'.$this->release_candidate;
            }

            if ($show_type) {
                $string .= ' ('.$this->type.')';
            }

            return $string;
        }
    }

    /**
     * Check if this Version is newer than another Version
     *
     * With this function we can compare two objects.
     *
     * @param SystemVersion $version_2    the Version which we want to compare with this Version
     *
     * @return boolean  @li true if this Version is newer than $version_2
     *                  @li otherwise false (equal or older)
     */
    public function isNewerThan(SystemVersion $version_2) : bool
    {
        if ($this->major_version != $version_2->major_version) {
            return ($this->major_version > $version_2->major_version);
        }

        if ($this->minor_version != $version_2->minor_version) {
            return ($this->minor_version > $version_2->minor_version);
        }

        if ($this->update_version != $version_2->update_version) {
            return ($this->update_version > $version_2->update_version);
        }

        // both versions have the same major, minor and update version!

        if (($this->type == 'development') && ($version_2->type != 'development')) {
            return false;
        } //Version two is unstable, or stable, this is development

        if (($this->type != 'development') && ($version_2->type == 'development')) {
            return true;
        } //Version two is development, this is stable.

        if (($this->type == 'development') && ($version_2->type == 'development')) {
            return false;
        } //Both versions are dev. We can not really compare this (yet).

        if (($this->release_candidate == 0) && ($version_2->release_candidate > 0)) {
            return true;
        } // this is stable, $version_2 is only a release candidate

        if (($this->release_candidate > 0) && ($version_2->release_candidate == 0)) {
            return false;
        } // $version_2 is stable, this version is only a release candidate

        if ($this->release_candidate > $version_2->release_candidate) {
            return true;
        } // this version is the newer release candidate than $version_2

        if ($this->release_candidate < $version_2->release_candidate) {
            return false;
        } // this version is the older release candidate than $version_2

        // both versions have the same major, minor, update and release candidate number!

        return false; // this version is equal to or lower than $version_2
    }

    /********************************************************************************
     *
     *   Getters
     *
     *********************************************************************************/

    /**
     * Get the version type of this version ('stable' or 'unstable')
     *
     * @return string       'stable' or 'unstable'
     */
    public function getVersionType() : string
    {
        return $this->type;
    }

    /********************************************************************************
     *
     *   Static Methods
     *
     *********************************************************************************/

    /**
     * Get the installed system version
     *
     * @return SystemVersion      the installed system version
     *
     * @throws Exception if there was an error
     */
    public static function getInstalledVersion() : SystemVersion
    {
        global $config;

        return new SystemVersion($config['system']['version']);
    }

    /**
     * Get the latest system version which is available (in the internet or in the directory "/updates/")
     *
     * @param string $type      'stable' or 'unstable'
     *
     * @return SystemVersion    the latest available system version
     *
     * @throws Exception if there was an error
     *
     * @todo    Search also in the local direcotry "/updates/" for updates.
     *          This is needed for manual updates (maybe the server has no internet access, or no "curl").
     */
    public static function getLatestVersion(string $type) : SystemVersion
    {
        if ((($type == 'stable') && (! \is_object(self::$latest_stable_version)))
            || (($type == 'unstable') && (! \is_object(self::$latest_unstable_version)))) {
            $ini = curlGetData('http://kami89.myparts.info/updates/latest.ini');
            $ini_array = parse_ini_string($ini, true);

            self::$latest_stable_version    = new SystemVersion($ini_array['stable']['version']);
            self::$latest_unstable_version  = new SystemVersion($ini_array['unstable']['version']);
        }

        switch ($type) {
            case 'stable':
                return self::$latest_stable_version;
            case 'unstable':
                return self::$latest_unstable_version;
            default:
                debug('error', '$type='.print_r($type, true), __FILE__, __LINE__, __METHOD__);
                throw new Exception('$type hat einen ungültigen Inhalt!');
        }
    }
}
