<?php
/* Copyright (C) 2013-2018	Jean-François Ferry	<hello+jf@librethic.io>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *   	\file       place/room/list.php
 *		\ingroup    place
 *		\brief      This file is to manage building rooms.
 */

//if (! defined('NOREQUIREUSER'))  define('NOREQUIREUSER','1');
//if (! defined('NOREQUIREDB'))    define('NOREQUIREDB','1');
//if (! defined('NOREQUIRESOC'))   define('NOREQUIRESOC','1');
//if (! defined('NOREQUIRETRAN'))  define('NOREQUIRETRAN','1');
//if (! defined('NOCSRFCHECK'))    define('NOCSRFCHECK','1');			// Do not check anti CSRF attack test
//if (! defined('NOSTYLECHECK'))   define('NOSTYLECHECK','1');			// Do not check style html tag into posted data
//if (! defined('NOTOKENRENEWAL')) define('NOTOKENRENEWAL','1');		// Do not check anti POST attack test
//if (! defined('NOREQUIREMENU'))  define('NOREQUIREMENU','1');			// If there is no need to load and show top and left menu
//if (! defined('NOREQUIREHTML'))  define('NOREQUIREHTML','1');			// If we don't need to load the html.form.class.php
//if (! defined('NOREQUIREAJAX'))  define('NOREQUIREAJAX','1');
//if (! defined("NOLOGIN"))        define("NOLOGIN",'1');				// If this page is public (can be called outside logged session)

// Change this following line to use the correct relative path (../, ../../, etc)
$res = 0;
if (!$res && file_exists('../main.inc.php')) {
    $res = @include '../main.inc.php';
}
if (!$res && file_exists('../../main.inc.php')) {
    $res = @include '../../main.inc.php';
}
if (!$res && file_exists('../../../main.inc.php')) {
    $res = @include '../../../main.inc.php';
}
if (!$res) {
    die('Include of main fails');
}

// Change this following line to use the correct relative path from htdocs
require_once '../class/building.class.php';
require_once '../class/room.class.php';
require_once '../class/html.formplace.class.php';
require_once '../lib/place.lib.php';

// Load traductions files requiredby by page
$langs->load('place@place');
$langs->load('companies');
$langs->load('other');

// Get parameters
$id = GETPOST('id', 'int');
$roomid = GETPOST('roomid', 'int');
$action = GETPOST('action', 'alpha');
$fk_place = GETPOST('fk_place', 'int');
$fk_building = GETPOST('building', 'int');
$ref = GETPOST('ref', 'alpha');
$lat = GETPOST('lat', 'alpha');
$lng = GETPOST('lng', 'alpha');

$mode = GETPOST('mode', 'alpha');
$confirm = GETPOST('confirm', 'alpha');

if (!$user->rights->place->read) {
    accessforbidden();
}

$obj_building = new Building($db);
$obj_room = new Room($db);

/*******************************************************************
* ACTIONS
*
* Put here all code to do according to value of "action" parameter
********************************************************************/
if ($action == 'updateroom' && !$_POST['cancel'] && $user->rights->place->write) {
    $error = 0;

    if (empty($ref)) {
        ++$error;
        setEventMessage('<div class="error">'.$langs->trans('ErrorFieldRequired', $langs->transnoentities('Ref')).'</div>');
    }

    $res = $obj_room->fetch($roomid);
    if (!$res) {
        ++$error;
        setEventMessage('<div class="error">'.$langs->trans('ErrorFailedToLoadRoom', $langs->transnoentities('Id')).'</div>');
    }

    if (!$error) {
        $obj_room->ref = $ref;
        $obj_room->label = GETPOST('label', 'alpha');

        $obj_room->type_code = GETPOST('fk_type_room', 'alpha');
        $obj_room->capacity = GETPOST('capacity', 'int');

        $obj_room->note_public = GETPOST('note_public');
        $obj_room->note_private = GETPOST('note_private');

        $result = $obj_room->update($user);
        if ($result > 0) {
            header('Location: '.$_SERVER['PHP_SELF'].'?building='.$fk_building);
            exit;
        } else {
            setEventMessage('<div class="error">'.$obj_room->error.'</div>');

            $action = 'editroom';
        }
    } else {
        $action = 'editroom';
    }
} // Remove line
elseif ($action == 'confirm_deleteroom' && $confirm == 'yes' && $user->rights->place->write) {
    $ret = $obj_room->fetch($roomid);
    if ($ret) {
        $result = $obj_room->delete($user);
        if ($result > 0) {
            setEventMessage($langs->trans('RoomDeleted'));
            header('Location: '.$_SERVER['PHP_SELF'].'?building='.$fk_building);
            exit;
        } else {
            setEventMessage('<div class="error">'.$obj_room->error.'</div>');
            $action = '';
        }
    } else {
        setEventMessage('<div class="error">'.$obj_room->error.'</div>');

        $action = '';
    }

    header('Location: '.$_SERVER['PHP_SELF'].'?id='.$obj_building->id);
    exit;
}

/***************************************************
* VIEW
*
* Put here all code to build page
****************************************************/

llxHeader('', $langs->trans('RoomsManagment'), '', '', '', '', array('/place/js/place.js.php'));

$form = new Form($db);

if ($fk_building && $obj_building->fetch($fk_building) > 0) {
    $head = placePrepareHead($obj_building->place);
    dol_fiche_head($head, 'buildings', $langs->trans('PlaceSingular'), 0, 'place@place');

    $ret = $obj_building->place->printInfoTable();

    echo '</div>';

    //Second tabs list for building
    $head = buildingPrepareHead($obj_building);
    dol_fiche_head($head, 'rooms', $langs->trans('BuildingSingular'), 0, 'building@place');

    /*---------------------------------------
     * View building info
    */
    $obj_building->printShortInfoTable();
    echo '</div><br />';
}

/*
 * Floors management
 */
print load_fiche_titre($langs->trans('RoomsManagment'), '', 'room_32.png@place');

// Show room for fk_building
$sortorder = GETPOST('sortorder', 'alpha');
$sortfield = GETPOST('sortfield', 'alpha');
$page = GETPOST('page', 'int');

if (empty($sortorder)) {
    $sortorder = 'DESC';
}
if (empty($sortfield)) {
    $sortfield = 't.rowid';
}
if (empty($arch)) {
    $arch = 0;
}

if ($page == -1) {
    $page = 0;
}

$limit = $conf->liste_limit;
$offset = $limit * $page;
$pageprev = $page - 1;
$pagenext = $page + 1;

if ($fk_building > 0) {
    $filter = array('t.fk_building' => $fk_building);
}

$list_room = $obj_room->fetch_all($sortorder, $sortfield, $limit, $offset, $filter);

if (is_array($obj_room->lines) && sizeof($obj_room->lines) > 0) {
    // Confirm delete
    if ($action == 'ask_deleteroom') {
        $out .= $form->formconfirm($_SERVER['PHP_SELF'].'?building='.$fk_building.'&roomid='.$roomid, $langs->trans('DeleteRoom'), $langs->trans('ConfirmDeleteRoom'), 'confirm_deleteroom', '', 0, 1);
    }

    $out .= '<table width="100%;" class="noborder">';
    //$out .=  '<table class="noborder">'."\n";
    $out .= '<tr class="liste_titre">';
    $out .= '<th class="liste_titre">'.$langs->trans('RoomNumber').'</th>';
    $out .= '<th class="liste_titre">'.$langs->trans('Label').'</th>';
    $out .= '<th class="liste_titre">'.$langs->trans('RoomFloor').'</th>';
    $out .= '<th class="liste_titre">'.$langs->trans('PlaceRoomDictType').'</th>';
    $out .= '<th class="liste_titre">'.$langs->trans('RoomCapacityShort').'</th>';
    $out .= '<th class="liste_titre">'.$langs->trans('Edit').'</th>';
    $out .= '</tr>';

    foreach ($obj_room->lines as $key => $room) {
        if ($action == 'editroom' && $room->id == GETPOST('roomid', 'int')) {
            $out .= '<form action="'.$_SERVER['PHP_SELF'].'?id='.$id.'#'.$room->id.'" method="POST">';
            $out .= '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
            $out .= '<input type="hidden" name="action" value="updateroom">';
            $out .= '<input type="hidden" name="usenewupdatelineform" value="1" />';
            $out .= '<input type="hidden" name="building" value="'.$fk_building.'">';
            $out .= '<input type="hidden" name="roomid" value="'.$room->id.'">';

            $out .= '<tr>';
            $out .= '<td>';
            $out .= '<input size="12" name="ref" value="'.(GETPOST('ref') ? GETPOST('ref') : $room->ref).'"></td>';
            $out .= '</td>';

            $out .= '<td>';
            $out .= '<input size="40" name="label" value="'.(GETPOST('label') ? GETPOST('label') : $room->label).'"></td>';
            $out .= '</td>';

            $out .= '<td>';
            $out .= $room->building->show_select_floor($id, 'fk_floor', $room->fk_floor);
            $out .= '</td>';

            $out .= '<td><div>';
            $formplace = new FormPlace($db);
            $out .= $formplace->select_types_rooms($room->type_code, 'fk_type_room', '', 2, '', 1);
            $out .= '</div></td>';

            $out .= '<td>';
            $out .= '<input size="8" name="capacity" value="'.(GETPOST('capacity') ? GETPOST('capacity') : $room->capacity).'"></td>';
            $out .= '</td>';

            $out .= '<td>';
            $out .= '<input type="submit" name="update_'.$room->id.'" value="'.$langs->trans('Save').'"/></td>';
            $out .= '</td>';

            $out .= '</tr>';
            $out .= '</form>';
        } else {
            $out .= '<tr>';
            $out .= '<td>';
            $roomstat = new Room($db);
            $roomstat->id = $room->id;
            $roomstat->ref = $room->ref;
            $out .= $roomstat->getNomUrl();
            $out .= '</td>';

            $out .= '<td>';
            $out .= $room->label;
            $out .= '</td>';

            $out .= '<td>';
            $out .= $room->floor_ref;
            $out .= '</td>';

            $out .= '<td>';
            $out .= $room->type_label;
            $out .= '</td>';

            $out .= '<td>';
            $out .= $room->capacity;
            $out .= '</td>';

            $out .= '<td>';
            $out .= '<a href="'.$_SERVER['PHP_SELF'].'?building='.$fk_building.'&amp;action=editroom&amp;roomid='.$room->id.'#'.$room->id.'">'.img_edit().'</a>';

            $out .= '<a href="'.$_SERVER['PHP_SELF'].'?building='.$fk_building.'&amp;action=ask_deleteroom&amp;roomid='.$room->id.'">'.img_delete().'</a>';
            $out .= '</td>';

            $out .= '</tr>';
            $out .= '</tr>';
        }
    }

    $out .= '</table>';
} elseif ($list_room > 0 && !sizeof($obj_room->lines)) {
    $out .= '<div class="info">'.$langs->trans('NoRoomFound').'</div>';
} else {
    setEventMessage($obj_room->error, 'errors');
}

echo $out;

/*
 * Boutons actions
 */
echo '<div class="tabsAction">';

if ($action != 'show_floor_form') {
    // Add room
    if ($user->rights->place->write) {
        if ($fk_building > 0) {
            $link_text = $langs->trans('AddNewRoomToThisBuilding');
        } else {
            $link_text = $langs->trans('AddRoom');
        }

        echo '<div class="inline-block divButAction">';
        echo '<a href="../room/add.php'.($fk_building > 0 ? '?building='.$fk_building : '').'" class="butAction">'.$link_text.'</a>';
        echo '</div>';
    }
}
echo '</div>';

// End of page
llxFooter();
$db->close();
