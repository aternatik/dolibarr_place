<?php
/* Copyright (C) 2013 Jean-François Ferry  <jfefe@aternatik.fr>
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
 *       \file       place/core/ajax/form_room_line.php
 *       \brief      File to load form floor line
 */

if (! defined('NOTOKENRENEWAL')) define('NOTOKENRENEWAL','1'); // Disables token renewal
if (! defined('NOREQUIREMENU'))  define('NOREQUIREMENU','1');
//if (! defined('NOREQUIREHTML'))  define('NOREQUIREHTML','1');
if (! defined('NOREQUIREAJAX'))  define('NOREQUIREAJAX','1');
//if (! defined('NOREQUIRESOC'))   define('NOREQUIRESOC','1');
//if (! defined('NOREQUIRETRAN'))  define('NOREQUIRETRAN','1');

$res='';
$res = @include_once '../../main.inc.php';
if (!$res)
	$res = @include_once '../../../main.inc.php';
if (!$res)
	$res = @include_once '../../../../main.inc.php';

dol_include_once("/place/class/building.class.php");

$id			= GETPOST('id','int');
$element	= GETPOST('element','alpha');
$ids		= GETPOST('ids','int');

$object = new Building($db);

$langs->load('place@place');

/*
 * View
 */

top_httphead();

//print '<!-- Ajax page called with url '.$_SERVER["PHP_SELF"].'?'.$_SERVER["QUERY_STRING"].' -->'."\n";

// Load original field value

	$form = new Form($db);

	$return=array();

	$out = '';

	$out .= '		<ul class="edit_floor">';

	// Floor name
	$out .= '		<li class="edit"><label>'.$langs->trans("FloorNumber").'</label> <input type="text" name="floor_ref[]" /></li>';

	// Position
	$out .= '		<li class="edit"><label>'.$langs->trans("FloorOrder").'</label> <input type="text" name="floor_pos[]" /></li>';

	$out .= '		</ul>';


	$return['value'] 	= $out;
	$return['num']		= "1";
	$return['error']	= "";

	echo json_encode($return);


?>
