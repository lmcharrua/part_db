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

namespace PartDB\Interfaces;

use PartDB\Permissions\PermissionManager;

interface IHasPermissions
{
    /**
     * Gets the integer value of a permission of the current object.
     * @param $permsission_name string The name of the permission that should be get. (Without "perms_")
     * @return int The int value of the requested permission.
     */
    public function getPermissionRaw(string $permsission_name) : int;

    /**
     * Sets the integer value of a permission of the current object.
     * @param $permsission_name string The name of the permission that should be get. (Without "perms_")
     * @param $value int The value the permission should be set to.
     */
    public function setPermissionRaw(string $permission_name, int $value);

    /**
     * Returns the PermissionManager of the (permission) parent of the current object.
     * @return PermissionManager|null The PermissionManager of the parent, or null if the current object has no parent.
     */
    public function &getParentPermissionManager();

    /**
     * Returns the PermissionManager of the current object.
     * @return PermissionManager The Permission manager of the current object.
     */
    public function &getPermissionManager() : PermissionManager;
}
