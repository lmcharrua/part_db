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

/*
 * Please note:
 *  The files "edit_categories.php", "edit_footprints.php", "edit_manufacturers.php",
 *  "edit_suppliers.php", "edit_devices.php", "edit_storelocations.php" and "edit_filetypes.php"
 *  are quite similar.
 *  If you make changes in one of them, please check if you should change the other files too.
 */

include_once __DIR__ . '/start_session.php';

use PartDB\Database;
use PartDB\HTML;
use PartDB\Log;
use PartDB\Storelocation;
use PartDB\Permissions\PermissionManager;
use PartDB\Permissions\StructuralPermission;
use PartDB\User;

$messages = array();
$fatal_error = false; // if a fatal error occurs, only the $messages will be printed, but not the site content

/********************************************************************************
 *
 *   Evaluate $_REQUEST
 *
 *   Notes:
 *       - "$selected_id == 0" means that we will show the form for creating a new storelocation
 *       - the $new_* variables contains the new values after editing an existing
 *           or creating a new storelocation
 *
 *********************************************************************************/

$selected_id        = isset($_REQUEST['selected_id'])   ? (int)$_REQUEST['selected_id'] : 0;
$new_name           = isset($_POST['name'])          ? (string)$_POST['name']         : '';
$new_parent_id      = isset($_POST['parent_id'])     ? (int)$_POST['parent_id']   : 0;
$new_is_full        = isset($_POST['is_full']);
$create_series      = isset($_POST['series']);
$series_from        = $_POST['series_from'] ?? 1;
$series_to          = isset($_POST['series_to'])     ? $_POST['series_to']   : 1;
$add_more           = isset($_POST['add_more']);
$new_comment        = isset($_POST['comment'])       ? (string)$_POST['comment']      : '';

$action = 'default';
if (isset($_POST['add'])) {
    $action = 'add';
}
if (isset($_POST['delete'])) {
    $action = 'delete';
}
if (isset($_POST['delete_confirmed'])) {
    $action = 'delete_confirmed';
}
if (isset($_POST['apply'])) {
    $action = 'apply';
}

/********************************************************************************
 *
 *   Initialize Objects
 *
 *********************************************************************************/

$html = new HTML($config['html']['theme'], $user_config['theme'], _('Lagerorte'));

try {
    $database           = new Database();
    $log                = new Log($database);
    $current_user       = User::getLoggedInUser($database, $log);
    $root_storelocation = Storelocation::getInstance($database, $current_user, $log, 0);

    $current_user->tryDo(PermissionManager::STORELOCATIONS, StructuralPermission::READ);

    if ($selected_id > 0) {
        $selected_storelocation = Storelocation::getInstance($database, $current_user, $log, $selected_id);
    } else {
        $selected_storelocation = null;
    }
} catch (Exception $e) {
    $messages[] = array('text' => nl2br($e->getMessage()), 'strong' => true, 'color' => 'red');
    $fatal_error = true;
}

/********************************************************************************
 *
 *   Execute actions
 *
 *********************************************************************************/

if (! $fatal_error) {
    switch ($action) {
        case 'add':
            try {
                if ($create_series) {
                    $width  = mb_strlen((string) $series_to); // determine the width of second argument
                    $format = '%0' . $width . 's';

                    foreach (range($series_from, $series_to) as $index) {
                        $new_storelocation_name = $new_name . sprintf($format, $index);
                        $new_storelocation = Storelocation::add(
                            $database,
                            $current_user,
                            $log,
                            $new_storelocation_name,
                            $new_parent_id,
                            $new_is_full,
                            $new_comment
                        );
                    }
                } else {
                    $new_storelocation = Storelocation::add(
                        $database,
                        $current_user,
                        $log,
                        $new_name,
                        $new_parent_id,
                        $new_is_full,
                        $new_comment
                    );
                }

                if (! $add_more) {
                    $selected_storelocation = $new_storelocation;
                    $selected_id = $selected_storelocation->getID();
                }
            } catch (Exception $e) {
                $messages[] = array('text' => _('Der neue Lagerort konnte nicht angelegt werden!'), 'strong' => true, 'color' => 'red');
                $messages[] = array('text' => _('Fehlermeldung: ').nl2br($e->getMessage()), 'color' => 'red');
            }
            break;

        case 'delete':
            if (is_object($selected_storelocation)) {
                try {
                    $parts = $selected_storelocation->getParts();
                    $count = count($parts);

                    if ($count > 0) {
                        $messages[] = array('text' => sprintf(_('Es gibt noch %d Bauteile an diesem Lagerort, '.
                            'daher kann der Lagerort nicht gelöscht werden.'), $count), 'strong' => true, 'color' => 'red');
                    } else {
                        $messages[] = array('text' => sprintf(_('Soll der Lagerort "%s'.
                            '" wirklich unwiederruflich gelöscht werden?'), $selected_storelocation->getFullPath()), 'strong' => true, 'color' => 'red');
                        $messages[] = array('text' => _('<br>Hinweise:'), 'strong' => true);
                        $messages[] = array('text' => _('&nbsp;&nbsp;&bull; Es gibt keine Bauteile an diesem Lagerort.'));
                        $messages[] = array('text' => _('&nbsp;&nbsp;&bull; Beinhaltet dieser Lagerort noch Unterlagerorte, dann werden diese eine Ebene nach oben verschoben.'));
                        $messages[] = array('html' => '<input type="hidden" name="selected_id" value="'.$selected_storelocation->getID().'">');
                        $messages[] = array('html' => '<input type="submit" class="btn btn-secondary" name="" value="'._('Nein, nicht löschen').'">', 'no_linebreak' => true);
                        $messages[] = array('html' => '<input type="submit" class="btn btn-danger" name="delete_confirmed" value="'._('Ja, Lagerort löschen').'">');
                    }
                } catch (Exception $e) {
                    $messages[] = array('text' => _('Es trat ein Fehler auf!'), 'strong' => true, 'color' => 'red');
                    $messages[] = array('text' => _('Fehlermeldung: ').nl2br($e->getMessage()), 'color' => 'red');
                }
            } else {
                $messages[] = array('text' => _('Es ist kein Lagerort markiert oder es trat ein Fehler auf!'),
                    'strong' => true, 'color' => 'red');
            }
            break;

        case 'delete_confirmed':
            if (is_object($selected_storelocation)) {
                try {
                    $selected_storelocation->delete();
                    $selected_storelocation = null;
                } catch (Exception $e) {
                    $messages[] = array('text' => _('Der Lagerort konnte nicht gelöscht werden!'), 'strong' => true, 'color' => 'red');
                    $messages[] = array('text' => _('Fehlermeldung: ').nl2br($e->getMessage()), 'color' => 'red');
                }
            } else {
                $messages[] = array('text' => _('Es ist kein Lagerort markiert oder es trat ein Fehler auf!'),
                    'strong' => true, 'color' => 'red');
            }
            break;

        case 'apply':
            if (is_object($selected_storelocation)) {
                try {
                    $selected_storelocation->setAttributes(array(  'name'       => $new_name,
                        'parent_id'  => $new_parent_id,
                        'is_full'    => $new_is_full,
                        'comment' => $new_comment));
                } catch (Exception $e) {
                    $messages[] = array('text' => _('Die neuen Werte konnten nicht gespeichert werden!'), 'strong' => true, 'color' => 'red');
                    $messages[] = array('text' => _('Fehlermeldung: ').nl2br($e->getMessage()), 'color' => 'red');
                }
            } else {
                $messages[] = array('text' => _('Es ist kein Lagerort markiert oder es trat ein Fehler auf!'),
                    'strong' => true, 'color' => 'red');
            }
            break;
    }
}

/********************************************************************************
 *
 *   Set the rest of the HTML variables
 *
 *********************************************************************************/

$html->setVariable('add_more', $add_more, 'boolean');

if (! $fatal_error) {
    try {
        if (is_object($selected_storelocation)) {
            $parent_id = $selected_storelocation->getParentID();
            $html->setVariable('id', $selected_storelocation->getID(), 'integer');
            $name = $selected_storelocation->getName();
            $is_full = $selected_storelocation->getIsFull();
            $comment = $selected_storelocation->getComment(false);
            $html->setVariable('datetime_added', $selected_storelocation->getDatetimeAdded(true));
            $html->setVariable('last_modified', $selected_storelocation->getLastModified(true));
            $last_modified_user = $selected_storelocation->getLastModifiedUser();
            $creation_user = $selected_storelocation->getCreationUser();
            if ($last_modified_user != null) {
                $html->setVariable('last_modified_user', $last_modified_user->getFullName(true), 'string');
                $html->setVariable('last_modified_user_id', $last_modified_user->getID(), 'int');
            }
            if ($creation_user != null) {
                $html->setVariable('creation_user', $creation_user->getFullName(true), 'string');
                $html->setVariable('creation_user_id', $creation_user->getID(), 'int');
            }
        } elseif ($action == 'add') {
            $parent_id = $new_parent_id;
            $name = $new_name;
            $is_full = $new_is_full;
            $comment = $new_comment;
        } else {
            $parent_id = 0;
            $name = '';
            $is_full = false;
            $comment = '';
        }

        $html->setVariable('name', $name, 'string');
        $html->setVariable('is_full', $is_full, 'boolean');
        $html->setVariable('comment', $comment);

        $storelocation_list = $root_storelocation->buildHtmlTree($selected_id, true, false);
        $html->setVariable('storelocation_list', $storelocation_list, 'string');

        $parent_storelocation_list = $root_storelocation->buildHtmlTree($parent_id, true, true);
        $html->setVariable('parent_storelocation_list', $parent_storelocation_list, 'string');
    } catch (Exception $e) {
        $messages[] = array('text' => nl2br($e->getMessage()), 'strong' => true, 'color' => 'red');
        $fatal_error = true;
    }
}

try {
    $html->setVariable('can_delete', $current_user->canDo(PermissionManager::STORELOCATIONS, StructuralPermission::DELETE));
    $html->setVariable('can_edit', $current_user->canDo(PermissionManager::STORELOCATIONS, StructuralPermission::EDIT));
    $html->setVariable('can_create', $current_user->canDo(PermissionManager::STORELOCATIONS, StructuralPermission::CREATE));
    $html->setVariable('can_move', $current_user->canDo(PermissionManager::STORELOCATIONS, StructuralPermission::MOVE));
    $html->setVariable('can_read', $current_user->canDo(PermissionManager::STORELOCATIONS, StructuralPermission::READ));
    $html->setVariable('can_visit_user', $current_user->canDo(PermissionManager::USERS, \PartDB\Permissions\UserPermission::READ));
} catch (Exception $e) {
    $messages[] = array('text' => nl2br($e->getMessage()), 'strong' => true, 'color' => 'red');
    $fatal_error = true;
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

$reload_link = $fatal_error ? 'edit_storelocations.php' : '';   // an empty string means that the...
$html->printHeader($messages, $reload_link);                   // ...reload-button won't be visible

if (! $fatal_error) {
    $html->printTemplate('edit_storelocations');
}

$html->printFooter();
