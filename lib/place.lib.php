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
 *	\file		lib/place.lib.php
 *	\ingroup	place
 *	\brief		This file is library for place module
 */

function placeAdminPrepareHead()
{
	global $langs, $conf;

	$langs->load("place@place");

	$h = 0;
	$head = array();

	$head[$h][0] = dol_buildpath("/place/admin/admin_place.php", 1);
	$head[$h][1] = $langs->trans("SettingsPlace");
	$head[$h][2] = 'settings';
	$h++;

	$head[$h][0] = dol_buildpath("/place/admin/room_extrafields.php", 1);
	$head[$h][1] = $langs->trans("RoomAttributes");
	$head[$h][2] = 'attributeroom';
	$h++;

	$head[$h][0] = dol_buildpath("/place/admin/about.php", 1);
	$head[$h][1] = $langs->trans("About");
	$head[$h][2] = 'about';
	$h++;

	// Show more tabs from modules
	// Entries must be declared in modules descriptor with line
	//$this->tabs = array(
	//	'entity:+tabname:Title:@place:/place/mypage.php?id=__ID__'
	//); // to add new tab
	//$this->tabs = array(
	//	'entity:-tabname:Title:@place:/place/mypage.php?id=__ID__'
	//); // to remove a tab
	complete_head_from_modules($conf, $langs, $object, $head, $h, 'place');

	return $head;
}

function placePrepareHead($object)
{
	global $langs, $conf, $user;
	$h = 0;
	$head = array();

	$head[$h][0] = dol_buildpath('/place/fiche.php',1).'?id='.$object->id;
	$head[$h][1] = $langs->trans("PlaceSingular");
    $head[$h][2] = 'place';
	$h++;

	$head[$h][0] = dol_buildpath('/place/building/list.php',1).'?id='.$object->id;
	$head[$h][1] = $langs->trans("Buildings");
    $head[$h][2] = 'buildings';
	$h++;

	// Show more tabs from modules
	// Entries must be declared in modules descriptor with line
	// $this->tabs = array('entity:+tabname:Title:@mymodule:/mymodule/mypage.php?id=__ID__');   to add new tab
	// $this->tabs = array('entity:-tabname:Title:@mymodule:/mymodule/mypage.php?id=__ID__');   to remove a tab
	complete_head_from_modules($conf,$langs,$object,$head,$h,'place');


	return $head;
}

function buildingPrepareHead($object)
{
	global $langs, $conf, $user;
	$h = 0;
	$head = array();

	$head[$h][0] = dol_buildpath('/place/building/fiche.php',1).'?id='.$object->id;
	$head[$h][1] = $langs->trans("Card");
	$head[$h][2] = 'building';
	$h++;


	$head[$h][0] = dol_buildpath('/place/building/floors.php',1).'?id='.$object->id;
	$head[$h][1] = $langs->trans("Floors");
	$head[$h][2] = 'floors';
	$h++;

	$head[$h][0] = dol_buildpath('/place/room/list.php',1).'?building='.$object->id;
	$head[$h][1] = $langs->trans("Rooms");
	$head[$h][2] = 'rooms';
	$h++;

	$head[$h][0] = dol_buildpath('/place/building/document.php',1).'?id='.$object->id;
	$head[$h][1] = $langs->trans("Documents");
	$head[$h][2] = 'document';
	$h++;



	// Show more tabs from modules
	// Entries must be declared in modules descriptor with line
	// $this->tabs = array('entity:+tabname:Title:@mymodule:/mymodule/mypage.php?id=__ID__');   to add new tab
	// $this->tabs = array('entity:-tabname:Title:@mymodule:/mymodule/mypage.php?id=__ID__');   to remove a tab
	complete_head_from_modules($conf,$langs,$object,$head,$h,'building');


	return $head;
}


function roomPrepareHead($object)
{
	global $langs, $conf, $user;
	$h = 0;
	$head = array();

	$head[$h][0] = dol_buildpath('/place/room/card.php',1).'?id='.$object->id;
	$head[$h][1] = $langs->trans("Room");
	$head[$h][2] = 'room';
	$h++;

	// Show more tabs from modules
	// Entries must be declared in modules descriptor with line
	// $this->tabs = array('entity:+tabname:Title:@mymodule:/mymodule/mypage.php?id=__ID__');   to add new tab
	// $this->tabs = array('entity:-tabname:Title:@mymodule:/mymodule/mypage.php?id=__ID__');   to remove a tab
	complete_head_from_modules($conf,$langs,$object,$head,$h,'room');


	return $head;
}
