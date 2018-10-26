<?php
/* Copyright (C) 2014-2018	Jean-François Ferry	<hello+jf@librethic.io>
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
 * \file /place/admin/room_extrafield.php
 * \ingroup place
 * \brief Page to setup extra fields of rooms.
 */
$res = @include '../../main.inc.php'; // For root directory
if (!$res) {
    $res = @include '../../../main.inc.php';
} // For "custom" directory

// Libraries
require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';
require_once '../lib/place.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';

if (!$user->admin) {
    accessforbidden();
}

$langs->load('admin');
$langs->load('other');
$langs->load('place@place');

$extrafields = new ExtraFields($db);
$form = new Form($db);

// List of supported format
$tmptype2label = ExtraFields::$type2label;
$type2label = array('');
foreach ($tmptype2label as $key => $val) {
    $type2label[$key] = $langs->trans($val);
}

$action = GETPOST('action', 'alpha');
$attrname = GETPOST('attrname', 'alpha');
$elementtype = 'place_room'; // Must be the $table_element of the class that manage extrafield

if (!$user->admin) {
    accessforbidden();
}

    /*
 * Actions
 */

require DOL_DOCUMENT_ROOT.'/core/actions_extrafields.inc.php';

/*
 * View
 */

llxHeader('', $langs->trans('PlaceSetup'));
$linkback = '<a href="'.DOL_URL_ROOT.'/admin/modules.php">'.$langs->trans('BackToModuleList').'</a>';
print load_fiche_titre($langs->trans('PlaceSetup'), $linkback, 'setup');

// Configuration header
$head = placeAdminPrepareHead();
dol_fiche_head($head, 'attributeroom', $langs->trans('Module110110Name'), 0, 'place@place');
echo "<br>\n";

echo $langs->trans('DefineHereComplementaryAttributes', $langs->transnoentitiesnoconv('PlaceRoom')).'<br>'."\n";
echo '<br>';

// Load attribute_label
$extrafields->fetch_name_optionals_label($elementtype);

echo '<table summary="listofattributes" class="noborder" width="100%">';

echo '<tr class="liste_titre">';
echo '<td>'.$langs->trans('Label').'</td>';
echo '<td>'.$langs->trans('AttributeCode').'</td>';
echo '<td>'.$langs->trans('Type').'</td>';
echo '<td align="right">'.$langs->trans('Size').'</td>';
echo '<td align="center">'.$langs->trans('Unique').'</td>';
echo '<td align="center">'.$langs->trans('Required').'</td>';
echo '<td width="80">&nbsp;</td>';
echo "</tr>\n";

$var = true;
foreach ($extrafields->attribute_type as $key => $value) {
    $var = !$var;
    echo '<tr '.$bc [$var].'>';
    echo '<td>'.$extrafields->attribute_label [$key]."</td>\n";
    echo '<td>'.$key."</td>\n";
    echo '<td>'.$type2label [$extrafields->attribute_type [$key]]."</td>\n";
    echo '<td align="right">'.$extrafields->attribute_size [$key]."</td>\n";
    echo '<td align="center">'.yn($extrafields->attribute_unique [$key])."</td>\n";
    echo '<td align="center">'.yn($extrafields->attribute_required [$key])."</td>\n";
    echo '<td align="right"><a href="'.$_SERVER ['PHP_SELF'].'?action=edit&attrname='.$key.'">'.img_edit().'</a>';
    echo '&nbsp; <a href="'.$_SERVER ['PHP_SELF']."?action=delete&attrname=$key\">".img_delete()."</a></td>\n";
    echo '</tr>';
}

echo '</table>';

dol_fiche_end();

// Buttons
if ($action != 'create' && $action != 'edit') {
    echo '<div class="tabsAction">';
    echo '<a class="butAction" href="'.$_SERVER ['PHP_SELF'].'?action=create">'.$langs->trans('NewAttribute').'</a>';
    echo '</div>';
}

/* ************************************************************************** */

/* Creation d'un champ optionnel											  */

/* ************************************************************************** */

if ($action == 'create') {
    echo '<br>';
    print_titre($langs->trans('NewAttribute'));

    require DOL_DOCUMENT_ROOT.'/core/tpl/admin_extrafields_add.tpl.php';
}

/* ************************************************************************** */

/* Edition d'un champ optionnel                                               */

/* ************************************************************************** */
if ($action == 'edit' && !empty($attrname)) {
    echo '<br>';
    print_titre($langs->trans('FieldEdition', $attrname));

    require DOL_DOCUMENT_ROOT.'/core/tpl/admin_extrafields_edit.tpl.php';
}

llxFooter();
$db->close();
