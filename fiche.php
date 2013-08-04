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
$res=@include("../../main.inc.php");				// For root directory
if (! $res) $res=@include("../../../main.inc.php");	// For "custom" directory
if (! $res) die("Include of main fails");

require DOL_DOCUMENT_ROOT.'/contact/class/contact.class.php';
require 'class/place.class.php';
require 'lib/place.lib.php';


// Load traductions files requiredby by page
$langs->load("place@place");
$langs->load("companies");
$langs->load("other");

// Get parameters
$id			= GETPOST('id','int');
$action		= GETPOST('action','alpha');
$ref		= GETPOST('ref','alpha');
$lat		= GETPOST('lat','alpha');
$lng		= GETPOST('lng','alpha');

// Protection if external user
//if ($user->societe_id > 0)
//{
	//accessforbidden();
//}

if( ! $user->rights->place->read)
	accessforbidden();

$object = new Place($db);


/*******************************************************************
* ACTIONS
*
* Put here all code to do according to value of "action" parameter
********************************************************************/

if ($action == 'update' && ! $_POST["cancel"]  && $user->rights->place->write )
{
	$error=0;

	if (empty($ref))
	{
		$error++;
		$mesg='<div class="error">'.$langs->trans("ErrorFieldRequired",$langs->transnoentities("Ref")).'</div>';
	}

	if (! $error)
	{
		$object->fetch($id);

		$object->ref          		= $ref;
		$object->lat          		= $lat;
		$object->lng          		= $lng;
		$object->description  		= $_POST["description"];
		$object->note_public       	= $_POST["note_public"];
		$object->note_private       = $_POST["note_private"];

		$result=$object->update($user);
		if ($result > 0)
		{
			Header("Location: ".$_SERVER['PHP_SELF']."?id=".$object->id);
			exit;
		}
		else
		{
			$mesg='<div class="error">'.$object->error.'</div>';

			$action='edit';
		}
	}
	else
	{
		$action='edit';
	}
}




/***************************************************
* VIEW
*
* Put here all code to build page
****************************************************/
$pagetitle=$langs->trans('FichePlace');
llxHeader('',$pagetitle,'');

$form=new Form($db);


if($object->fetch($id) > 0)
{
	$head=placePrepareHead($object);
	dol_fiche_head($head, 'place', $langs->trans("PlaceSingular"),0,'place@place');


	if ($action == 'edit' )
	{

		if(!$user->rights->place->write)
			accessforbidden('',0);

		/*---------------------------------------
		 * Edit object
		*/
		print '<form action="'.$_SERVER["PHP_SELF"].'" method="POST">';
		print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
		print '<input type="hidden" name="action" value="update">';
		print '<input type="hidden" name="id" value="'.$object->id.'">';

		print '<table class="border" width="100%">';

		// Ref
		print '<tr><td width="20%">'.$langs->trans("Ref").'</td>';
		print '<td><input size="12" name="ref" value="'.(GETPOST('ref') ? GETPOST('ref') : $object->ref).'"></td></tr>';


		// Description
		print '<tr><td valign="top">'.$langs->trans("Description").'</td>';
		print '<td>';
		print '<textarea name="description" cols="80" rows="'.ROWS_3.'">'.($_POST['description'] ? GETPOST('description','alpha') : $object->description).'</textarea>';
		print '</td></tr>';

		// Lat
		print '<tr><td width="20%">'.$langs->trans("Latitude").'</td>';
		print '<td><input size="12" name="lat" value="'.(GETPOST('lat') ? GETPOST('lat') : $object->lat).'"></td></tr>';

		// Long
		print '<tr><td width="20%">'.$langs->trans("Longitude").'</td>';
		print '<td><input size="12" name="lng" value="'.(GETPOST('lng') ? GETPOST('lng') : $object->lng).'"></td></tr>';

		// Public note
		print '<tr><td valign="top">'.$langs->trans("NotePublic").'</td>';
		print '<td>';
		print '<textarea name="note_public" cols="80" rows="'.ROWS_3.'">'.($_POST['note_public'] ? GETPOST('note_public','alpha') : $object->note_public)."</textarea><br>";
		print "</td></tr>";

		// Private note
		if (! $user->societe_id)
		{
			print '<tr><td valign="top">'.$langs->trans("NotePrivate").'</td>';
			print '<td>';
			print '<textarea name="note_private" cols="80" rows="'.ROWS_3.'">'.($_POST['note_private'] ? GETPOST('note_private') : $object->note_private)."</textarea><br>";
			print "</td></tr>";
		}

		print '<tr><td align="center" colspan="2">';
		print '<input name="update" class="button" type="submit" value="'.$langs->trans("Modify").'"> &nbsp; ';
		print '<input type="submit" class="button" name="cancel" Value="'.$langs->trans("Cancel").'"></td></tr>';
		print '</table>';
		print '</form>';
	}
	else
	{

		/*---------------------------------------
		 * View object
		 */

		print '<table width="100%" class="border">';

		// Ref
		print '<tr>';
		print '<td  width="20%">' . $langs->trans("Ref") . '</td>';
		print '<td   width="30%">';
		print $object->ref;
		print '</td>';
		print '</tr>';

		// socpeople
		print '<tr>';
		print '<td  width="20%">' . $langs->trans("SocPeopleAssociated") . '</td>';
		print '<td   width="30%">';
		$contactstat = new Contact($db);
		if($contactstat->fetch($object->fk_socpeople))
			print $contactstat->getNomUrl(1);
		print '</td>';
		print '</tr>';

		// Description
		print '<tr>';
		print '<td  width="20%">' . $langs->trans("Description") . '</td>';
		print '<td   width="30%">';
		print $object->description;
		print '</td>';
		print '</tr>';

		// Latitude
		print '<tr>';
		print '<td  width="20%">' . $langs->trans("Latitude") . '</td>';
		print '<td   width="30%">';
		print $object->lat;
		print '</td>';
		print '</tr>';

		// Longitude
		print '<tr>';
		print '<td  width="20%">' . $langs->trans("Longitude") . '</td>';
		print '<td   width="30%">';
		print $object->lng;
		print '</td>';
		print '</tr>';

		// Link to OSM
		print '<tr>';
		print '<td  width="20%">' . $langs->trans("OSMLink") . '</td>';
		print '<td   width="30%">';
		print '<a href="http://openstreetmap.org/?lat='.$object->lat.'&amp;lon='.$object->lng.'&amp;zoom=17" target="_blank">'.$langs->trans("ShowInOSM").'</a>';
		print '</td>';
		print '</tr>';


		print '</table>';

	}

	print '</div>';

	/*
	 * Boutons actions
	*/
	print '<div class="tabsAction">';

	if ($action != "edit" )
	{

		// Edit place
		print '<div class="inline-block divButAction">';
		print '<a href="'.$_SERVER['PHP_SELF'].'?id='.$id.'&amp;action=edit" class="butAction">'.$langs->trans('Edit').'</a>';
		print '</div>';
	}


}
else {
	dol_print_error();
}



// End of page
llxFooter();
$db->close();
?>
