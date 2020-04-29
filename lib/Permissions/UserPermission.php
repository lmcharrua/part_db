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

use PartDB\User;

class UserPermission extends BasePermission
{
    const CREATE = 'create';
    const READ  = 'read';
    const EDIT_USERNAME = 'edit_username';
    const EDIT_INFOS  = 'edit_infos';
    const CHANGE_GROUP  = 'change_group';
    const DELETE = 'delete';
    const EDIT_PERMISSIONS = 'edit_permissions';
    const SET_PASSWORD   = 'set_password';
    const CHANGE_USER_SETTINGS = 'change_user_settings';

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
            $operations[static::READ] = static::buildOperationArray(0, static::READ, _('Anzeigen'));
            $operations[static::CREATE] = static::buildOperationArray(4, static::CREATE, _('Anlegen'));
            $operations[static::DELETE] = static::buildOperationArray(8, static::DELETE, _('Löschen'));
            $operations[static::EDIT_USERNAME] = static::buildOperationArray(2, static::EDIT_USERNAME, _('Nutzernamen ändern'));
            $operations[static::CHANGE_GROUP] = static::buildOperationArray(6, static::CHANGE_GROUP, _('Gruppe ändern'));
            $operations[static::EDIT_INFOS] = static::buildOperationArray(10, static::EDIT_INFOS, _('Informationen ändern'));
            $operations[static::EDIT_PERMISSIONS] = static::buildOperationArray(12, static::EDIT_PERMISSIONS, _('Berechtigungen ändern'));
            $operations[static::SET_PASSWORD] = static::buildOperationArray(14, static::SET_PASSWORD, _('Password setzen'));
            $operations[static::CHANGE_USER_SETTINGS] = static::buildOperationArray(16, static::CHANGE_USER_SETTINGS, _('Benutzereinstellungen ändern'));

            static::$operation_cache = $operations;
        }
        return static::$operation_cache;
    }

    protected function modifyValueBeforeSetting(string $operation, int $new_value, int $data) : int
    {
        //Set read permission, too, when you get edit permissions.
        if (($operation == static::EDIT_USERNAME
                || $operation == static::DELETE
                || $operation == static::CHANGE_GROUP
                || $operation == static::CREATE
                || $operation == static::EDIT_INFOS
                || $operation == static::EDIT_USERNAME
                || $operation == static::SET_PASSWORD
                || $operation == static::EDIT_PERMISSIONS)
            && $new_value == static::ALLOW) {
            return self::writeBitPair($data, static::opToBitN(static::READ), static::ALLOW);
        }

        return $data;
    }

    public function generateLoopRow(bool $read_only = false, bool $inherit = false) : array
    {
        if (!$read_only && $this->perm_holder instanceof User) {
            $perm_holder = $this->perm_holder;
            /** @var $perm_holder User */
            if ($perm_holder->isLoggedInUser()) {
                $read_only = true;
            }
        }
        return parent::generateLoopRow($read_only, $inherit);
    }
}
