<?php
/* Module to manage locations, buildings, floors and rooms into Dolibarr ERP/CRM
 * Copyright (C) 2013	Jean-François Ferry	<jfefe@aternatik.fr>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *   	\file       place/index.php
 *		\ingroup    place
 *		\brief      Page to manage place object
 */


// Change this following line to use the correct relative path (../, ../../, etc)
$res=0;
$res=@include("../main.inc.php");				// For root directory
if (! $res) $res=@include("../../main.inc.php");	// For "custom" directory
if (! $res) die("Include of main fails");

require 'class/place.class.php';
require DOL_DOCUMENT_ROOT.'/contact/class/contact.class.php';



// Load traductions files requiredby by page
$langs->load("place@place");
$langs->load("companies");
$langs->load("other");

// Get parameters
$id			= GETPOST('id','int');
$action		= GETPOST('action','alpha');

$sortorder	= GETPOST('sortorder','alpha');
$sortfield	= GETPOST('sortfield','alpha');
$page		= GETPOST('page','int');


if (empty($sortorder)) $sortorder="DESC";
if (empty($sortfield)) $sortfield="t.rowid";
if (empty($arch)) $arch = 0;

if ($page == -1) {
	$page = 0 ;
}

$limit = $conf->global->limit;
$offset = $limit * $page ;
$pageprev = $page - 1;
$pagenext = $page + 1;

if( ! $user->rights->place->read)
	accessforbidden();

/***************************************************
 * VIEW
*
* Put here all code to build page
****************************************************/

$pagetitle=$langs->trans('PlacePageIndex');
llxHeader('',$pagetitle,'');



$form=new Form($db);
$object = new Place($db);

print_fiche_titre($pagetitle,'','place_32.png@place');

// Load object list
$ret = $object->fetch_all($sortorder, $sortfield, $limit, $offset);
if($ret == -1) {
	dol_print_error($db,$object->error);
	exit;
}

if(!$ret) {
	print '<div class="warning">'.$langs->trans('NoPlaceInDatabase').'</div>';
}
else
{
	print '<table class="noborder" width="100%">'."\n";
	print '<tr class="liste_titre">';
	print_liste_field_titre($langs->trans('PlaceSingular'),$_SERVER['PHP_SELF'],'t.ref','',$param,'',$sortfield,$sortorder);
	print_liste_field_titre($langs->trans('SocPeopleAssociated'),$_SERVER['PHP_SELF'],'t.fk_socpeople','',$param,'',$sortfield,$sortorder);
	print_liste_field_titre($langs->trans('Description'),$_SERVER['PHP_SELF'],'t.description','',$param,'',$sortfield,$sortorder);
	print_liste_field_titre($langs->trans('Edit'));
	print '</tr>';

	foreach ($object->lines as $place)
	{

		print '<tr><td>';
		print $object->getElementUrl($place->id, 'place',1);
		print '</td>';


		print '<td>';
		$contactstat = new Contact($db);
		if($contactstat->fetch($place->fk_socpeople))
			print $contactstat->getNomUrl(1);
		print '</td>';

		print '<td>';
		print $place->description;
		print '</td>';

		print '<td>';

		print '<a href="fiche.php?id='.$place->id.'">'.$langs->trans('SeeOrEdit').'</a>';


		print '</td></tr>';
	}

	print '</table>';

}


// Action Bar
print '<div class="tabsAction">';

// Add place
print '<div class="inline-block divButAction">';
print '<a href="add.php" class="butAction">'.$langs->trans('AddPlace').'</a>';
print '</div>';


print '</div>';
