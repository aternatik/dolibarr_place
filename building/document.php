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
 *  \file       place/building/document.php
 *  \brief      Tab for documents linked to building
 *  \ingroup    place
 */

$res=0;
if (! $res && file_exists("../main.inc.php")) {
    $res=@include '../main.inc.php';
}
if (! $res && file_exists("../../main.inc.php")) {
    $res=@include '../../main.inc.php';
}
if (! $res && file_exists("../../../main.inc.php")) {
    $res=@include '../../../main.inc.php';
}
if (! $res) {
    die("Include of main fails");
}

require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/images.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';

// Change this following line to use the correct relative path from htdocs
require_once '../class/building.class.php';
require_once '../lib/place.lib.php';

$langs->load("place@place");
$langs->load('other');

$action=GETPOST('action', 'aZ09');
$confirm=GETPOST('confirm');
$id=(GETPOST('socid', 'int') ? GETPOST('socid', 'int') : GETPOST('id', 'int'));
$ref = GETPOST('ref', 'alpha');

// Security check
if ($user->societe_id > 0) {
	unset($action);
	$socid = $user->societe_id;
}
$result = restrictedArea($user, 'societe', $id, '&societe');

// Get parameters
$sortfield = GETPOST("sortfield", 'alpha');
$sortorder = GETPOST("sortorder", 'alpha');
$page = GETPOST("page", 'int');
if ($page == -1) {
    $page = 0;
}
$offset = $conf->liste_limit * $page;
$pageprev = $page - 1;
$pagenext = $page + 1;
if (! $sortorder) {
    $sortorder="ASC";
}
if (! $sortfield) {
    $sortfield="name";
}

$object = new Building($db);
if ($id > 0 || ! empty($ref)) {
	$result = $object->fetch($id, $ref);

	$relativepathwithnofile = dol_sanitizeFileName($object->place->id.'-'.str_replace(' ', '-', $object->place->ref)).'/building/'.dol_sanitizeFileName($object->ref).'/';
	$upload_dir = $conf->place->multidir_output[$object->entity].'/'.$relativepathwithnofile;
}

/*
 * Actions
 */

include_once DOL_DOCUMENT_ROOT . '/core/actions_linkedfiles.inc.php';



/*
 * View
 */

$form = new Form($db);

//$help_url='EN:Module_Third_Parties|FR:Module_Tiers|ES:Empresas';
llxHeader('', $langs->trans("Building").' - '.$langs->trans("Files"), $help_url);

if ($object->id) {
	/*
	 * Affichage onglets
	 */
    if ($object->place) {
        $head=placePrepareHead($object->place);
        dol_fiche_head($head, 'buildings', $langs->trans("PlaceSingular"), 0, 'place@place');
    
        $ret = $object->place->printInfoTable();
        print '</div>';
    }
    
	$head = buildingPrepareHead($object);

	$form=new Form($db);

	dol_fiche_head($head, 'document', $langs->trans("BuildingSingular"), 0, 'building@place');

	// Construit liste des fichiers
	$filearray=dol_dir_list($upload_dir, "files", 0, '', '(\.meta|_preview\.png)$', $sortfield, (strtolower($sortorder)=='desc'?SORT_DESC:SORT_ASC), 1);
	$totalsize=0;
	foreach ($filearray as $key => $file) {
		$totalsize+=$file['size'];
	}

	$linkback = '<a href="' .dol_buildpath('/place/building/list.php', 1) . '?restore_lastsearch_values=1' . (! empty($socid) ? '&socid=' . $socid : '') . '">' . $langs->trans("BackToList") . '</a>';

	dol_banner_tab($object, 'ref', $linkback, 1, 'ref', 'ref', $morehtmlref);
	print '<div class="fichecenter">';

    print '<div class="underbanner clearboth"></div>';
	print '<table class="border centpercent">';

	// Number of files
	print '<tr><td class="titlefield">'.$langs->trans("NbOfAttachedFiles").'</td><td colspan="3">'.count($filearray).'</td></tr>';

	// Total size
	print '<tr><td>'.$langs->trans("TotalSizeOfAttachedFiles").'</td><td colspan="3">'.$totalsize.' '.$langs->trans("bytes").'</td></tr>';

	print '</table>';

	print '</div>';
	dol_fiche_end();


	$modulepart = 'place';
	$permission = $user->rights->place->write;
	$permtoedit = 1;
	$param = '&id=' . $object->id;
	
	include_once DOL_DOCUMENT_ROOT . '/core/tpl/document_actions_post_headers.tpl.php';
} else {
	accessforbidden('', 0, 0);
}


llxFooter();
$db->close();
