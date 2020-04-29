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

namespace PartDB\Permissions;

class ToolsPermission extends BasePermission
{
    const IMPORT        = 'import';
    const LABELS        = 'labels';
    const CALCULATOR    = 'calculator';
    const FOOTPRINTS    = 'footprints';
    const IC_LOGOS      = 'ic_logos';
    const STATISTICS    = 'statistics';

    protected static $operation_cache = null;

    /**
     * Returns an array of all available operations for this Permission.
     * @return array All availabel operations.
     */
    public static function listOperations() : array
    {
        if (!isset(static::$operation_cache)) {
            /**
             * Dont change these definitions, because it would break compatibility with older database.
             * However you can add other definitions, the return value can get high as 30, as the DB uses a 32bit integer.
             */
            $operations = array();
            $operations[static::IMPORT] = static::buildOperationArray(0, static::IMPORT, _('Import'));
            $operations[static::LABELS] = static::buildOperationArray(2, static::LABELS, _('Labels'));
            $operations[static::CALCULATOR] = static::buildOperationArray(4, static::CALCULATOR, _('Widerstandsrechner'));
            $operations[static::FOOTPRINTS] = static::buildOperationArray(6, static::FOOTPRINTS, _('Footprints'));
            $operations[static::IC_LOGOS] = static::buildOperationArray(8, static::IC_LOGOS, _('IC-Logos'));
            $operations[static::STATISTICS] = static::buildOperationArray(10, static::STATISTICS, _('Statistik'));

            static::$operation_cache = $operations;
        }

        return static::$operation_cache;
    }
}
