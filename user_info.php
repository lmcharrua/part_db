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

include_once __DIR__ . '/start_session.php';

use PartDB\Database;
use PartDB\HTML;
use PartDB\Log;
use PartDB\User;

$messages = array();
$fatal_error = false; // if a fatal error occurs, only the $messages will be printed, but not the site content

/********************************************************************************
 *
 *   Evaluate $_REQUEST
 *
 *********************************************************************************/

$user_id = isset($_REQUEST['uid']) ? (int)$_REQUEST['uid'] : 0;

/********************************************************************************
 *
 *   Initialize Objects
 *
 *********************************************************************************/

$html = new HTML($config['html']['theme'], $user_config['theme'], _('Benutzerinformationen'));

try {
    $database           = new Database();
    $log                = new Log($database);
    $current_user       = User::getLoggedInUser($database, $log);

    //Currently only the view of your own user is implemented.
    if ($user_id == 0) {
        $selected_user      = $current_user;
    } else {
        $selected_user = User::getInstance($database, $current_user, $log, $user_id);
        //Check if the current user, is allowed to view other profiles.
        $current_user->tryDo(\PartDB\Permissions\PermissionManager::USERS, \PartDB\Permissions\UserPermission::READ);
    }
} catch (Exception $e) {
    $messages[] = array('text' => nl2br($e->getMessage()), 'strong' => true, 'color' => 'red');
    $fatal_error = true;
}

/********************************************************************************
 *
 *   Execute Actions
 *
 *********************************************************************************/


switch ($action) {
    /*
    case "change_pw":
        if ($pw_1 == "" || $pw_2 == "") {
            $messages[] = array('text' => _("Das neue Password darf nicht leer sein!"), 'strong' => true, 'color' => 'red');
            break;
        }
        if ($pw_1 !== $pw_2) {
            $messages[] = array('text' => _("Das neue Password und die Bestätigung müssen übereinstimmen!"), 'strong' => true, 'color' => 'red');
            break;
        }
        if (!$current_user->isPasswordValid($pw_old)) {
            $messages[] = array('text' => _("Das eingegebene alte Password war falsch!"), 'strong' => true, 'color' => 'red');
            break;
        }
        //If all checks were ok, change the password!
        $current_user->setPassword($pw_1);
        $messages[] = array('text' => _("Das Passwort wurde erfolgreich geändert!"), 'strong' => true, 'color' => 'green');

        break;
    */
}

/********************************************************************************
 *
 *   Set the rest of the HTML variables
 *
 *********************************************************************************/

if (! $fatal_error) {
    try {
        $html->setVariable('username', $selected_user->getName(), 'string');
        $html->setVariable('firstname', $selected_user->getFirstName(), 'string');
        $html->setVariable('lastname', $selected_user->getLastName(), 'string');
        $html->setVariable('email', $selected_user->getEmail(), 'string');
        $html->setVariable('department', $selected_user->getDepartment(), 'string');
        $html->setVariable('group', $selected_user->getGroup()->getFullPath(), 'string');
        $html->setVariable('avatar_url', $selected_user->getAvatar(), 'string');

        $html->setVariable('perm_loop', $selected_user->getPermissionManager()->generatePermissionsLoop(true, true));
    } catch (Exception $e) {
        $messages[] = array('text' => nl2br($e->getMessage()), 'strong' => true, 'color' => 'red');
        $fatal_error = true;
    }
}

/********************************************************************************
 *
 *   Generate HTML Output
 *
 *********************************************************************************/


//If a ajax version is requested, say this the template engine.
if (isset($_REQUEST['ajax'])) {
    $html->setVariable('ajax_request', true);
}


$reload_link = $fatal_error ? 'user_info.php' : '';  // an empty string means that the...
$html->printHeader($messages, $reload_link);                           // ...reload-button won't be visible


if (! $fatal_error) {
    $html->printTemplate('main');
    $html->printTemplate('permissions');
}


$html->printFooter();
