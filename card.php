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

require DOL_DOCUMENT_ROOT.'/contact/class/contact.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';

require 'class/place.class.php';
require 'lib/place.lib.php';

// Load traductions files requiredby by page
$langs->loadLangs(array(
    'place@place',
    'companies',
    'other')
);

// Get parameters
$id = GETPOST('id', 'int');
$action = GETPOST('action', 'alpha');
$ref = GETPOST('ref', 'alpha');
$lat = GETPOST('lat', 'alpha');
$lng = GETPOST('lng', 'alpha');

// Protection if external user
//if ($user->societe_id > 0)
//{
    //accessforbidden();
//}

if (!$user->rights->place->read) {
    accessforbidden();
}

$object = new Place($db);

/*******************************************************************
* ACTIONS
*
* Put here all code to do according to value of "action" parameter
********************************************************************/

if ($action == 'update' && !$_POST['cancel'] && $user->rights->place->write) {
    $error = 0;

    if (empty($ref)) {
        ++$error;
        $mesg = '<div class="error">'.$langs->trans('ErrorFieldRequired', $langs->transnoentities('Ref')).'</div>';
    }

    if (!$error) {
        $object->fetch($id);

        $object->ref = $ref;
        $object->lat = $lat;
        $object->lng = $lng;
        $object->description = $_POST['description'];
        $object->note_public = $_POST['note_public'];
        $object->note_private = $_POST['note_private'];

        $result = $object->update($user);
        if ($result > 0) {
            header('Location: '.$_SERVER['PHP_SELF'].'?id='.$object->id);
            exit;
        } else {
            $mesg = '<div class="error">'.$object->error.'</div>';

            $action = 'edit';
        }
    } else {
        $action = 'edit';
    }
}

/*
 * Generate document
*/
if ($action == 'builddoc') {  // En get ou en post
    if (is_numeric(GETPOST('model'))) {
        $error = $langs->trans('ErrorFieldRequired', $langs->transnoentities('Model'));
    } else {
        require_once 'core/modules/place/modules_place.php';

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
        $result = place_doc_create($db, $object, '', GETPOST('model', 'alpha'), $outputlangs);
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
$pagetitle = $langs->trans('FichePlace');
llxHeader('', $pagetitle, '');

$form = new Form($db);
$formfile = new FormFile($db);

if ($object->fetch($id) > 0) {
    $head = placePrepareHead($object);
    dol_fiche_head($head, 'place', $langs->trans('PlaceSingular'), 0, 'place@place');

    if ($action == 'edit') {
        if (!$user->rights->place->write) {
            accessforbidden('', 0);
        }

        /*---------------------------------------
         * Edit object
        */
        echo '<form action="'.$_SERVER['PHP_SELF'].'" method="POST">';
        echo '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
        echo '<input type="hidden" name="action" value="update">';
        echo '<input type="hidden" name="id" value="'.$object->id.'">';

        echo '<table class="border" width="100%">';

        // Ref
        echo '<tr><td width="20%">'.$langs->trans('Ref').'</td>';
        echo '<td><input size="12" name="ref" value="'.(GETPOST('ref') ? GETPOST('ref') : $object->ref).'"></td></tr>';

        // Description
        echo '<tr><td valign="top">'.$langs->trans('Description').'</td>';
        echo '<td>';
        echo '<textarea name="description" cols="80" rows="'.ROWS_3.'">'.($_POST['description'] ? GETPOST('description', 'alpha') : $object->description).'</textarea>';
        echo '</td></tr>';

        // Lat
        echo '<tr><td width="20%">'.$langs->trans('Latitude').'</td>';
        echo '<td><input size="12" name="lat" value="'.(GETPOST('lat') ? GETPOST('lat') : $object->lat).'"></td></tr>';

        // Long
        echo '<tr><td width="20%">'.$langs->trans('Longitude').'</td>';
        echo '<td><input size="12" name="lng" value="'.(GETPOST('lng') ? GETPOST('lng') : $object->lng).'"></td></tr>';

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

        // Ref
        echo '<tr>';
        echo '<td  width="20%">'.$langs->trans('NameOfThePlace').'</td>';
        echo '<td   width="30%">';
        echo $object->ref;
        echo '</td>';
        echo '</tr>';

        // socpeople
        echo '<tr>';
        echo '<td  width="20%">'.$langs->trans('SocPeopleAssociated').'</td>';
        echo '<td   width="30%">';
        $contactstat = new Contact($db);
        if ($contactstat->fetch($object->fk_socpeople)) {
            print $contactstat->getNomUrl(1);
        }
        echo '</td>';
        echo '</tr>';

        // Description
        echo '<tr>';
        echo '<td  width="20%">'.$langs->trans('Description').'</td>';
        echo '<td   width="30%">';
        echo $object->description;
        echo '</td>';
        echo '</tr>';

        // Latitude
        echo '<tr>';
        echo '<td  width="20%">'.$langs->trans('Latitude').'</td>';
        echo '<td   width="30%">';
        echo $object->lat;
        echo '</td>';
        echo '</tr>';

        // Longitude
        echo '<tr>';
        echo '<td  width="20%">'.$langs->trans('Longitude').'</td>';
        echo '<td   width="30%">';
        echo $object->lng;
        echo '</td>';
        echo '</tr>';

        // Link to OSM
        echo '<tr>';
        echo '<td  width="20%">'.$langs->trans('OSMLink').'</td>';
        echo '<td   width="30%">';
        echo '<a href="http://openstreetmap.org/#map='.$conf->global->PLACE_DEFAULT_ZOOM_FOR_MAP.'/'.$object->lat.'/'.$object->lng.'" target="_blank">'.$langs->trans('ShowInOSM').'</a>';
        echo '</td>';
        echo '</tr>';

        echo '</table>';
    }

    echo '</div>';

    /*
     * Boutons actions
    */
    echo '<div class="tabsAction">';

    if ($action != 'edit') {

        // Edit place
        if ($user->rights->place->write) {
            echo '<div class="inline-block divButAction">';
            echo '<a href="'.$_SERVER['PHP_SELF'].'?id='.$id.'&amp;action=edit" class="butAction">'.$langs->trans('Edit').'</a>';
            echo '</div>';
        }

        // Add building
        if ($user->rights->place->write) {
            echo '<div class="inline-block divButAction">';
            echo '<a href="add.php?id='.$id.'&amp;action=add_building" class="butAction">'.$langs->trans('AddBuilding').'</a>';
            echo '</div>';
        }

        print '<div class="fichecenter"><div class="fichehalfleft">';
        print '<a name="builddoc"></a>'; // ancre

        // Documents
        $objref = dol_sanitizeFileName($object->ref);
        $relativepath = $comref . '/' . $comref . '.pdf';
        $filedir = $conf->place->dir_output . '/' . $objref;
        $urlsource = $_SERVER["PHP_SELF"] . "?id=" . $object->id;
        $genallowed = $user->rights->place->read;    // If you can read, you can build the PDF to read content
        $delallowed = $user->rights->place->create;  // If you can create/edit, you can remove a file on card
        print $formfile->showdocuments('place', $objref, $filedir, $urlsource, $genallowed, $delallowed, $object->modelpdf, 1, 0, 0, 28, 0, '', '', '', $soc->default_lang);
        

        // Show links to link elements
        $linktoelem = $form->showLinkToObjectBlock($object, null, array('myobject'));
        $somethingshown = $form->showLinkedObjectBlock($object, $linktoelem);


        print '</div><div class="fichehalfright"><div class="ficheaddleft">';

        $MAXEVENT = 10;

        $morehtmlright = '<a href="'.dol_buildpath('/mymodule/myobject_info.php', 1).'?id='.$object->id.'">';
        $morehtmlright.= $langs->trans("SeeAll");
        $morehtmlright.= '</a>';

        // List of actions on element
        include_once DOL_DOCUMENT_ROOT . '/core/class/html.formactions.class.php';
        $formactions = new FormActions($db);
        $somethingshown = $formactions->showactions($object, 'myobject', $socid, 1, '', $MAXEVENT, '', $morehtmlright);

        print '</div></div></div>';

    }
} else {
    dol_print_error();
}

// End of page
llxFooter();
$db->close();
