<?php
/* Module to manage locations, buildings, floors and rooms into Dolibarr ERP/CRM
 * Copyright (C) 2013-2018	Jean-François Ferry	<hello+jf@librethic.io>
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
 *	\brief		This file is library for place module.
 */
function placeAdminPrepareHead()
{
    global $langs, $conf;

    $langs->load('place@place');

    $h = 0;
    $head = array();

    $head[$h][0] = dol_buildpath('/place/admin/admin_place.php', 1);
    $head[$h][1] = $langs->trans('SettingsPlace');
    $head[$h][2] = 'settings';
    ++$h;

    $head[$h][0] = dol_buildpath('/place/admin/room_extrafields.php', 1);
    $head[$h][1] = $langs->trans('RoomAttributes');
    $head[$h][2] = 'attributeroom';
    ++$h;

    $head[$h][0] = dol_buildpath('/place/admin/about.php', 1);
    $head[$h][1] = $langs->trans('About');
    $head[$h][2] = 'about';
    ++$h;

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

    $head[$h][0] = dol_buildpath('/place/card.php', 1).'?id='.$object->id;
    $head[$h][1] = $langs->trans('PlaceSingular');
    $head[$h][2] = 'place';
    ++$h;

    $head[$h][0] = dol_buildpath('/place/document.php', 1).'?id='.$object->id;
    $head[$h][1] = $langs->trans('Documents');
    require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
    require_once DOL_DOCUMENT_ROOT.'/core/class/link.class.php';
    $relativepathwithnofile = dol_sanitizeFileName($object->id.'-'.str_replace(' ', '-', $object->ref)).'/';
    $upload_dir = $conf->place->multidir_output[$object->entity].'/'.$relativepathwithnofile;
    $nbFiles = count(dol_dir_list($upload_dir, 'files', 0, '', '(\.meta|_preview.*\.png)$'));
    $nbLinks=Link::count($object->db, $object->element, $object->id);
    $head[$h][1] = $langs->trans('Documents');
    if (($nbFiles+$nbLinks) > 0) {
        $head[$h][1].= ' <span class="badge">'.($nbFiles+$nbLinks).'</span>';
    }
    $head[$h][2] = 'document';
    ++$h;

    $head[$h][0] = dol_buildpath('/place/building/list.php', 1).'?id='.$object->id;
    $head[$h][1] = $langs->trans('Buildings');
    $nbBuildings = 0;
    $sql = "SELECT COUNT(n.rowid) as nb";
    $sql.= " FROM ".MAIN_DB_PREFIX."place_building as n";
    $sql.= " WHERE fk_place = '".$object->id."'";
    $resql=$object->db->query($sql);
    if ($resql) {
        $num = $object->db->num_rows($resql);
        $i = 0;
        while ($i < $num) {
            $obj = $object->db->fetch_object($resql);
            $nbBuildings=$obj->nb;
            $i++;
        }
    } else {
        dol_print_error($object->db);
    }
    if ($nbBuildings > 0) {
        $head[$h][1].= ' <span class="badge">'.$nbBuildings.'</span>';
    }
    $head[$h][2] = 'buildings';
    ++$h;

    // Show more tabs from modules
    // Entries must be declared in modules descriptor with line
    // $this->tabs = array('entity:+tabname:Title:@mymodule:/mymodule/mypage.php?id=__ID__');   to add new tab
    // $this->tabs = array('entity:-tabname:Title:@mymodule:/mymodule/mypage.php?id=__ID__');   to remove a tab
    complete_head_from_modules($conf, $langs, $object, $head, $h, 'place');

    return $head;
}

function buildingPrepareHead($object)
{
    global $langs, $conf, $user;
    $h = 0;
    $head = array();

    $head[$h][0] = dol_buildpath('/place/building/card.php', 1).'?id='.$object->id;
    $head[$h][1] = $langs->trans('Card');
    $head[$h][2] = 'building';
    ++$h;

    $head[$h][0] = dol_buildpath('/place/building/floors.php', 1).'?id='.$object->id;
    $head[$h][1] = $langs->trans('Floors');
    $nbFloors = 0;
    $sql = "SELECT COUNT(n.rowid) as nb";
    $sql.= " FROM ".MAIN_DB_PREFIX."place_floor as n";
    $sql.= " WHERE fk_building = '".$object->id."'";
    $resql=$object->db->query($sql);
    if ($resql) {
        $num = $object->db->num_rows($resql);
        $i = 0;
        while ($i < $num) {
            $obj = $object->db->fetch_object($resql);
            $nbFloors=$obj->nb;
            $i++;
        }
    } else {
        dol_print_error($object->db);
    }
    if ($nbFloors > 0) {
        $head[$h][1].= ' <span class="badge">'.$nbFloors.'</span>';
    }
    $head[$h][2] = 'floors';
    ++$h;

    $head[$h][0] = dol_buildpath('/place/room/list.php', 1).'?building='.$object->id;
    $head[$h][1] = $langs->trans('Rooms');
    $nbRooms = 0;
    $sql = "SELECT COUNT(n.rowid) as nb";
    $sql.= " FROM ".MAIN_DB_PREFIX."place_room as n";
    $sql.= " WHERE fk_building = '".$object->id."'";
    $resql=$object->db->query($sql);
    if ($resql) {
        $num = $object->db->num_rows($resql);
        $i = 0;
        while ($i < $num) {
            $obj = $object->db->fetch_object($resql);
            $nbRooms=$obj->nb;
            $i++;
        }
    } else {
        dol_print_error($object->db);
    }
    if ($nbRooms > 0) {
        $head[$h][1].= ' <span class="badge">'.$nbRooms.'</span>';
    }
    $head[$h][2] = 'rooms';
    ++$h;

    $head[$h][0] = dol_buildpath('/place/building/document.php', 1).'?id='.$object->id;
    $head[$h][1] = $langs->trans('Documents');
    require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
    require_once DOL_DOCUMENT_ROOT.'/core/class/link.class.php';
    $relativepathwithnofile = dol_sanitizeFileName($object->place->id.'-'.str_replace(' ', '-', $object->place->ref)).'/building/'.dol_sanitizeFileName($object->ref);
    $upload_dir = $conf->place->multidir_output[$object->entity].'/'.$relativepathwithnofile;
    $nbFiles = count(dol_dir_list($upload_dir, 'files', 0, '', '(\.meta|_preview.*\.png)$'));
    $nbLinks=Link::count($object->db, $object->element, $object->id);
    $head[$h][1] = $langs->trans('Documents');
    if (($nbFiles+$nbLinks) > 0) {
        $head[$h][1].= ' <span class="badge">'.($nbFiles+$nbLinks).'</span>';
    }
    $head[$h][2] = 'document';
    ++$h;

    // Show more tabs from modules
    // Entries must be declared in modules descriptor with line
    // $this->tabs = array('entity:+tabname:Title:@mymodule:/mymodule/mypage.php?id=__ID__');   to add new tab
    // $this->tabs = array('entity:-tabname:Title:@mymodule:/mymodule/mypage.php?id=__ID__');   to remove a tab
    complete_head_from_modules($conf, $langs, $object, $head, $h, 'building');

    return $head;
}

function roomPrepareHead($object)
{
    global $langs, $conf, $user;
    $h = 0;
    $head = array();

    $head[$h][0] = dol_buildpath('/place/room/card.php', 1).'?id='.$object->id;
    $head[$h][1] = $langs->trans('Room');
    $head[$h][2] = 'room';
    ++$h;

    $head[$h][0] = dol_buildpath('/place/room/document.php', 1).'?id='.$object->id;
    $head[$h][1] = $langs->trans('Documents');
    $head[$h][2] = 'document';
    ++$h;

    // Show more tabs from modules
    // Entries must be declared in modules descriptor with line
    // $this->tabs = array('entity:+tabname:Title:@mymodule:/mymodule/mypage.php?id=__ID__');   to add new tab
    // $this->tabs = array('entity:-tabname:Title:@mymodule:/mymodule/mypage.php?id=__ID__');   to remove a tab
    complete_head_from_modules($conf, $langs, $object, $head, $h, 'room');

    return $head;
}
