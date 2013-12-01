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
    	global $conf,$langs, $db;
        //print_r($parameters);
        //echo "action: ".$action;

        if (in_array('actioncard',explode(':',$parameters['context'])))
        {
        	$out = '';
        	// Show room select list when create an actioncom
        	if($action == 'create')
        	{
        		$form = new Form($db);
        		if(!class_exists('FormPlace'))
        			require_once 'html.formplace.class.php';
        		$formplace = new FormPlace($db);

        		// Place & Room
        		$out .= '<tr><td width="30%" class="nowrap">'.$langs->trans("ActionIntoPlace").'</td><td>';
        		if (GETPOST('fk_resource_place','int') > 0)
        		{
        			if(!class_exists('Place'))
        				require_once 'place.class.php';
        			$room = new Place($db);
        			$room->fetch(GETPOST('fk_resource_place','int'));
        			$out .= $room->getNomUrl(1);
        			$out .= '<input type="hidden" name="fk_resource_place" value="'.GETPOST('fk_resource_place','int').'">';
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

        	/*
        	 *  View Location and room into actioncomm card
        	 */
        	if($action == '')
        	{
        		$form = new Form($db);
        		if(!class_exists('Resource'))
        			require_once 'resource.class.php';
        		$resource = new Resource($db);

        		$resources = $resource->getElementResources($object->element,$object->id);
        		$num=count($resources);

        		$i = 0;
        		while ($i < $num) {
        			$var = !$var;

        			// Parse element/subelement (ex: project_task)
        			$module = $element = $subelement = $resources[$i]['resource_type'];

        			// If we ask an resource form external module (instead of default path)
        			if (preg_match('/^([^@]+)@([^@]+)$/i',$resources[$i]['resource_type'],$regs))
        			{
        				$resource 	= $element = $subelement = $regs[1];
        				$module 	= $regs[2];
        			}
        			//print '<br />1. element : '.$element.' - module : '.$module .' - resourec : '.$resource.'<br />';

        			if ( preg_match('/^([^_]+)_([^_]+)/i',$resource,$regs))
        			{
        				$module = $element = $regs[1];
        				$subelement = $regs[2];
        			}
        			$classfile = strtolower($subelement); $classname = ucfirst($subelement);
        			$classpath = $module.'/class';

        			if ($conf->$module->enabled && $element != $object->element)
        			{
        				//print '/'.$classpath.'/'.$classfile.'.class.php';
        				dol_include_once('/'.$classpath.'/'.$classfile.'.class.php');

        				$objectstat = new $classname($db);
        				$ret = $objectstat->fetch($resources[$i]['resource_id']);
        				if ($ret >= 0)
        				{
        					$object->linkedResources[$element][$i]['object'] = $objectstat;
        					$object->linkedResources[$element][$i]['busy'] = $resources[$i]['busy'];
        					$object->linkedResources[$element][$i]['mandatory'] = $resources[$i]['mandatory'];
        				}
        			}
        			$i++;
        		}
        		if(count($object->linkedResources) > 0)
        		{
        			$out .= '<tr class="liste_titre"><td colspan="4">'.$langs->trans('Resources').'</td></tr>';
        		}
        		foreach($object->linkedResources as $obj_type => $resource_array)
        		{
        			$out.= '<tr><td>'.$langs->trans(ucfirst($obj_type)).'</td>';
        			$out.= '<td colspan="3">';
        			foreach($resource_array as $resource)
        			{
        				$out .= $resource['object']->getNomUrl(1).' ';
        			}
        			$out.= '</td></tr>';
        		}
        	}

        	// Show place and room select list when edit an actioncom
        	if($action == 'edit')
        	{
        		$form = new Form($db);
        		if(!class_exists('FormPlace'))
        			require_once 'html.formplace.class.php';
        		$formplace = new FormPlace($db);

        		// Place & Room
        		$out .= '<tr><td width="30%" class="nowrap">'.$langs->trans("ActionIntoPlace").'</td><td>';

        		$events=array();
        		$events[]=array('method' => 'getRooms', 'url' => dol_buildpath('/place/core/ajax/rooms.php',1), 'htmlname' => 'fk_resource_room', 'params' => array());
        		$out .= $formplace->select_place_list(GETPOST('fk_resource_place','int'),'fk_resource_place','',1,1,0,$events);


        		$out .= '</td></tr>';
        		$out .= '<tr><td class="nowrap">'.$langs->trans("ActionIntoRoom").'</td><td>';
        		$out .= $formplace->selectrooms(GETPOST('fk_resource_place','int'),GETPOST('fk_resource_room'),'fk_resource_room',1);
        		$out .= '</td></tr>';
        	}
        	print $out;

        }

        $this->results=array('myreturn'=>'');
        $this->resprints=$out;

        return 0;
    }

    function insertExtraFields($parameters, &$object, &$action, $hookmanager)
    {
    	global $langs, $db;

    	if (in_array('actioncommdao',explode(':',$parameters['context'])))
    	{
    		// Init du tableau des resources pour l'element
    		$object->resources = array('place@place' => GETPOST('fk_resource_place'), 'room@place' => GETPOST('fk_resource_room'));

    		foreach($object->resources as $resource_element => $resource_id)
    		{
    			include_once 'resource.class.php';
    			$resource = new Resource($db);
    			$resource->add_element_resource($object->id,$object->element,$resource_id,$resource_element);
    		}
    	}
    }
}