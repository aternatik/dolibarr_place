<?php
/* Copyright (C) 2007-2010	Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2013-2016		Jean-François Ferry	<jfefe@aternatik.fr>
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
 *   	\file       place/room/add.php
 *		\ingroup    place
 *		\brief      Page to add a room into place management.
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

require_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';

// Change this following line to use the correct relative path from htdocs
require_once '../class/building.class.php';
require_once '../class/building.class.php';
require_once '../class/room.class.php';
require_once '../class/html.formplace.class.php';
require_once '../lib/place.lib.php';

// Load traductions files requiredby by page
$langs->load('place@place');
$langs->load('companies');
$langs->load('other');
$langs->load('admin');

// Get parameters
$id = GETPOST('id', 'int');
$action = GETPOST('action', 'alpha');
$fk_place = GETPOST('fk_place', 'int');
$fk_building = GETPOST('building', 'int');
$ref = GETPOST('ref', 'alpha');

if (!$user->rights->place->read) {
    accessforbidden();
}

$object = new Building($db);
$object_room = new Room($db);
$object_place = new Place($db);

$extrafields = new ExtraFields($db);
$extralabels = $extrafields->fetch_name_optionals_label($object_room->table_element);

/*******************************************************************
* ACTIONS
*
* Put here all code to do according to value of "action" parameter
********************************************************************/
if ($action == 'create' && !$_POST['cancel']) {
    $error = '';

    $ref = GETPOST('ref', 'alpha');
    $label = GETPOST('label', 'alpha');
    $fk_floor = GETPOST('fk_floor', 'int');
    $type_code = GETPOST('fk_type_room', 'alpha');
    $capacity = GETPOST('capacity', 'int');

    if (empty($ref)) {
        $mesg = $langs->trans('ErrorFieldRequired', $langs->transnoentities('RoomFormLabel_ref'));
        setEventMessage($mesg, 'errors');
        ++$error;
    }

    if (!$error) {
        $object_room->ref = GETPOST('ref', 'alpha');
        $object_room->label = GETPOST('label', 'alpha');
        $object_room->fk_place = $fk_place;
        $object_room->fk_building = $fk_building;
        $object_room->fk_floor = $fk_floor;
        $object_room->type_code = $type_code;
        $object_room->capacity = $capacity;

        $ret = $extrafields->setOptionalsFromPost($extralabels, $object_room);

        $result = $object_room->create($user);
        if ($result > 0) {
            // Creation OK
            $db->commit();
            setEventMessage($langs->trans('RoomCreatedWithSuccess'));
            header('Location: ../room/list.php?building='.$fk_building);

            return;
        } else {
            // Creation KO
            setEventMessage($object_room->error, 'errors');
            $action = '';
        }
    } else {
        $action = '';
    }
}

/***************************************************
* VIEW
*
* Put here all code to build page
****************************************************/

$pagetitle = $langs->trans('AddRoom');
llxHeader('', $pagetitle, '');

$form = new Form($db);
$formplace = new FormPlace($db);

// If we know place
if ($object_place->fetch($fk_place) > 0) {
    $head = placePrepareHead($object_place);
    dol_fiche_head($head, 'rooms', $langs->trans('PlaceSingular'), 0, 'place@place');

    $ret = $object_place->printInfoTable();

    echo '</div><br />';
}

// If we know building
if ($fk_building && $object->fetch($fk_building) > 0) {
    $head = placePrepareHead($object->place);
    dol_fiche_head($head, 'buildings', $langs->trans('PlaceSingular'), 0, 'place@place');

    $ret = $object->place->printInfoTable();

    echo '</div>';
    //Second tabs list for building
    $head = buildingPrepareHead($object);
    dol_fiche_head($head, 'rooms', $langs->trans('BuildingSingular'), 0, 'building@place');

    /*---------------------------------------
     * View building info
    */
    $ret_html = $object->printShortInfoTable();
    echo '</div><br />';

    $object_place->fetch($object->fk_place);
}

    if (!$user->rights->place->write) {
        accessforbidden('', 0);
    }

    /*---------------------------------------
     * Add object
    */

    print load_fiche_titre($pagetitle, '', 'room_32.png@place');

    echo '<form action="'.$_SERVER['PHP_SELF'].'" method="POST">';
    echo '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
    echo '<input type="hidden" name="action" value="create">';
    echo '<input type="hidden" name="id" value="'.$object_room->id.'">';

    if ($fk_building > 0) {
        print '<input type="hidden" name="building" value="'.$fk_building.'">';
    }

    echo '<table class="border" width="100%">';

    // Ref
    echo '<tr><td width="20%"><span class="fieldrequired">'.$langs->trans('RoomFormLabel_ref').'</span></td>';
    echo '<td><input size="12" name="ref" value="'.(GETPOST('ref') ? GETPOST('ref') : $object_room->ref).'"></td></tr>';

    // Place
    echo '<tr><td width="20%"><span class="fieldrequired">'.$langs->trans('RoomFormLabel_fk_place').'</span></td>';
    if (!$fk_place) {
        echo '<td>';
        $events[] = array('method' => 'getBuildings', 'url' => dol_buildpath('/place/core/ajax/buildings.php', 1), 'htmlname' => 'fk_building', 'params' => array());
        echo $formplace->select_place_list(GETPOST('fk_place') ? GETPOST('fk_place') : $object_place->id, 'fk_place', '', 1, 1, 0, $events);
        echo '</td>';
    } else {
        echo '<td>';
        echo $object_place->getNomUrl(1);
        echo '<input type="hidden" name="fk_place" value="'.(GETPOST('fk_place') ? GETPOST('fk_place') : $object_place->id).'">';
        echo '</td>';
    }
    echo '</tr>';

    // Building
    if (!$fk_building) {
        echo '<tr><td width="20%"><span class="fieldrequired">'.$langs->trans('RoomFormLabel_fk_building').'</span></td>';
        //print '<td><input size="12" name="building" value="'.(GETPOST('building') ? GETPOST('building') : $object_room->fk_building).'"></td></tr>';
        echo '<td>';
        $event = array();
        echo $formplace->selectbuildings($fk_place, GETPOST('building') ? GETPOST('building') : $object_room->fk_building, 'building', 0, '', '', 0, '', false, 0, 0, $event);
        //print $formplace->show_select_building(,'fk_building',$fk_place,'','',0,$event);
        echo '<td>';
    }

    // Floor

    echo '<tr><td width="20%">'.$langs->trans('RoomFormLabel_floor').'</td>';
    echo '<td>';
    echo $object->show_select_floor($fk_building, 'fk_floor');
    if ($fk_building > 0) {
        echo ' <a href="../building/floors.php?id='.$fk_building.'">'.$langs->trans('FloorManagment').'</a>';
    }
    echo '</td></tr>';

    // Room type
    $formplace = new FormPlace($db);
    echo '<tr><td width="20%">'.$langs->trans('PlaceRoomDictType').'</td>';
    echo '<td>';
    echo $formplace->select_types_rooms($fk_type_room, 'fk_type_room', '', 2);
    echo '</td></tr>';

    // Capacity
    echo '<tr><td width="20%"><span class="">'.$langs->trans('RoomFormLabel_capacity').'</span></td>';
    echo '<td><input size="12" name="capacity" value="'.(GETPOST('capacity') ? GETPOST('capacity') : $object_room->capacity).'"></td></tr>';

    // Extrafields
    $reshook = $hookmanager->executeHooks('formObjectOptions', $parameters, $object_room, $action);    // Note that $action and $object may have been modified by hook
    if (empty($reshook) && !empty($extrafields->attribute_label)) {
        echo $object->showOptionals($extrafields, 'edit');
    }

    // Public note
    echo '<tr><td valign="top">'.$langs->trans('NotePublic').'</td>';
    echo '<td>';
    echo '<textarea name="note_public" cols="80" rows="'.ROWS_3.'">'.($_POST['note_public'] ? GETPOST('note_public', 'alpha') : $object_room->note_public).'</textarea><br>';
    echo '</td></tr>';

    // Private note
    if (!$user->societe_id) {
        echo '<tr><td valign="top">'.$langs->trans('NotePrivate').'</td>';
        echo '<td>';
        echo '<textarea name="note_private" cols="80" rows="'.ROWS_3.'">'.($_POST['note_private'] ? GETPOST('note_private') : $object_room->note_private).'</textarea><br>';
        echo '</td></tr>';
    }

    echo '<tr><td align="center" colspan="2">';
    echo '<input name="add" class="button" type="submit" value="'.$langs->trans('Add').'"> &nbsp; ';
    echo '<input type="submit" class="button" name="cancel" Value="'.$langs->trans('Cancel').'"></td></tr>';
    echo '</table>';
    echo '</form>';

    echo '</div>';

    /*
     * Boutons actions
    */
    echo '<div class="tabsAction">';

    echo '</div>';

// Example 2 : Adding links to objects
// The class must extends CommonObject class to have this method available
//$somethingshown=$object->showLinkedObjectBlock();

// Example 3 : List of data
if ($action == 'list') {
    $sql = 'SELECT';
    $sql .= ' t.rowid,';

    $sql .= ' t.entity,';
    $sql .= ' t.ref,';
    $sql .= ' t.label,';
    $sql .= ' t.fk_place,';
    $sql .= ' t.description,';
    $sql .= ' t.lat,';
    $sql .= ' t.lng,';
    $sql .= ' t.note_public,';
    $sql .= ' t.note_private,';
    $sql .= ' t.fk_user_creat,';
    $sql .= ' t.tms';

    $sql .= ' FROM '.MAIN_DB_PREFIX.'place_building as t';
    $sql .= " WHERE field3 = 'xxx'";
    $sql .= ' ORDER BY field1 ASC';

    echo '<table class="noborder">'."\n";
    echo '<tr class="liste_titre">';
    print_liste_field_titre($langs->trans('field1'), $_SERVER['PHP_SELF'], 't.field1', '', $param, '', $sortfield, $sortorder);
    print_liste_field_titre($langs->trans('field2'), $_SERVER['PHP_SELF'], 't.field2', '', $param, '', $sortfield, $sortorder);
    echo '</tr>';

    dol_syslog($script_file.' sql='.$sql, LOG_DEBUG);
    $resql = $db->query($sql);
    if ($resql) {
        $num = $db->num_rows($resql);
        $i = 0;
        if ($num) {
            while ($i < $num) {
                $obj = $db->fetch_object($resql);
                if ($obj) {
                    // You can use here results
                    echo '<tr><td>';
                    echo $obj->field1;
                    echo $obj->field2;
                    echo '</td></tr>';
                }
                ++$i;
            }
        }
    } else {
        ++$error;
        dol_print_error($db);
    }

    echo '</table>'."\n";
}

// End of page
llxFooter();
$db->close();
