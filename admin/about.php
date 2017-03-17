<?php
/* Module to manage locations, buildings, floors and rooms into Dolibarr ERP/CRM
 * Copyright (C) 2013-2016	Jean-François Ferry	<jfefe@aternatik.fr>
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
 * 	\file		place/admin/about.php
 * 	\ingroup	place
 * 	\brief		This file is about page of place module.
 */
// Dolibarr environment
$res = @include '../../main.inc.php'; // From htdocs directory
if (!$res) {
    $res = @include '../../../main.inc.php'; // From "custom" directory
}

// Libraries
require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';
require_once '../lib/place.lib.php';

dol_include_once('/place/lib/PHP_Markdown_1.0.1o/markdown.php');

//require_once "../class/myclass.class.php";
// Translations
$langs->load('place@place');

// Access control
if (!$user->admin) {
    accessforbidden();
}

// Parameters
$action = GETPOST('action', 'alpha');

/*
 * Actions
 */

/*
 * View
 */
$page_name = 'PlaceAbout';
llxHeader('', $langs->trans($page_name));

// Subheader
$linkback = '<a href="'.DOL_URL_ROOT.'/admin/modules.php">'
    .$langs->trans('BackToModuleList').'</a>';
print load_fiche_titre($langs->trans($page_name), $linkback);

// Configuration header
$head = placeAdminPrepareHead();
dol_fiche_head(
    $head,
    'about',
    $langs->trans('Module110110Name'),
    0,
    'place@place'
);

// About page goes here
echo $langs->trans('PlaceAboutPage');

echo '<br>';

$buffer = file_get_contents(dol_buildpath('/place/README.md', 0));
echo Markdown($buffer);

echo '<br>',
'<a href="'.dol_buildpath('/place/COPYING', 1).'">',
'<img src="'.dol_buildpath('/place/img/gplv3.png', 1).'"/>',
'</a>';

llxFooter();

$db->close();
