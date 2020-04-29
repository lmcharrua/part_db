<?php

/**
 *
 * Part-DB Version 0.4+ "nextgen"
 * Copyright (C) 2016 - 2018 Jan Böhmer
 * https://github.com/jbtronics
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA
 *
 */

namespace PartDB\Permissions;

class LabelPermission extends BasePermission
{
    const CREATE_LABELS = 'create_labels';
    const EDIT_OPTIONS = 'edit_options';
    const DELETE_PROFILES = 'delete_profiles';
    const EDIT_PROFILES = 'edit_profiles';

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
            $operations[static::CREATE_LABELS] = static::buildOperationArray(0, static::CREATE_LABELS, _('Labels erzeugen'));
            $operations[static::EDIT_OPTIONS] = static::buildOperationArray(2, static::EDIT_OPTIONS, _('Labels anpassen'));
            $operations[static::DELETE_PROFILES] = static::buildOperationArray(4, static::DELETE_PROFILES, _('Profile löschen'));
            $operations[static::EDIT_PROFILES] = static::buildOperationArray(6, static::EDIT_PROFILES, _('Profile anlegen/bearbeiten'));

            static::$operation_cache = $operations;
        }
        return static::$operation_cache;
    }

    protected function modifyValueBeforeSetting(string $operation, int $new_value, int $data) : int
    {
        //Set read permission, too, when you get edit permissions.
        if (($operation == static::EDIT_OPTIONS
                || $operation == static::EDIT_PROFILES
                || $operation == static::DELETE_PROFILES
                || $operation == static::EDIT_OPTIONS)
            && $new_value == static::ALLOW) {
            return self::writeBitPair($data, static::opToBitN(static::CREATE_LABELS), static::ALLOW);
        }

        return $data;
    }
}
