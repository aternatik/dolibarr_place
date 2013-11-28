<?php
/* Copyright (C) 2013	Jean-François Ferry	<jfefe@aternatik.fr>
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
 *					Initialy built by build_class_from_table on 2013-07-24 16:03
 */


// Change this following line to use the correct relative path (../, ../../, etc)
$res=0;
$res=@include("../main.inc.php");				// For root directory
if (! $res) $res=@include("../../main.inc.php");	// For "custom" directory
if (! $res) die("Include of main fails");

require_once 'class/place.class.php';
require_once 'class/building.class.php';
require_once 'lib/place.lib.php';


// Load traductions files requiredby by page
$langs->load("place@place");
$langs->load("companies");
$langs->load("other");

// Get parameters
$id			= GETPOST('id','int');
$action		= GETPOST('action','alpha');


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


// Protection if external user
if ($user->societe_id > 0)
{
	//accessforbidden();
}



/*******************************************************************
* ACTIONS
*
********************************************************************/
if ($action == 'confirm_add_place')
{
	$error='';

	$ref=GETPOST('ref','alpha');
	$fk_socpeople=GETPOST('fk_socpeople','int');
	$description=GETPOST('description','alpha');
	$lat=GETPOST('lat','alpha');
	$lng=GETPOST('lng','alpha');

	if (empty($ref))
	{
		$mesg=$langs->trans("ErrorFieldRequired",$langs->transnoentities("Ref"));
		setEventMessage($mesg, 'errors');
		$error++;
	}

	/*
	if (!$fk_socpeople || $fk_socpeople < 0)
	{
		$mesg=$langs->trans("ErrorFieldRequired",$langs->transnoentities("Contact"));
		setEventMessage($mesg, 'errors');
		$error++;
	}
	*/


	if (! $error)
	{
		$object=new Place($db);
		$object->ref=GETPOST('ref','alpha');
		$object->fk_socpeople=GETPOST('fk_socpeople','int');
		$object->description=GETPOST('description','alpha');
		$object->lat=GETPOST('lat','alpha');
		$object->lng=GETPOST('lng','alpha');

		$result=$object->create($user);
		if ($result > 0)
		{
			// Creation OK
			$db->commit();
			setEventMessage($langs->trans('PlaceCreatedWithSuccess'));
			Header("Location: fiche.php?id=" . $object->id);
			return;
		}
		else
		{
			// Creation KO
			setEventMessage($object->error, 'errors');
			$action = '';
		}
	}
	else
	{
		$action = '';
	}
}
elseif ($action == 'confirm_add_building')
{
	$error='';

	$ref=GETPOST('ref','alpha');
	$fk_place=GETPOST('id','int');
	$description=GETPOST('description','alpha');
	$lat=GETPOST('lat','alpha');
	$lng=GETPOST('lng','alpha');

	if (empty($ref))
	{
		$mesg=$langs->trans("ErrorFieldRequired",$langs->transnoentities("Ref"));
		setEventMessage($mesg, 'errors');
		$error++;
	}

	if (!$fk_place || $fk_place < 0)
	{
		$mesg=$langs->trans("ErrorFieldRequired",$langs->transnoentities("Place"));
		setEventMessage($mesg, 'errors');
		$error++;
	}


	if (! $error)
	{
		$object=new Building($db);
		$object->ref=GETPOST('ref','alpha');
		$object->fk_place=$fk_place;
		$object->description=GETPOST('description','alpha');
		$object->lat=GETPOST('lat','alpha');
		$object->lng=GETPOST('lng','alpha');

		$result=$object->create($user);
		if ($result > 0)
		{
			// Creation OK
			$db->commit();
			setEventMessage($langs->trans('BuildingCreatedWithSuccess'));
			Header("Location: building/fiche.php?id=" . $object->id);
			return;
		}
		else
		{
			// Creation KO
			setEventMessage($object->error, 'errors');
			$action = 'add_building';
		}
	}
	else
	{
		$action = 'add_building';
	}
}




/***************************************************
* VIEW
*
* Put here all code to build page
****************************************************/

$form=new Form($db);
$object = new Place($db);

if(!$action) {

	$pagetitle=$langs->trans('AddPlace');
	llxHeader('',$pagetitle,'');
	print_fiche_titre($pagetitle,'','place_32.png@place');


	print '<form method="post" action="'.$_SERVER['PHP_SELF'].'" name="add_place">';
	print '<input type="hidden" name="action" value="confirm_add_place" />';

	print '<table class="border" width="100%">';

	// Ref / label
	$field = 'ref';
	print '<tr>';
	print '<td>';
	print '<label for="'.$field.'" class="fieldrequired">';
	print $langs->trans('PlaceFormLabel_'.$field);
	print '</td>';
	print '<td>';
	print '<input type="text" name="'.$field.'" value="'.$$field.'" />';
	print '</td>';
	print '</tr>';

	// Associated socpeople
	$field = 'fk_socpeople';
	print '<tr>';
	print '<td>';
	print '<label for="'.$field.'">';
	print $langs->trans('PlaceFormLabel_'.$field);
	print '</label>';
	print '</td>';
	print '<td>';
	// Contact list with company name
	$ret = $form->select_contacts($socid,$$field,$field,1,'','','','',1);
	//$form->select_contacts(  $forcecombo=0, $event=array(), $options_only=false)
	print '</td>';
	print '</tr>';

	// Description
	$field = 'description';
	print '<tr>';
	print '<td>';
	print '<label for="'.$field.'">';
	print $langs->trans('PlaceFormLabel_'.$field);
	print '</label>';
	print '</td>';
	print '<td>';
	require_once (DOL_DOCUMENT_ROOT . "/core/class/doleditor.class.php");
	$doleditor = new DolEditor($field, $$field, 160, '', '', false);
	$doleditor->Create();
	print '</td>';
	print '</tr>';

	// Latitude
	$field = 'lat';
	print '<tr>';
	print '<td>';
	print '<label for="'.$field.'">';
	print $langs->trans('PlaceFormLabel_'.$field);
	print '</label>';
	print '</td>';
	print '<td>';
	print '<input type="text" name="'.$field.'" value="'.$$field.'">';
	print '</td>';
	print '</tr>';

	// Longitude
	$field = 'lng';
	print '<tr>';
	print '<td>';
	print '<label for="'.$field.'">';
	print $langs->trans('PlaceFormLabel_'.$field);
	print '</label>';
	print '</td>';
	print '<td>';
	print '<input type="text" name="'.$field.'" value="'.$$field.'">';
	print '</td>';
	print '</tr>';

	print '</table>';

	print '<div style="text-align: center">
		<input type="submit"  class="button" name="" value="'.$langs->trans('Save').'" />
		</div>';

	print '</form>';
}
else if($action == 'add_building' && $user->rights->place->write)
{

	$pagetitle=$langs->trans('AddBuilding');
	llxHeader('',$pagetitle,'');

	if($object->fetch($id) > 0)
	{
		$head=placePrepareHead($object);
		dol_fiche_head($head, 'buildings', $langs->trans("PlaceSingular"),0,'place@place');

		$object->printInfoTable();

		print '</div>';

		$link_back = '<a href="building/list.php?id='.$id.'">'.$langs->trans('BackToBuildingList').'</a>';
	}

	print_fiche_titre($pagetitle,$link_back,'building_32.png@place');


	print '<form method="post" action="'.$_SERVER['PHP_SELF'].'" name="add_building">';
	print '<input type="hidden" name="action" value="confirm_add_building" />';
	print '<input type="hidden" name="id" value="'.$id.'" />';

	print '<table class="border" width="100%">';

	// Ref / label
	$field = 'ref';
	print '<tr>';
	print '<td>';
	print '<label for="'.$field.'" class="fieldrequired">';
	print $langs->trans('BuildingFormLabel_'.$field);
	print '</td>';
	print '<td>';
	print '<input type="text" name="'.$field.'" value="'.$$field.'" />';
	print '</td>';
	print '</tr>';

	// Associated place
	if(!$id)
	{
		$field = 'fk_place';
		print '<tr>';
		print '<td>';
		print '<label for="'.$field.'" class="fieldrequired">';
		print $langs->trans('BuildingFormLabel_'.$field);
		print '</label>';
		print '</td>';
		print '<td>';

		print '<a href="index.php">';
		print $langs->trans('PleaseSelectPlaceFirst');
		print '</a>';

		//$ret = $form->select_places($socid,$$field,$field,1,'','','','',1);
		print '</td>';
		print '</tr>';
	}

	// Description
	$field = 'description';
	print '<tr>';
	print '<td>';
	print '<label for="'.$field.'">';
	print $langs->trans('BuildingFormLabel_'.$field);
	print '</label>';
	print '</td>';
	print '<td>';
	require_once (DOL_DOCUMENT_ROOT . "/core/class/doleditor.class.php");
	$doleditor = new DolEditor($field, $$field, 160, '', '', false);
	$doleditor->Create();
	print '</td>';
	print '</tr>';

	// Latitude
	$field = 'lat';
	print '<tr>';
	print '<td>';
	print '<label for="'.$field.'">';
	print $langs->trans('BuildingFormLabel_'.$field);
	print '</label>';
	print '</td>';
	print '<td>';
	print '<input type="text" name="'.$field.'" value="'.$$field.'">';
	print '</td>';
	print '</tr>';

	// Longitude
	$field = 'lng';
	print '<tr>';
	print '<td>';
	print '<label for="'.$field.'">';
	print $langs->trans('BuildingFormLabel_'.$field);
	print '</label>';
	print '</td>';
	print '<td>';
	print '<input type="text" name="'.$field.'" value="'.$$field.'">';
	print '</td>';
	print '</tr>';

	print '</table>';

	print '<div style="text-align: center">
	<input type="submit"  class="button" name="" value="'.$langs->trans('Save').'" />
	</div>';

	print '</form>';
}


// End of page
llxFooter();
$db->close();
?>
