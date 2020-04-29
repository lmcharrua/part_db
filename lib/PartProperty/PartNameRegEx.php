<?php
/*
    Part-DB Version 0.4+ "nextgen"
    Copyright (C) 2017 Jan Böhmer
    https://github.com/jbtronics

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

namespace PartDB\PartProperty;

use Exception;

class PartNameRegEx
{
    /** @var string  */
    private static $pattern = '/^(\/.+\/)(?:@([fn]+))?(?:\$(.+))*$/';

    /** @var string  */
    private $regex = '';
    /** @var string  */
    private $flags_str = '';
    /** @var string[] */
    private $capture_names = array();

    /**
     * PartNameRegEx constructor.
     * @param $partname_regex string The string which should be parsed
     * @throws Exception If there was an Error.
     */
    public function __construct(string $partname_regex)
    {
        if (!empty($partname_regex)) {
            if (!self::isValid($partname_regex)) {
                throw new Exception('The PartNameRegex string (' . $partname_regex . ') is not valid!');
            }

            $this->parse($partname_regex);
        }
    }

    private function parse(string $str)
    {
        $matches = array();
        mb_ereg(self::getPattern(false, true), $str, $matches);

        $this->regex = $matches[1];
        $this->flags_str = $matches[2];

        $this->capture_names = explode('$', $matches[3]);
    }


    /**
     * Returns the Regular Expression part.
     * @param $is_mb bool True if should be prepared for the multibyte regex functions. (Strip slashes)
     * @return string The Reguala Expression.
     */
    public function getRegex(bool $is_mb = false) : string
    {
        if ($is_mb) {
            return regexStripSlashes($this->regex);
        } else {
            return $this->regex;
        }
    }

    public function getFlags() : string
    {
        return $this->flags_str;
    }

    /**
     * Checks if the Name filter is enforced, so it cant be ignored.
     * @return bool True, if the filter is enforced.
     */
    public function isEnforced() : bool
    {
        return strcontains($this->flags_str, 'f');
    }

    /**
     * Check if this RegEx does not apply a filter to the name.
     * @return bool True, if RegEx is not a filter.
     */
    public function isNofilter() : bool
    {
        return strcontains($this->flags_str, 'n');
    }

    /**
     * Gets the names of the capture groups of this regex.
     * @return array
     */
    public function getCapturegroupNames() : array
    {
        return $this->capture_names;
    }

    /**
     * Gets the properties based on the name and the capture group names.
     * @param $name string The name from which the properties should be parsed
     * @return array A array of PartProperty Elements.
     */
    public function getProperties(string $name) : array
    {
        $tmp = array();

        if (empty($this->getRegex())) {
            return $tmp;
        }

        mb_eregi($this->getRegex(true), $name, $tmp);

        $properties = array();

        foreach ($this->capture_names as $n => $nValue) {
            if (empty($tmp[$n + 1])) { //Ignore empty values
                continue;
            }
            $properties[] = new PartProperty('', $this->capture_names[$n], $tmp[$n + 1]);
        }

        return $properties;
    }

    /**
     * Checks if a name is valid.
     * @param $name string The name which should be checked.
     * @return bool True if the name is valid, or the nofilter flag is set.
     */
    public function checkName(string $name) : bool
    {
        if ($this->isNofilter() || empty($this->getRegex())) { //When we dont filter, every name is ok.
            return true;
        }

        return mb_eregi($this->getRegex(true), $name) !== false;
    }

    /**
     * Static functions
     */

    /**
     * Check if the string is valid.
     * @param $partname_regex string The string which should be checked.
     * @return bool True, if the string is valid.
     */
    public static function isValid(string $partname_regex) : bool
    {
        return mb_ereg_match(self::getPattern(false, true), $partname_regex);
    }

    public static function getPattern(bool $for_html_pattern = false, bool $for_mb = false) : string
    {
        if ($for_html_pattern) {
            $pattern = regexStripSlashes(regexAllowUmlauts(self::$pattern));
            return "($pattern)|(@@)";
        } elseif ($for_mb) {
            return regexStripSlashes(self::$pattern);
        } else {
            return regexAllowUmlauts(self::$pattern);
        }
    }
}
