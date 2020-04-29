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

include_once __DIR__ . '/start_session.php';

use PartDB\Database;
use PartDB\HTML;
use PartDB\Log;
use PartDB\Permissions\ConfigPermission;
use PartDB\Permissions\PermissionManager;
use PartDB\User;

$messages = array();
$fatal_error = false; // if a fatal error occurs, only the $messages will be printed, but not the site content

/********************************************************************************
 *
 *   Some special functions for this site
 *
 *********************************************************************************/

function build_theme_loop()
{
    global $config;
    $loop = array();
    $directories = findAllDirectories(BASE.'/templates/');

    foreach ($directories as $directory) {
        $name = str_ireplace(BASE.'/templates/', '', $directory);
        if ($name != 'custom_css' && $name != 'fonts') {
            $loop[] = array('value' => $name, 'text' => $name, 'selected' => $name == $config['html']['theme']);
        }
    }

    return $loop;
}

/********************************************************************************
 *
 *   Evaluate $_POST
 *
 *********************************************************************************/

// section "system settings"
$theme                      = isset($_POST['theme'])             ? (string)$_POST['theme']            : $config['html']['theme'];
$custom_css                 = isset($_POST['custom_css'])        ? (string)$_POST['custom_css']       : $config['html']['custom_css'];
$timezone                   = isset($_POST['timezone'])          ? (string)$_POST['timezone']         : $config['timezone'];
$language                   = isset($_POST['language'])          ? (string)$_POST['language']         : $config['language'];
$disable_updatelist         = isset($_POST['disable_updatelist']);
$disable_search_warning     = isset($_POST['disable_search_warning']);
$disable_help               = isset($_POST['disable_help']);
$disable_config             = isset($_POST['disable_config']);
$enable_debug_link          = isset($_POST['enable_debug_link']);
$disable_devices            = isset($_POST['disable_devices']);
$disable_footprints         = isset($_POST['disable_footprints']);
$disable_manufacturers      = isset($_POST['disable_manufacturers']);
$disable_labels             = isset($_POST['disable_labels']);
$disable_calculator         = isset($_POST['disable_calculator']);
$disable_iclogos            = isset($_POST['disable_iclogos']);
$disable_auto_datasheets    = isset($_POST['disable_auto_datasheets']);
$disable_tools_footprints   = isset($_POST['disable_tools_footprints']);
$disable_suppliers          = isset($_POST['disable_suppliers']);
$tools_footprints_autoload  = isset($_POST['tools_footprints_autoload']);
$enable_developer_mode      = isset($_POST['enable_developer_mode']);
$use_modal_popup            = isset($_POST['use_modal_popup']);
$page_title                 = isset($_POST['page_title'])        ? (string)$_POST['page_title']       : $config['page_title'];
$startup_banner             = isset($_POST['startup_banner'])    ? (string)$_POST['startup_banner']   : $config['startup']['custom_banner'];
$downloads_enable           = isset($_POST['downloads_enable']);

// section "appearance"
$use_old_datasheet_icons    = isset($_POST['use_old_datasheet_icons']);
$short_description          = isset($_POST['short_description']);
$others_panel_collapse      = isset($_POST['others_panel_collapse']);
$others_panel_postion       = isset($_POST['others_panel_position']) ? (string)$_POST['others_panel_position'] : 'top';

// section "3d footprints"
$foot3d_active              = isset($_POST['foot3d_active']);
$foot3d_show_info           = isset($_POST['foot3d_show_info']);

//section "part properites"
$properties_active          = isset($_POST['properties_active']);

//Edit parts
$created_redirect           = isset($_POST['created_redirect']);
$saved_redirect             = isset($_POST['saved_redirect']);

//Table settings
$table_autosort             = isset($_POST['table_autosort']);
$default_subcat             = isset($_POST['default_subcat']);
$default_limit              = isset($_POST['default_limit']) ? (int) $_POST['default_limit']  : 50;
$show_full_paths            = isset($_POST['show_full_paths']);
$instock_warning_full_row   = isset($_POST['instock_warning_full_row']);

//Search settings
$livesearch_active          = isset($_POST['livesearch_active']);
$search_highlighting        = isset($_POST['search_highlighting']);

//Attachement settings
$attachements_structure     = isset($_POST['attachements_structure']);
$attachements_download      = isset($_POST['attachements_download']);
$attachements_show_name     = isset($_POST['attachements_show_name']);

//Detailinfo settings
$info_hide_actions              = isset($_POST['info_hide_actions']);
$info_hide_empty_orderdetails   = isset($_POST['info_hide_empty_orderdetails']);
$info_hide_empty_attachements   = isset($_POST['info_hide_empty_attachements']);

//User settings
$use_gravatar                   = isset($_POST['gravatar_enable']);
$login_redirect                 = isset($_POST['login_redirect']);
$max_sessiontime                = $_POST['max_sessiontime'] ?? -1;

//Logging system settings
$min_log_level                  = isset($_POST['min_log_level']) ? (int)$_POST['min_log_level'] : 7;

$action = 'default';
if (isset($_POST['apply'])) {
    $action = 'apply';
}

/********************************************************************************
 *
 *   Initialize Objects
 *
 *********************************************************************************/

$html = new HTML($config['html']['theme'], /*$config['html']['custom_css']*/ $custom_css, _('Konfiguration'));

try {
    $database           = new Database();
    $log                = new Log($database);
    //$system             = new System($database, $log);
    $current_user       = User::getLoggedInUser($database, $log);

    if (!$current_user->canDo(PermissionManager::CONFIG, ConfigPermission::READ_CONFIG)
    && !$current_user->canDo(PermissionManager::CONFIG, ConfigPermission::SERVER_INFO)) {
        $current_user->tryDo(PermissionManager::CONFIG, ConfigPermission::READ_CONFIG);
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
        case 'apply':
            $config_old = $config;

            $config['html']['theme']                    = $theme;
            $config['html']['custom_css']               = $custom_css;
            $config['timezone']                         = $timezone;
            $config['language']                         = $language;
            $config['startup']['disable_update_list']   = $disable_updatelist;
            $config['startup']['disable_search_warning'] = $disable_search_warning;
            $config['menu']['disable_help']             = $disable_help;
            $config['menu']['disable_labels']           = $disable_labels;
            $config['menu']['disable_calculator']       = $disable_calculator;
            $config['menu']['disable_iclogos']          = $disable_iclogos;
            $config['menu']['enable_debug']             = $enable_debug_link;
            $config['devices']['disable']               = $disable_devices;
            $config['footprints']['disable']            = $disable_footprints;
            $config['manufacturers']['disable']         = $disable_manufacturers;
            $config['auto_datasheets']['disable']       = $disable_auto_datasheets;
            $config['suppliers']['disable']             = $disable_suppliers;
            $config['menu']['disable_footprints']       = $disable_tools_footprints;
            $config['tools']['footprints']['autoload']  = $tools_footprints_autoload;
            $config['developer_mode']                   = ($enable_developer_mode && file_exists(BASE.'/development'));
            $config['allow_server_downloads']           = $downloads_enable;

            $config['appearance']['use_old_datasheet_icons'] = $use_old_datasheet_icons;
            $config['appearance']['short_description'] = $short_description;
            $config['other_panel']['collapsed']        = $others_panel_collapse;
            $config['other_panel']['position']         = $others_panel_postion;

            $config['foot3d']['active']                 = $foot3d_active;
            $config['foot3d']['show_info']              = $foot3d_show_info;

            $config['properties']['active']             = $properties_active;

            $config['search']['livesearch']             = $livesearch_active;
            $config['search']['highlighting']           = $search_highlighting;

            $config['edit_parts']['created_go_to_info']      = $created_redirect;    //Jump to info page of a part, if a new part was created
            $config['edit_parts']['saved_go_to_info']        = $saved_redirect;

            $config['table']['autosort']                = $table_autosort;
            $config['table']['default_show_subcategories'] = $default_subcat;
            $config['table']['default_limit']           = $default_limit;
            $config['table']['full_paths']         = $show_full_paths;
            $config['table']['instock_warning_full_row_color'] = $instock_warning_full_row;

            $config['attachements']['folder_structure'] = $attachements_structure;
            $config['attachements']['download_default'] = $attachements_download;
            $config['attachements']['show_name']        = $attachements_show_name;

            $config['part_info']['hide_actions']                = $info_hide_actions;
            $config['part_info']['hide_empty_attachements']     = $info_hide_empty_attachements;
            $config['part_info']['hide_empty_orderdetails']     = $info_hide_empty_orderdetails;

            $config['user']['avatars']['use_gravatar']          = $use_gravatar;
            $config['user']['redirect_to_login']                = $login_redirect;

            $config['logging_system']['min_level']              = $min_log_level;


            if ($max_sessiontime >= 0) {
                $config['user']['gc_maxlifetime'] = $max_sessiontime;
            }

            if (! $config['is_online_demo']) {
                // settings which should not be able to change in the online demo
                $config['menu']['disable_config']       = $disable_config;
                $config['partdb_title']                   = $page_title;
                $config['startup']['custom_banner']     = $startup_banner;
            }

            try {
                $current_user->tryDo(PermissionManager::CONFIG, ConfigPermission::EDIT_CONFIG);
                saveConfig();
                \PartDB\LogSystem\ConfigChangedEntry::add($database, $current_user, $log);
                $html->setVariable('refresh_navigation_frame', true, 'boolean');
                //header('Location: system_config.php'); // Reload the page that we can see if the new settings are stored successfully --> does not work correctly?!
            } catch (Exception $e) {
                $config = $config_old; // reload the old config
                $messages[] = array('text' => _('Die neuen Werte konnten nicht gespeichert werden!'), 'strong' => true, 'color' => 'red');
                $messages[] = array('text' => _('Fehlermeldung: '.nl2br($e->getMessage())), 'color' => 'red');
            }
            break;
    }
}

/********************************************************************************
 *
 *   Set all HTML variables
 *
 *********************************************************************************/

try {
    // theme
    $html->setVariable('theme_loop', build_theme_loop());
    $html->setVariable('custom_css_loop', build_custom_css_loop());

    // locale settings

    //Convert timezonelist, to a format, we can use
    $timezones_raw = DateTimeZone::listIdentifiers();
    $timezones = array();
    foreach ($timezones_raw as $timezone) {
        $timezones[$timezone] = $timezone;
    }
    $html->setVariable('timezone_loop', arrayToTemplateLoop($timezones, $config['timezone']));
    $html->setVariable('language_loop', arrayToTemplateLoop($config['languages'], $config['language']));

    // checkboxes
    $html->setVariable('disable_updatelist', $config['startup']['disable_update_list'], 'boolean');
    $html->setVariable('disable_search_warning', $config['startup']['disable_search_warning'], 'boolean');
    $html->setVariable('disable_help', $config['menu']['disable_help'], 'boolean');
    $html->setVariable('disable_config', $config['menu']['disable_config'], 'boolean');
    $html->setVariable('enable_debug_link', $config['menu']['enable_debug'], 'boolean');
    $html->setVariable('disable_devices', $config['devices']['disable'], 'boolean');
    $html->setVariable('disable_footprints', $config['footprints']['disable'], 'boolean');
    $html->setVariable('disable_manufacturers', $config['manufacturers']['disable'], 'boolean');
    $html->setVariable('disable_labels', $config['menu']['disable_labels'], 'boolean');
    $html->setVariable('disable_calculator', $config['menu']['disable_calculator'], 'boolean');
    $html->setVariable('disable_iclogos', $config['menu']['disable_iclogos'], 'boolean');
    $html->setVariable('disable_auto_datasheets', $config['auto_datasheets']['disable'], 'boolean');
    $html->setVariable('disable_tools_footprints', $config['menu']['disable_footprints'], 'boolean');
    $html->setVariable('tools_footprints_autoload', $config['tools']['footprints']['autoload'], 'boolean');
    $html->setVariable('developer_mode_available', file_exists(BASE . '/development'), 'boolean');
    $html->setVariable('enable_developer_mode', $config['developer_mode'], 'boolean');
    $html->setVariable('use_old_datasheet_icons', $config['appearance']['use_old_datasheet_icons'], 'boolean');

    // site properties
    $html->setVariable('page_title', $config['partdb_title'], 'string');
    $html->setVariable('startup_banner', $config['startup']['custom_banner'], 'string');

    // server
    $html->setVariable('php_version', PHP_VERSION, 'string');
    $html->setVariable('htaccess_works', getenv('htaccessWorking') == 'true', 'boolean');
    $html->setVariable('is_online_demo', $config['is_online_demo'], 'boolean');
    $html->setVariable('using_https', isUsingHTTPS(), 'boolean');
    $html->setVariable('max_input_vars', ini_get('max_input_vars'), 'string');
    $html->setVariable('max_upload_filesize', ini_get('upload_max_filesize'), 'string');
    $html->setVariable('session_cookie_lifetime', ini_get('session.cookie_lifetime') > 0 ? ini_get('session.cookie_lifetime') . 's' : _('Bis zum Schließen des Browsers'), 'string');
    $html->setVariable('session_gc_maxlifetime', ini_get('session.gc_maxlifetime'), 'string');
    $html->setVariable('current_server_datetime', formatTimestamp(time()));

    //Part properties
    $html->setVariable('properties_active', $config['properties']['active'], 'boolean');

    // 3d Footprints
    $html->setVariable('foot3d_active', $config['foot3d']['active'], 'boolean');
    $html->setVariable('foot3d_show_info', $config['foot3d']['show_info'], 'boolean');

    // Edit Dialog settings
    $html->setVariable('created_redirect', $config['edit_parts']['created_go_to_info'], 'boolean');
    $html->setVariable('saved_redirect', $config['edit_parts']['saved_go_to_info'], 'boolean');

    // Appearance
    $html->setVariable('short_description', $config['appearance']['short_description'], 'boolean');
    $html->setVariable('others_panel_collapse', $config['other_panel']['collapsed'], 'boolean');
    $html->setVariable('others_panel_position', $config['other_panel']['position'], 'string');

    //Table
    $html->setVariable('table_autosort', $config['table']['autosort'], 'boolean');
    $html->setVariable('default_subcat', $config['table']['default_show_subcategories'], 'boolean');
    $html->setVariable('default_limit', $config['table']['default_limit'], 'int');
    $html->setVariable('show_full_paths', $config['table']['full_paths'], 'boolean');
    $html->setVariable('instock_warning_full_row', $config['table']['instock_warning_full_row_color'], 'boolean');


    //Attachements
    $html->setVariable('attachements_structure', $config['attachements']['folder_structure'], 'boolean');
    $html->setVariable('attachements_download', $config['attachements']['download_default'], 'boolean');
    $html->setVariable('attachements_show_name', $config['attachements']['show_name'], 'boolean');
    $html->setVariable('disable_suppliers', $config['suppliers']['disable'], 'boolean');

    //Detail infos
    $html->setVariable('info_hide_actions', $config['part_info']['hide_actions'], 'boolean');
    $html->setVariable('info_hide_empty_orderdetails', $config['part_info']['hide_empty_orderdetails'], 'boolean');
    $html->setVariable('info_hide_empty_attachements', $config['part_info']['hide_empty_attachements'], 'boolean');

    //Misc
    $html->setVariable('downloads_enable', $config['allow_server_downloads'], 'boolean');

    //Users
    $html->setVariable('gravatar_enable', $config['user']['avatars']['use_gravatar'], 'boolean');
    $html->setVariable('login_redirect', $config['user']['redirect_to_login'], 'boolean');
    $html->setVariable('gc_lifetime', $config['user']['gc_maxlifetime'], 'int');

    //Logging system
    $html->setVariable('min_log_level', $config['logging_system']['min_level'], 'int');

    //Search
    $html->setVariable('livesearch_active', $config['search']['livesearch']);
    $html->setVariable('search_highlighting', $config['search']['highlighting']);

    // check if the server supports the selected language and print a warning if not
    if (!ownSetlocale(LC_ALL, $config['language'])) {
        $messages[] = array('text' => _('Achtung:'), 'strong' => true, 'color' => 'red');
        $messages[] = array('text' => sprintf(_('Die gewählte Sprache "%s" wird vom Server nicht unterstützt!'), $config['language']), 'color' => 'red');
        $messages[] = array('text' => _('Bitte installieren Sie diese Sprache oder wählen Sie eine andere.'), 'color' => 'red');
    }

    //Permission variables
    $html->setVariable('can_infos', $current_user->canDo(PermissionManager::CONFIG, ConfigPermission::SERVER_INFO));
    $html->setVariable('can_edit', $current_user->canDo(PermissionManager::CONFIG, ConfigPermission::EDIT_CONFIG));
    $html->setVariable('can_read', $current_user->canDo(PermissionManager::CONFIG, ConfigPermission::READ_CONFIG));
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

$html->printHeader($messages);

if (! $fatal_error) {
    $html->printTemplate('system_config');
}

$html->printFooter();
