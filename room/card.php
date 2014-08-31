<?php
/* Copyright (C) 2007-2010	Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2013		Jean-François Ferry	<jfefe@aternatik.fr>
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
 *		\brief      This file is an example of a php page
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
$res=0;
if (! $res && file_exists("../main.inc.php")) $res=@include '../main.inc.php';
if (! $res && file_exists("../../main.inc.php")) $res=@include '../../main.inc.php';
if (! $res && file_exists("../../../main.inc.php")) $res=@include '../../../main.inc.php';
if (! $res) die("Include of main fails");

require_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';

require_once '../class/building.class.php';
require_once '../class/room.class.php';
require_once '../class/html.formplace.class.php';
require_once '../lib/place.lib.php';


// Load traductions files requiredby by page
$langs->load("place@place");
$langs->load("companies");
$langs->load("other");

// Get parameters
$id			= GETPOST('id','int');
$action		= GETPOST('action','alpha');
$fk_place	= GETPOST('fk_place','int');
$ref		= GETPOST('ref','alpha');
$lat		= GETPOST('lat','alpha');
$lng		= GETPOST('lng','alpha');

if( ! $user->rights->place->read)
	accessforbidden();

$object=new Room($db);

$extrafields = new ExtraFields($db);
$extralabels=$extrafields->fetch_name_optionals_label($object->table_element);


/*******************************************************************
* ACTIONS
*
* Put here all code to do according to value of "action" parameter
********************************************************************/
if ($action == 'room_update' && ! $_POST["cancel"]  && $user->rights->place->write )
{
	$error=0;

	if (empty($ref))
	{
		$error++;
		setEventMessage('<div class="error">'.$langs->trans("ErrorFieldRequired",$langs->transnoentities("Ref")).'</div>');
	}

	$res = $object->fetch($id);
	if(!$res)
	{
		$error++;
		setEventMessage('<div class="error">'.$langs->trans("ErrorFailedToLoadRoom",$langs->transnoentities("Id")).'</div>');
	}

	if (! $error)
	{

		$object->ref          		= $ref;
		$object->label  			= GETPOST("label",'alpha');
		$object->fk_floor  			= GETPOST("fk_floor",'int');

		$object->type_code  		= GETPOST("fk_type_room",'alpha');
		$object->capacity  			= GETPOST("capacity",'int');

		$object->note_public       	= GETPOST("note_public");
		$object->note_private       = GETPOST("note_private");

		$ret = $extrafields->setOptionalsFromPost($extralabels,$object);

		$result=$object->update($user);
		if ($result > 0)
		{
			Header("Location: ".$_SERVER['PHP_SELF']."?id=".$object->id);
			exit;
		}
		else
		{
			setEventMessage('<div class="error">'.$object->error.'</div>');

			$action='edit_room';
		}

	}
	else
	{
		$action='editroom';
	}
}


// Remove file in doc form
else if ($action == 'remove_file')
{
    if ($object->fetch($id))
    {
        require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';

        $langs->load("other");
        $upload_dir = $conf->place->dir_output;
        $file = $upload_dir . '/' . GETPOST('file');
        $ret=dol_delete_file($file,0,0,0,$object);
        if ($ret) setEventMessage($langs->trans("FileWasRemoved", GETPOST('urlfile')));
        else setEventMessage($langs->trans("ErrorFailToDeleteFile", GETPOST('urlfile')), 'errors');
    }
}
/*
 * Generate document
*/
if ($action == 'builddoc')  // En get ou en post
{
    if (is_numeric(GETPOST('model')))
    {
        $error=$langs->trans("ErrorFieldRequired",$langs->transnoentities("Model"));
    }
    else
    {
        require_once '../core/modules/place/modules_place.php';

        $object->fetch($id);

        // Define output language
        $outputlangs = $langs;
        $newlang='';
        if ($conf->global->MAIN_MULTILANGS && empty($newlang) && ! empty($_REQUEST['lang_id'])) $newlang=$_REQUEST['lang_id'];
        if ($conf->global->MAIN_MULTILANGS && empty($newlang)) $newlang=$fac->client->default_lang;
        if (! empty($newlang))
        {
            $outputlangs = new Translate("",$conf);
            $outputlangs->setDefaultLang($newlang);
        }
        $result=room_doc_create($db, $object, '', GETPOST('model','alpha'), $outputlangs);
        if ($result <= 0)
        {
            dol_print_error($db,$result);
            exit;
        }
    }
}

/***************************************************
* VIEW
*
* Put here all code to build page
****************************************************/

llxHeader('','Room','');

$form=new Form($db);
$formfile = new FormFile($db);

if($object->fetch($id) > 0)
{
	if($object->place)
	{
		$head=placePrepareHead($object->place);
		dol_fiche_head($head, 'buildings', $langs->trans("PlaceSingular"),0,'place@place');

		$ret = $object->place->printInfoTable();
		print '</div>';
	}


	//Second tabs list for building
	if($object->building)
	{
		$head=buildingPrepareHead($object->building);
		dol_fiche_head($head, 'rooms', $langs->trans("BuildingSingular"),0,'building@place');

		$ret = $object->building->printShortInfoTable();
		print '</div>';
	}

	$head=roomPrepareHead($object);
	dol_fiche_head($head, 'room', $langs->trans("RoomSingular"),0,'room@place');



	if ($action == 'edit' )
	{

		if(!$user->rights->place->write)
			accessforbidden('',0);

		/*---------------------------------------
		 * Edit object
		*/
		print '<form action="'.$_SERVER["PHP_SELF"].'" method="POST">';
		print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
		print '<input type="hidden" name="action" value="room_update">';
		print '<input type="hidden" name="id" value="'.$object->id.'">';

		print '<table class="border" width="100%">';

		// Ref
		print '<tr><td width="20%">'.$langs->trans("RoomNumber").'</td>';
		print '<td><input size="12" name="ref" value="'.(GETPOST('ref') ? GETPOST('ref') : $object->ref).'"></td></tr>';


		// Label
		print '<tr><td valign="top">'.$langs->trans("Label").'</td>';
		print '<td><input size="12" name="label" value="'.(GETPOST('label') ? GETPOST('label') : $object->label).'"></td></tr>';


		// Floor
		print '<tr><td width="20%">'.$langs->trans("RoomFormLabel_floor").'</td>';
		print '<td>';
		print $object->building->show_select_floor($object->building->id, 'fk_floor',$object->fk_floor);
		//<input size="12" name="fk_floor" value="'.(GETPOST('fk_floor') ? GETPOST('fk_floor') : $object_room->fk_floor).'">';
		print '</td></tr>';

		// Room type
		$formplace = new FormPlace($db);
		print '<tr><td width="20%">'.$langs->trans("PlaceRoomDictType").'</td>';
		print '<td>';
		print $formplace->select_types_rooms($object->fk_type_room, 'fk_type_room','',2);
		print '</td></tr>';

		// Capacity
		print '<tr><td width="20%">'.$langs->trans("RoomCapacityShort").'</td>';
		print '<td><input size="12" name="capacity" value="'.(GETPOST('capacity') ? GETPOST('capacity') : $object->capacity).'"></td></tr>';

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

		// Extrafields
		$reshook=$hookmanager->executeHooks('formObjectOptions',$parameters,$object,$action);    // Note that $action and $object may have been modified by hook
		if (empty($reshook) && ! empty($extrafields->attribute_label))
		{
			print $object->showOptionals($extrafields,'edit');
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

		// Ref / label
		print '<tr>';
		print '<td  width="20%">' . $langs->trans("RoomNumber") . '</td>';
		print '<td   width="30%">';
		print $object->ref;
		print '</td>';
		print '</tr>';

		// Label
		print '<tr>';
		print '<td  width="20%">' . $langs->trans("Label") . '</td>';
		print '<td   width="30%">';
		print $object->label;
		print '</td>';
		print '</tr>';

		// Floor
		print '<tr>';
		print '<td  width="20%">' . $langs->trans("RoomFloor") . '</td>';
		print '<td   width="30%">';
		$object->fetch_floor($object->fk_floor);
		print $object->floor->ref;
		print '</td>';
		print '</tr>';

		// Type
		print '<tr>';
		print '<td  width="20%">' . $langs->trans("PlaceRoomDictType") . '</td>';
		print '<td   width="30%">';
		print $object->type_label;
		print '</td>';
		print '</tr>';

		// Capacity
		print '<tr>';
		print '<td  width="20%">' . $langs->trans("RoomCapacityShort") . '</td>';
		print '<td   width="30%">';
		print $object->capacity;
		print '</td>';
		print '</tr>';

		// Extrafields
		$reshook=$hookmanager->executeHooks('formObjectOptions',$parameters,$object,$action);    // Note that $action and $object may have been modified by hook
		if (empty($reshook) && ! empty($extrafields->attribute_label))
		{
			print $object->showOptionals($extrafields);
		}

		print '</table>';



	}

	print '</div>';

	/*
	 * Boutons actions
	*/
	print '<div class="tabsAction">';

	if ($action != "edit" )
	{

		// Edit building
		if($user->rights->place->write)
		{
			print '<div class="inline-block divButAction">';
			print '<a href="'.$_SERVER['PHP_SELF'].'?id='.$id.'&amp;action=edit" class="butAction">'.$langs->trans('Edit').'</a>';
			print '</div>';
		}

		// Floor managment
		if($user->rights->place->write)
		{
			print '<div class="inline-block divButAction">';
			print '<a href="floors.php?id='.$id.'" class="butAction">'.$langs->trans('FloorManagment').'</a>';
			print '</div>';
		}
	}
	print '</div>';

	/*
	 * Documents generes
	*/

	$dirtoscan=dol_sanitizeFileName($object->place->id.'-'.str_replace(' ','-',$object->place->ref))."/building/".dol_sanitizeFileName($object->building->ref).'/rooms/'.dol_sanitizeFileName($object->ref);
	$filedir=$conf->place->dir_output . '/'.dol_sanitizeFileName($object->place->id.'-'.str_replace(' ','-',$object->place->ref))."/building/".dol_sanitizeFileName($object->building->ref).'/rooms/'.dol_sanitizeFileName($object->ref);
	$urlsource=$_SERVER["PHP_SELF"]."?id=".$object->id;
	$genallowed=$user->rights->place->read;
	$delallowed=$user->rights->place->write;
	$var=true;
	$somethingshown=$formfile->show_documents('place',$dirtoscan,$filedir,$urlsource,$genallowed,$delallowed,$object->modelpdf);


	$events = $object->getActionsForResource('room@place',$id,$filter);

	print_fiche_titre($langs->trans("EventsForThisRoom"));
	print "<table class='noborder' width='100%'>\n";
	print "<tr class='liste_titre'><td colspan=''>".$langs->trans("DateStart")."</td><td>".$langs->trans("DateEnd")."</td><td>".$langs->trans("Title")."</td><td>".$langs->trans("Type")."</td><td>".$langs->trans("Edit")."</td>";
	print "</tr>\n";
	if (count($events) > 0)
	{
	    $var=true;
	    foreach ($events as $event)
	    {
	        $var=!$var;
	        print "\t<tr ".$bc[$var].">\n";

	        print '<td>'.dol_print_date($event['datep'],'dayhour').'</td>';
	        print '<td>'.dol_print_date($event['datef'],'dayhour').'</td>';

	        print '<td with="50%">';
	        print "<a href='".DOL_URL_ROOT."/comm/action/fiche.php?action=view&amp;id=".$event['rowid']."'>".$event['label']."</a>";
	        print "</td>\n";
	        print "<td>".$event['code']."</td>";
	        //print "<td>".dolGetFirstLastname($event->author->firstname,$event->author->lastname)."</td>";
	        print '<td><a href="'.dol_buildpath('/resource/element_resource.php',1).'?element=action&element_id='.$event['rowid'].'">'.img_picto('','edit').'</a></td>';

	        print "\t</tr>\n";
	    }
	}
	else
	{
	    print "<tr ".$bc[false].'><td colspan="3">'.$langs->trans("NoEvents")."</td></tr>";
	}
	print "</table>\n";



}




// Example 2 : Adding links to objects
// The class must extends CommonObject class to have this method available
//$somethingshown=$object->showLinkedObjectBlock();


// Example 3 : List of data
$error = 0;
if ($action == 'list')
{
    $sql = "SELECT";
    $sql.= " t.rowid,";

		$sql.= " t.entity,";
		$sql.= " t.ref,";
		$sql.= " t.label,";
		$sql.= " t.fk_place,";
		$sql.= " t.description,";
		$sql.= " t.lat,";
		$sql.= " t.lng,";
		$sql.= " t.note_public,";
		$sql.= " t.note_private,";
		$sql.= " t.fk_user_creat,";
		$sql.= " t.tms";


    $sql.= " FROM ".MAIN_DB_PREFIX."place_building as t";
    $sql.= " WHERE field3 = 'xxx'";
    $sql.= " ORDER BY field1 ASC";

    print '<table class="noborder">'."\n";
    print '<tr class="liste_titre">';
    print_liste_field_titre($langs->trans('field1'),$_SERVER['PHP_SELF'],'t.field1','',$param,'',$sortfield,$sortorder);
    print_liste_field_titre($langs->trans('field2'),$_SERVER['PHP_SELF'],'t.field2','',$param,'',$sortfield,$sortorder);
    print '</tr>';

    dol_syslog($script_file." sql=".$sql, LOG_DEBUG);
    $resql=$db->query($sql);
    if ($resql)
    {
        $num = $db->num_rows($resql);
        $i = 0;
        if ($num)
        {
            while ($i < $num)
            {
                $obj = $db->fetch_object($resql);
                if ($obj)
                {
                    // You can use here results
                    print '<tr><td>';
                    print $obj->field1;
                    print $obj->field2;
                    print '</td></tr>';
                }
                $i++;
            }
        }
    }
    else
    {
        $error++;
        dol_print_error($db);
    }

    print '</table>'."\n";
}



// End of page
llxFooter();
$db->close();
?>
