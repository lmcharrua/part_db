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

class ConfigPermission extends BasePermission
{
    const READ_CONFIG        = 'read_config';
    const EDIT_CONFIG       = 'edit_config';
    const CHANGE_ADMIN_PW   = 'change_admin_pw';
    const SERVER_INFO       = 'server_info';

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
            $operations[static::READ_CONFIG] = static::buildOperationArray(0, static::READ_CONFIG, _('Konfiguration anzeigen'));
            $operations[static::EDIT_CONFIG] = static::buildOperationArray(2, static::EDIT_CONFIG, _('Konfiguration bearbeiten'));
            //Dont use 4, this was for CHANGE_ADMIN_PW permission, that is not needed any more.
            //$operations[] = static::buildOperationArray(4, static::CHANGE_ADMIN_PW, _("Administratorpassword ändern"));
            $operations[static::SERVER_INFO] = static::buildOperationArray(6, static::SERVER_INFO, _('Serverinformationen anzeigen'));

            static::$operation_cache = $operations;
        }
        return static::$operation_cache;
    }

    protected function modifyValueBeforeSetting(string $operation, int $new_value, int $data) : int
    {

        //Set read permission, too, when you get edit permissions.
        if ($operation == static::EDIT_CONFIG && $new_value == static::ALLOW) {
            return self::writeBitPair($data, static::opToBitN(static::READ_CONFIG), static::ALLOW);
        }


        return $data;
    }
}
