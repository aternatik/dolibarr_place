<?php
/* Copyright (C) 2013-2016 Jean-François FERRY  <jfefe@aternatik.fr>
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
 *       \file       place/core/ajax/rooms.php
 *       \brief      File to load rooms combobox.
 */
if (!defined('NOTOKENRENEWAL')) {
    define('NOTOKENRENEWAL', '1');
} // Disables token renewal
if (!defined('NOREQUIREMENU')) {
    define('NOREQUIREMENU', '1');
}
//if (! defined('NOREQUIREHTML'))  define('NOREQUIREHTML','1');
if (!defined('NOREQUIREAJAX')) {
    define('NOREQUIREAJAX', '1');
}
//if (! defined('NOREQUIRESOC'))   define('NOREQUIRESOC','1');
//if (! defined('NOREQUIRETRAN'))  define('NOREQUIRETRAN','1');

require_once '../../../../main.inc.php';

$id = GETPOST('id', 'int');
$action = GETPOST('action', 'alpha');
$htmlname = GETPOST('htmlname', 'alpha');

/*
 * View
 */

top_httphead();

//print '<!-- Ajax page called with url '.$_SERVER["PHP_SELF"].'?'.$_SERVER["QUERY_STRING"].' -->'."\n";

// Load original field value
if (!empty($id) && !empty($action) && !empty($htmlname)) {
    if (!class_exists('FormPlace')) {
        require_once '../../class/html.formplace.class.php';
    }

    $form = new FormPlace($db);

    $return = array();

    $return['value'] = $form->selectrooms($id, '', 'fk_resource_room', 0, '', '', 0, '', true);
    $return['num'] = $form->num;
    $return['error'] = $form->error;

    echo json_encode($return);
}
