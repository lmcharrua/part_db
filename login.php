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

$user_name = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';
$logout   = isset($_REQUEST['logout']);

$redirect_url = $_REQUEST['redirect'] ?? '';

$messages = array();
$fatal_error = false;

$action = 'default';
if (!User::isLoggedIn() && $user_name != '') {
    $action = 'login';
}

if ($logout == true && User::isLoggedIn() && $user_name == '') {
    $action = 'logout';
}

if (User::isLoggedIn() && $logout == false) {
    $action = 'redirect';
}

$html = new HTML($config['html']['theme'], $user_config['theme'], $config['partdb_title'] . ' - ' . _('Login'));

try {
    $database           = new Database();
    $log                = new Log($database);
    $user               = User::getLoggedInUser($database, $log);
} catch (Exception $e) {
    $messages[] = array('text' => nl2br($e->getMessage()), 'strong' => true, 'color' => 'red');
    $fatal_error = true;
}


if (!$fatal_error) {
    switch ($action) {
        case 'logout':
            User::logout();
            $html->setVariable('refresh_navigation_frame', true, 'boolean');
            $html->setVariable('loggedout', true);
            break;
        case 'login':
            try {
                $user               = User::getUserByName($database, $log, $user_name);
                $pw_valid           = User::login($user, $password);
            } catch (Exception $ex) {
                $pw_valid = false;
            }

            $html->setVariable('pw_valid', $pw_valid, 'boolean');
            if (User::isLoggedIn()) {
                $html->setVariable('refresh_navigation_frame', true, 'boolean');
            }
            break;
        case 'redirect':
            if ($redirect_url != '') {
                //We need to remove Part-DB/ part, because PHP_URI_REQUEST contains it...
                $html->redirect(str_replace(BASE_RELATIVE . '/', '', $redirect_url));
            } elseif (User::getLoggedInUser()->getNeedPasswordChange()) { //Redirect to user settings, when user needs to change password.
                $html->redirect('user_settings.php');
            } else { //Else redirect to start page.
                $html->redirect('startup.php');
            }
            break;
        case 'default':
            break;
    }
}

//If a ajax version is requested, say this the template engine.
/*if (isset($_REQUEST["ajax"])) {
    $html->setVariable("ajax_request", true);
}*/

if (User::isLoggedIn()) {
    $user = User::getLoggedInUser($database, $log);
    $html->setVariable('loggedin', true, 'boolean');
}

$html->setVariable('username', $user_name, 'string');

//$html->set_variable("refresh_navigation_frame", true, "boolean");


$reload_link = $fatal_error ? 'login.php'  : ''; // an empty string means that the...
$html->printHeader($messages, $reload_link);                                   // ...reload-button won't be visible


if (! $fatal_error) {
    $html->printTemplate('login');
}

$html->printFooter();
