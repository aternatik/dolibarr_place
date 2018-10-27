<?php
/* Copyright (C) 2007-2010	Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2013-2018	Jean-François Ferry	 <hello+jf@librethic.io>
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
 *   	\file       place/room/card.php
 *		\ingroup    place
 *		\brief      This file is an example of a php page.
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
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';

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
$action = GETPOST('action', 'alpha');
$fk_place = GETPOST('fk_place', 'int');
$ref = GETPOST('ref', 'alpha');
$lat = GETPOST('lat', 'alpha');
$lng = GETPOST('lng', 'alpha');

if (!$user->rights->place->read) {
    accessforbidden();
}

$object = new Room($db);

$extrafields = new ExtraFields($db);
$extralabels = $extrafields->fetch_name_optionals_label($object->table_element);

/*******************************************************************
* ACTIONS
*
* Put here all code to do according to value of "action" parameter
********************************************************************/
if ($action == 'room_update' && !$_POST['cancel'] && $user->rights->place->write) {
    $error = 0;

    if (empty($ref)) {
        ++$error;
        setEventMessage('<div class="error">'.$langs->trans('ErrorFieldRequired', $langs->transnoentities('Ref')).'</div>');
    }

    $res = $object->fetch($id);
    if (!$res) {
        ++$error;
        setEventMessage('<div class="error">'.$langs->trans('ErrorFailedToLoadRoom', $langs->transnoentities('Id')).'</div>');
    }

    if (!$error) {
        $object->ref = $ref;
        $object->label = GETPOST('label', 'alpha');
        $object->fk_floor = GETPOST('fk_floor', 'int');

        $object->type_code = GETPOST('fk_type_room', 'alpha');
        $object->capacity = GETPOST('capacity', 'int');

        $object->note_public = GETPOST('note_public');
        $object->note_private = GETPOST('note_private');

        $ret = $extrafields->setOptionalsFromPost($extralabels, $object);

        $result = $object->update($user);
        if ($result > 0) {
            header('Location: '.$_SERVER['PHP_SELF'].'?id='.$object->id);
            exit;
        } else {
            setEventMessage('<div class="error">'.$object->error.'</div>');

            $action = 'edit_room';
        }
    } else {
        $action = 'editroom';
    }
}

// Remove file in doc form
elseif ($action == 'remove_file') {
    if ($object->fetch($id)) {
        require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';

        $langs->load('other');
        $upload_dir = $conf->place->dir_output;
        $file = $upload_dir.'/'.GETPOST('file');
        $ret = dol_delete_file($file, 0, 0, 0, $object);
        if ($ret) {
            setEventMessage($langs->trans('FileWasRemoved', GETPOST('urlfile')));
        } else {
            setEventMessage($langs->trans('ErrorFailToDeleteFile', GETPOST('urlfile')), 'errors');
        }
    }
}
/*
 * Generate document
*/
if ($action == 'builddoc') {  // En get ou en post
    if (is_numeric(GETPOST('model'))) {
        $error = $langs->trans('ErrorFieldRequired', $langs->transnoentities('Model'));
    } else {
        require_once '../core/modules/place/modules_place.php';

        $object->fetch($id);

        // Define output language
        $outputlangs = $langs;
        $newlang = '';
        if ($conf->global->MAIN_MULTILANGS && empty($newlang) && !empty($_REQUEST['lang_id'])) {
            $newlang = $_REQUEST['lang_id'];
        }
        if ($conf->global->MAIN_MULTILANGS && empty($newlang)) {
            $newlang = $fac->client->default_lang;
        }
        if (!empty($newlang)) {
            $outputlangs = new Translate('', $conf);
            $outputlangs->setDefaultLang($newlang);
        }
        $result = room_doc_create($db, $object, '', GETPOST('model', 'alpha'), $outputlangs);
        if ($result <= 0) {
            dol_print_error($db, $result);
            exit;
        }
    }
}

/***************************************************
* VIEW
*
* Put here all code to build page
****************************************************/

llxHeader('', 'Room', '');

$form = new Form($db);
$formfile = new FormFile($db);

if ($object->fetch($id) > 0) {
    if ($object->place) {
        $head = placePrepareHead($object->place);
        dol_fiche_head($head, 'buildings', $langs->trans('PlaceSingular'), 0, 'place@place');

        $ret = $object->place->printInfoTable();
        echo '</div>';
    }

    //Second tabs list for building
    if ($object->building) {
        $head = buildingPrepareHead($object->building);
        dol_fiche_head($head, 'rooms', $langs->trans('BuildingSingular'), 0, 'building@place');

        $ret = $object->building->printShortInfoTable();
        echo '</div>';
    }

    $head = roomPrepareHead($object);
    dol_fiche_head($head, 'room', $langs->trans('RoomSingular'), 0, 'room@place');

    if ($action == 'edit') {
        if (!$user->rights->place->write) {
            accessforbidden('', 0);
        }

        /*---------------------------------------
         * Edit object
        */
        echo '<form action="'.$_SERVER['PHP_SELF'].'" method="POST">';
        echo '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
        echo '<input type="hidden" name="action" value="room_update">';
        echo '<input type="hidden" name="id" value="'.$object->id.'">';

        echo '<table class="border" width="100%">';

        // Ref
        echo '<tr><td width="20%">'.$langs->trans('RoomNumber').'</td>';
        echo '<td><input size="12" name="ref" value="'.(GETPOST('ref') ? GETPOST('ref') : $object->ref).'"></td></tr>';

        // Label
        echo '<tr><td valign="top">'.$langs->trans('Label').'</td>';
        echo '<td><input size="12" name="label" value="'.(GETPOST('label') ? GETPOST('label') : $object->label).'"></td></tr>';

        // Floor
        echo '<tr><td width="20%">'.$langs->trans('RoomFormLabel_floor').'</td>';
        echo '<td>';
        echo $object->building->show_select_floor($object->building->id, 'fk_floor', $object->fk_floor);
        //<input size="12" name="fk_floor" value="'.(GETPOST('fk_floor') ? GETPOST('fk_floor') : $object_room->fk_floor).'">';
        echo '</td></tr>';

        // Room type
        $formplace = new FormPlace($db);
        echo '<tr><td width="20%">'.$langs->trans('PlaceRoomDictType').'</td>';
        echo '<td>';
        echo $formplace->select_types_rooms($object->fk_type_room, 'fk_type_room', '', 2);
        echo '</td></tr>';

        // Capacity
        echo '<tr><td width="20%">'.$langs->trans('RoomCapacityShort').'</td>';
        echo '<td><input size="12" name="capacity" value="'.(GETPOST('capacity') ? GETPOST('capacity') : $object->capacity).'"></td></tr>';

        // Public note
        echo '<tr><td valign="top">'.$langs->trans('NotePublic').'</td>';
        echo '<td>';
        echo '<textarea name="note_public" cols="80" rows="'.ROWS_3.'">'.($_POST['note_public'] ? GETPOST('note_public', 'alpha') : $object->note_public).'</textarea><br>';
        echo '</td></tr>';

        // Private note
        if (!$user->societe_id) {
            echo '<tr><td valign="top">'.$langs->trans('NotePrivate').'</td>';
            echo '<td>';
            echo '<textarea name="note_private" cols="80" rows="'.ROWS_3.'">'.($_POST['note_private'] ? GETPOST('note_private') : $object->note_private).'</textarea><br>';
            echo '</td></tr>';
        }

        // Extrafields
        $reshook = $hookmanager->executeHooks('formObjectOptions', $parameters, $object, $action);    // Note that $action and $object may have been modified by hook
        if (empty($reshook) && !empty($extrafields->attribute_label)) {
            echo $object->showOptionals($extrafields, 'edit');
        }

        echo '<tr><td align="center" colspan="2">';
        echo '<input name="update" class="button" type="submit" value="'.$langs->trans('Modify').'"> &nbsp; ';
        echo '<input type="submit" class="button" name="cancel" Value="'.$langs->trans('Cancel').'"></td></tr>';
        echo '</table>';
        echo '</form>';
    } else {

        /*---------------------------------------
         * View object
        */
        echo '<table width="100%" class="border">';

        // Ref / label
        echo '<tr>';
        echo '<td  width="20%">'.$langs->trans('RoomNumber').'</td>';
        echo '<td   width="30%">';
        echo $object->ref;
        echo '</td>';
        echo '</tr>';

        // Label
        echo '<tr>';
        echo '<td  width="20%">'.$langs->trans('Label').'</td>';
        echo '<td   width="30%">';
        echo $object->label;
        echo '</td>';
        echo '</tr>';

        // Floor
        echo '<tr>';
        echo '<td  width="20%">'.$langs->trans('RoomFloor').'</td>';
        echo '<td   width="30%">';
        $object->fetch_floor($object->fk_floor);
        echo $object->floor->ref;
        echo '</td>';
        echo '</tr>';

        // Type
        echo '<tr>';
        echo '<td  width="20%">'.$langs->trans('PlaceRoomDictType').'</td>';
        echo '<td   width="30%">';
        echo $object->type_label;
        echo '</td>';
        echo '</tr>';

        // Capacity
        echo '<tr>';
        echo '<td  width="20%">'.$langs->trans('RoomCapacityShort').'</td>';
        echo '<td   width="30%">';
        echo $object->capacity;
        echo '</td>';
        echo '</tr>';

        // Extrafields
        $reshook = $hookmanager->executeHooks('formObjectOptions', $parameters, $object, $action);    // Note that $action and $object may have been modified by hook
        if (empty($reshook) && !empty($extrafields->attribute_label)) {
            echo $object->showOptionals($extrafields);
        }

        echo '</table>';
    }

    echo '</div>';

    /*
     * Boutons actions
    */
    echo '<div class="tabsAction">';

    if ($action != 'edit') {

        // Edit building
        if ($user->rights->place->write) {
            echo '<div class="inline-block divButAction">';
            echo '<a href="'.$_SERVER['PHP_SELF'].'?id='.$id.'&amp;action=edit" class="butAction">'.$langs->trans('Edit').'</a>';
            echo '</div>';
        }

        // Floor managment
        if ($user->rights->place->write) {
            echo '<div class="inline-block divButAction">';
            echo '<a href="floors.php?id='.$id.'" class="butAction">'.$langs->trans('FloorManagment').'</a>';
            echo '</div>';
        }
    }
    echo '</div>';

    /*
     * Documents generes
    */

    $dirtoscan = dol_sanitizeFileName($object->place->id.'-'.str_replace(' ', '-', $object->place->ref)).'/building/'.dol_sanitizeFileName($object->building->ref).'/rooms/'.dol_sanitizeFileName($object->ref);
    $filedir = $conf->place->dir_output.'/'.dol_sanitizeFileName($object->place->id.'-'.str_replace(' ', '-', $object->place->ref)).'/building/'.dol_sanitizeFileName($object->building->ref).'/rooms/'.dol_sanitizeFileName($object->ref);
    $urlsource = $_SERVER['PHP_SELF'].'?id='.$object->id;
    $genallowed = $user->rights->place->read;
    $delallowed = $user->rights->place->write;
    $var = true;
    print $formfile->showdocuments('place', $dirtoscan, $filedir, $urlsource, $genallowed, $delallowed, $object->modelpdf);

    $events = $object->getActionsForResource('room@place', $id, $filter);

    print load_fiche_titre($langs->trans('EventsForThisRoom'));
    echo "<table class='noborder' width='100%'>\n";
    echo "<tr class='liste_titre'><td colspan=''>".$langs->trans('DateStart').'</td><td>'.$langs->trans('DateEnd').'</td><td>'.$langs->trans('Title').'</td><td>'.$langs->trans('Type').'</td><td>'.$langs->trans('Edit').'</td>';
    echo "</tr>\n";
    if (count($events) > 0) {
        $var = true;
        foreach ($events as $event) {
            $var = !$var;
            echo "\t<tr ".$bc[$var].">\n";

            echo '<td>'.dol_print_date($event['datep'], 'dayhour').'</td>';
            echo '<td>'.dol_print_date($event['datef'], 'dayhour').'</td>';

            echo '<td with="50%">';
            if (!class_exists('ActionComm')) {
                require_once DOL_DOCUMENT_ROOT.'/comm/action/class/actioncomm.class.php';

            }
            $eventStat = new ActionComm($db);
            $eventStat->fetch($event['rowid']);
            echo $eventStat->getNomUrl(1);
            echo "</td>\n";
            echo '<td>'.$event['code'].'</td>';
            //print "<td>".dolGetFirstLastname($event->author->firstname,$event->author->lastname)."</td>";
            echo '<td><a href="'.dol_buildpath('/resource/element_resource.php', 1).'?element=action&element_id='.$event['rowid'].'">'.img_picto('', 'edit').'</a></td>';

            echo "\t</tr>\n";
        }
    } else {
        echo '<tr '.$bc[false].'><td colspan="3">'.$langs->trans('NoEvents').'</td></tr>';
    }
    echo "</table>\n";
}

// Example 2 : Adding links to objects
// The class must extends CommonObject class to have this method available
//$somethingshown=$object->showLinkedObjectBlock();

// Example 3 : List of data
$error = 0;
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
