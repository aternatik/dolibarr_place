<?php
/* Copyright (C) 2013 Jean-François FERRY  <jfefe@aternatik.fr>
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
 *       \file       place/class/actions_place.class.php
 *       \brief      Place module actions
 */

class ActionsPlace
{
     /** Overloading the formObjectOptions function : replacing the parent's function with the one below
      *  @param      parameters  meta datas of the hook (context, etc...)
      *  @param      object             the object you want to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
      *  @param      action             current action (if set). Generally create or edit or null
      *  @return       void
      */
    function formObjectOptions($parameters, &$object, &$action, $hookmanager)
    {
    	global $langs, $db;
        //print_r($parameters);
        //echo "action: ".$action;


        if (in_array('actioncard',explode(':',$parameters['context'])))
        {
        	// Show room select list when create an actioncom
        	if($action == 'create')
        	{
        		$out = '';
        		$form = new Form($db);
        		if(!class_exists('FormPlace'))
        			require_once 'html.formplace.class.php';
        		$formplace = new FormPlace($db);

        		// Place / Room
        		$out .= '<tr><td width="30%" class="nowrap">'.$langs->trans("ActionIntoPlace").'</td><td>';
        		if (GETPOST('fk_resource_place','int') > 0)
        		{
        			$room = new Room($db);
        			$room->fetch(GETPOST('fk_resource_place','int'));
        			$out .= $room->getNomUrl(1);
        			$out .= '<input type="hidden" name="socid" value="'.GETPOST('fk_resource_place','int').'">';
        		}
        		else
        		{

        			$events=array();
        			$events[]=array('method' => 'getRooms', 'url' => dol_buildpath('/place/core/ajax/rooms.php',1), 'htmlname' => 'fk_resource_room', 'params' => array());
        			$out .= $formplace->select_place_list('','fk_resource_place','',1,1,0,$events);

        		}
        		$out .= '</td></tr>';
        		$out .= '<tr><td class="nowrap">'.$langs->trans("ActionIntoRoom").'</td><td>';
        		$out .= $formplace->selectrooms(GETPOST('fk_resource_place','int'),GETPOST('fk_resource_room'),'fk_resource_room',1);
        		$out .= '</td></tr>';
        	}

        }

        print $out;
        $this->results=array('myreturn'=>'');
        $this->resprints=$out;

        return 0;
    }
}