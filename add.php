<?php
/* Copyright (C) 2013-2016	Jean-François Ferry	<jfefe@aternatik.fr>
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
 *   	\file       place/place.php
 *		\ingroup    place
 *		\brief      Page to manage place object
 *					Initialy built by build_class_from_table on 2013-07-24 16:03.
 */

// Change this following line to use the correct relative path (../, ../../, etc)
$res = 0;
$res = @include '../main.inc.php';                // For root directory
if (!$res) {
    $res = @include '../../main.inc.php';
}    // For "custom" directory
if (!$res) {
    die('Include of main fails');
}

require_once 'class/place.class.php';
require_once 'class/building.class.php';
require_once 'lib/place.lib.php';

// Load traductions files requiredby by page
$langs->load('place@place');
$langs->load('companies');
$langs->load('other');

// Get parameters
$id = GETPOST('id', 'int');
$action = GETPOST('action', 'alpha');

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

$limit = $conf->global->limit;
$offset = $limit * $page;
$pageprev = $page - 1;
$pagenext = $page + 1;

// Protection if external user
if ($user->societe_id > 0) {
    //accessforbidden();
}

/*******************************************************************
* ACTIONS
*
********************************************************************/
if ($action == 'confirm_add_place') {
    $error = '';

    $ref = GETPOST('ref', 'alpha');
    $fk_socpeople = GETPOST('fk_socpeople', 'int');
    $description = GETPOST('description', 'alpha');
    $lat = GETPOST('lat', 'alpha');
    $lng = GETPOST('lng', 'alpha');

    if (empty($ref)) {
        $mesg = $langs->trans('ErrorFieldRequired', $langs->transnoentities('Ref'));
        setEventMessage($mesg, 'errors');
        ++$error;
    }

    /*
    if (!$fk_socpeople || $fk_socpeople < 0)
    {
        $mesg=$langs->trans("ErrorFieldRequired",$langs->transnoentities("Contact"));
        setEventMessage($mesg, 'errors');
        $error++;
    }
    */

    if (!$error) {
        $object = new Place($db);
        $object->ref = GETPOST('ref', 'alpha');
        $object->fk_socpeople = GETPOST('fk_socpeople', 'int');
        $object->description = GETPOST('description', 'alpha');
        $object->lat = GETPOST('lat', 'alpha');
        $object->lng = GETPOST('lng', 'alpha');

        $result = $object->create($user);
        if ($result > 0) {
            // Creation OK
            $db->commit();
            setEventMessage($langs->trans('PlaceCreatedWithSuccess'));
            header('Location: fiche.php?id='.$object->id);

            return;
        } else {
            // Creation KO
            setEventMessage($object->error, 'errors');
            $action = '';
        }
    } else {
        $action = '';
    }
} elseif ($action == 'confirm_add_building') {
    $error = '';

    $ref = GETPOST('ref', 'alpha');
    $fk_place = GETPOST('id', 'int');
    $description = GETPOST('description', 'alpha');
    $lat = GETPOST('lat', 'alpha');
    $lng = GETPOST('lng', 'alpha');

    if (empty($ref)) {
        $mesg = $langs->trans('ErrorFieldRequired', $langs->transnoentities('Ref'));
        setEventMessage($mesg, 'errors');
        ++$error;
    }

    if (!$fk_place || $fk_place < 0) {
        $mesg = $langs->trans('ErrorFieldRequired', $langs->transnoentities('Place'));
        setEventMessage($mesg, 'errors');
        ++$error;
    }

    if (!$error) {
        $object = new Building($db);
        $object->ref = GETPOST('ref', 'alpha');
        $object->fk_place = $fk_place;
        $object->description = GETPOST('description', 'alpha');
        $object->lat = GETPOST('lat', 'alpha');
        $object->lng = GETPOST('lng', 'alpha');

        $result = $object->create($user);
        if ($result > 0) {
            // Creation OK
            $db->commit();
            setEventMessage($langs->trans('BuildingCreatedWithSuccess'));
            header('Location: building/fiche.php?id='.$object->id);

            return;
        } else {
            // Creation KO
            setEventMessage($object->error, 'errors');
            $action = 'add_building';
        }
    } else {
        $action = 'add_building';
    }
}

/***************************************************
* VIEW
*
* Put here all code to build page
****************************************************/

$form = new Form($db);
$object = new Place($db);

if (!$action) {
    $pagetitle = $langs->trans('AddPlace');
    llxHeader('', $pagetitle, '');
    print load_fiche_titre($pagetitle, '', 'place_32.png@place');

    echo '<form method="post" action="'.$_SERVER['PHP_SELF'].'" name="add_place">';
    echo '<input type="hidden" name="action" value="confirm_add_place" />';

    echo '<table class="border" width="100%">';

    // Ref / label
    $field = 'ref';
    echo '<tr>';
    echo '<td>';
    echo '<label for="'.$field.'" class="fieldrequired">';
    echo $langs->trans('PlaceFormLabel_'.$field);
    echo '</td>';
    echo '<td>';
    echo '<input type="text" name="'.$field.'" value="'.$$field.'" />';
    echo '</td>';
    echo '</tr>';

    // Associated socpeople
    $field = 'fk_socpeople';
    echo '<tr>';
    echo '<td>';
    echo '<label for="'.$field.'">';
    echo $langs->trans('PlaceFormLabel_'.$field);
    echo '</label>';
    echo '</td>';
    echo '<td>';
    // Contact list with company name
    $ret = $form->select_contacts($socid, $$field, $field, 1, '', '', '', '', 1);
    //$form->select_contacts(  $forcecombo=0, $event=array(), $options_only=false)
    echo '</td>';
    echo '</tr>';

    // Description
    $field = 'description';
    echo '<tr>';
    echo '<td>';
    echo '<label for="'.$field.'">';
    echo $langs->trans('PlaceFormLabel_'.$field);
    echo '</label>';
    echo '</td>';
    echo '<td>';
    require_once DOL_DOCUMENT_ROOT.'/core/class/doleditor.class.php';
    $doleditor = new DolEditor($field, $$field, 160, '', '', false);
    $doleditor->Create();
    echo '</td>';
    echo '</tr>';

    // Latitude
    $field = 'lat';
    echo '<tr>';
    echo '<td>';
    echo '<label for="'.$field.'">';
    echo $langs->trans('PlaceFormLabel_'.$field);
    echo '</label>';
    echo '</td>';
    echo '<td>';
    echo '<input type="text" name="'.$field.'" value="'.$$field.'">';
    echo '</td>';
    echo '</tr>';

    // Longitude
    $field = 'lng';
    echo '<tr>';
    echo '<td>';
    echo '<label for="'.$field.'">';
    echo $langs->trans('PlaceFormLabel_'.$field);
    echo '</label>';
    echo '</td>';
    echo '<td>';
    echo '<input type="text" name="'.$field.'" value="'.$$field.'">';
    echo '</td>';
    echo '</tr>';

    echo '</table>';

    echo '<div style="text-align: center">
		<input type="submit"  class="button" name="" value="'.$langs->trans('Save').'" />
		</div>';

    echo '</form>';
} elseif ($action == 'add_building' && $user->rights->place->write) {
    $pagetitle = $langs->trans('AddBuilding');
    llxHeader('', $pagetitle, '');

    if ($object->fetch($id) > 0) {
        $head = placePrepareHead($object);
        dol_fiche_head($head, 'buildings', $langs->trans('PlaceSingular'), 0, 'place@place');

        $object->printInfoTable();

        echo '</div>';

        $link_back = '<a href="building/list.php?id='.$id.'">'.$langs->trans('BackToBuildingList').'</a>';
    }

    print load_fiche_titre($pagetitle, $link_back, 'building_32.png@place');

    echo '<form method="post" action="'.$_SERVER['PHP_SELF'].'" name="add_building">';
    echo '<input type="hidden" name="action" value="confirm_add_building" />';
    echo '<input type="hidden" name="id" value="'.$id.'" />';

    echo '<table class="border" width="100%">';

    // Ref / label
    $field = 'ref';
    echo '<tr>';
    echo '<td>';
    echo '<label for="'.$field.'" class="fieldrequired">';
    echo $langs->trans('BuildingFormLabel_'.$field);
    echo '</td>';
    echo '<td>';
    echo '<input type="text" name="'.$field.'" value="'.$$field.'" />';
    echo '</td>';
    echo '</tr>';

    // Associated place
    if (!$id) {
        $field = 'fk_place';
        echo '<tr>';
        echo '<td>';
        echo '<label for="'.$field.'" class="fieldrequired">';
        echo $langs->trans('BuildingFormLabel_'.$field);
        echo '</label>';
        echo '</td>';
        echo '<td>';

        echo '<a href="index.php">';
        echo $langs->trans('PleaseSelectPlaceFirst');
        echo '</a>';

        //$ret = $form->select_places($socid,$$field,$field,1,'','','','',1);
        echo '</td>';
        echo '</tr>';
    }

    // Description
    $field = 'description';
    echo '<tr>';
    echo '<td>';
    echo '<label for="'.$field.'">';
    echo $langs->trans('BuildingFormLabel_'.$field);
    echo '</label>';
    echo '</td>';
    echo '<td>';
    require_once DOL_DOCUMENT_ROOT.'/core/class/doleditor.class.php';
    $doleditor = new DolEditor($field, $$field, 160, '', '', false);
    $doleditor->Create();
    echo '</td>';
    echo '</tr>';

    // Latitude
    $field = 'lat';
    echo '<tr>';
    echo '<td>';
    echo '<label for="'.$field.'">';
    echo $langs->trans('BuildingFormLabel_'.$field);
    echo '</label>';
    echo '</td>';
    echo '<td>';
    echo '<input type="text" name="'.$field.'" value="'.$$field.'">';
    echo '</td>';
    echo '</tr>';

    // Longitude
    $field = 'lng';
    echo '<tr>';
    echo '<td>';
    echo '<label for="'.$field.'">';
    echo $langs->trans('BuildingFormLabel_'.$field);
    echo '</label>';
    echo '</td>';
    echo '<td>';
    echo '<input type="text" name="'.$field.'" value="'.$$field.'">';
    echo '</td>';
    echo '</tr>';

    echo '</table>';

    echo '<div style="text-align: center">
	<input type="submit"  class="button" name="" value="'.$langs->trans('Save').'" />
	</div>';

    echo '</form>';
}

// End of page
llxFooter();
$db->close();
