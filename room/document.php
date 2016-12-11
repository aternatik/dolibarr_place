<?php
/* Copyright (C) 2002-2007 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2010 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2012 Regis Houssin        <regis.houssin@capnetworks.com>
 * Copyright (C) 2010      Juanjo Menent        <jmenent@2byte.es>
 * Copyright (C) 2013      Cédric Salvador      <csalvador@gpcsolutions.fr>
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
 *  \file       place/room/document.php
 *  \brief      Tab for documents linked to a room
 *  \ingroup    place.
 */
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

require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/images.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';

// Change this following line to use the correct relative path from htdocs
require_once '../class/room.class.php';
require_once '../lib/place.lib.php';

$langs->load('place@place');
$langs->load('other');

$action = GETPOST('action');
$confirm = GETPOST('confirm');
$id = (GETPOST('socid', 'int') ? GETPOST('socid', 'int') : GETPOST('id', 'int'));
$ref = GETPOST('ref', 'alpha');

// Security check
if ($user->societe_id > 0) {
    unset($action);
    $socid = $user->societe_id;
}
$result = restrictedArea($user, 'societe', $id, '&societe');

// Get parameters
$sortfield = GETPOST('sortfield', 'alpha');
$sortorder = GETPOST('sortorder', 'alpha');
$page = GETPOST('page', 'int');
if ($page == -1) {
    $page = 0;
}
$offset = $conf->liste_limit * $page;
$pageprev = $page - 1;
$pagenext = $page + 1;
if (!$sortorder) {
    $sortorder = 'ASC';
}
if (!$sortfield) {
    $sortfield = 'name';
}

$object = new Room($db);
if ($id > 0 || !empty($ref)) {
    $result = $object->fetch($id, $ref);

    $relativepathwithnofile = dol_sanitizeFileName($object->place->id.'-'.str_replace(' ', '-', $object->place->ref)).'/building/'.dol_sanitizeFileName($object->building->ref).'/rooms/'.dol_sanitizeFileName($object->ref).'/'; // for sub-directory
    $upload_dir = $conf->place->dir_output.'/'.$relativepathwithnofile;
}

/*
 * Actions
 */

include_once DOL_DOCUMENT_ROOT.'/core/tpl/document_actions_pre_headers.tpl.php';

/*
 * View
 */

$form = new Form($db);

//$help_url='EN:Module_Third_Parties|FR:Module_Tiers|ES:Empresas';
llxHeader('', $langs->trans('Room').' - '.$langs->trans('Files'), $help_url);

if ($object->id) {

    /*
     * Affichage onglets
     */

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
    dol_fiche_head($head, 'document', $langs->trans('RoomSingular'), 0, 'room@place');

    // Construit liste des fichiers
    $filearray = dol_dir_list($upload_dir, 'files', 0, '', '(\.meta|_preview\.png)$', $sortfield, (strtolower($sortorder) == 'desc' ? SORT_DESC : SORT_ASC), 1);
    $totalsize = 0;
    foreach ($filearray as $key => $file) {
        $totalsize += $file['size'];
    }

    echo '<table class="border"width="100%">';

    // Ref
    echo '<tr><td width="25%">'.$langs->trans('RoomFormLabel_ref').'</td>';
    echo '<td colspan="3">';
    echo $form->showrefnav($object, 'id', '', ($user->societe_id ? 0 : 1), 'rowid', 'ref');
    echo '</td></tr>';

    // Nbre fichiers
    echo '<tr><td>'.$langs->trans('NbOfAttachedFiles').'</td><td colspan="3">'.count($filearray).'</td></tr>';

    //Total taille
    echo '<tr><td>'.$langs->trans('TotalSizeOfAttachedFiles').'</td><td colspan="3">'.$totalsize.' '.$langs->trans('bytes').'</td></tr>';

    echo '</table>';

    echo '</div>';

    $modulepart = 'place';
    $permission = $user->rights->place->write;
    $param = '&id='.$object->id;
    include_once DOL_DOCUMENT_ROOT.'/core/tpl/document_actions_post_headers.tpl.php';
} else {
    accessforbidden('', 0, 0);
}

llxFooter();
$db->close();
