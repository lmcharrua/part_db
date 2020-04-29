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

class SystemPermission extends BasePermission
{
    const USE_DEBUG  = 'use_debug';
    const SHOW_LOGS  = 'show_logs';
    const DELETE_LOGS = 'delete_logs';

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
            $operations[static::USE_DEBUG] = static::buildOperationArray(0, static::USE_DEBUG, _('Debugtools benutzen'));
            $operations[static::SHOW_LOGS] = static::buildOperationArray(2, static::SHOW_LOGS, _('Logs anzeigen'));
            $operations[static::DELETE_LOGS] = static::buildOperationArray(4, static::DELETE_LOGS, _('Logeinträge löschen'));

            static::$operation_cache = $operations;
        }

        return static::$operation_cache;
    }
}
