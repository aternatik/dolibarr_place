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

        	/*
        	 *  View Location and room into actioncomm card
        	 */
        	if($action == '')
        	{
        		$form = new Form($db);
        		if(!class_exists('Resource'))
        			dol_include_once('resource/class/resource.class.php');
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
        	}

        	//print $out;

        }

        $this->results=array('myreturn'=>'');
        $this->resprints=$out;

        return 0;
    }

    function doActions($parameters, &$object, &$action, $hookmanager)
    {
    	global $langs, $db;

    	$langs->load('place@place');
		$langs->load('resource@resource');

    	if (in_array('element_resource',explode(':',$parameters['context'])))
    	{
			$element_id = GETPOST('element_id','int');
			$element = GETPOST('element','alpha');
			$resource_type = GETPOST('resource_type');

			$busy = GETPOST('busy','int');
			$mandatory = GETPOST('mandatory','int');

    		if($action == 'add_resource_place' && !GETPOST('cancel'))
    		{
	    		dol_include_once('resource/class/resource.class.php');
	    		$resource_stat = new Resource($db);
	    		$res = $resource_stat->add_element_resource($element_id,$element,GETPOST('fk_resource_place'),$resource_type,$busy,$mandatory);

	    		if($res > 0)
	    		{
	    			setEventMessage($langs->trans('ResourceLinkedWithSuccess'),'mesgs');
	    			header("Location: ".$_SERVER['PHP_SELF'].'?element='.$element.'&element_id='.$element_id);
	    			exit;
	    		}
	    		else
	    		{
	    			setEventMessage($langs->trans('ErrorWhenLinkingResource'),'errors');
	    			header("Location: ".$_SERVER['PHP_SELF'].'?mode=add&resource_type='.$resource_type.'&element='.$element.'&element_id='.$element_id);
	    			exit;
	    		}

    		}

    		if($action == 'add_resource_room' && !GETPOST('cancel'))
    		{
    			// Init du tableau des resources pour l'element
    			dol_include_once('resource/class/resource.class.php');
    			$resource_stat = new Resource($db);

    			$resources = array('place@place' => GETPOST('fk_resource_place'),'room@place' => GETPOST('fk_resource_room'));
    			$error=$number_resources=0;

    			foreach($resources as $resource_element => $resource_id)
    			{
					if($element && $resource_id > 0)
					{
						$res = $resource_stat->add_element_resource($element_id,$element,$resource_id,$resource_element,$busy,$mandatory);
						if($res > 0)
						{
							$number_resources++;
						}
						else
						{
							$error++;
						}
					}
					else
					{
						setEventMessage('ErrorNoId','errors');
						$error++;
					}
    			}

    			if(!$error && $number_resources > 0)
    			{
    				setEventMessage($langs->trans('ResourcesLinkedWithSuccess',$number_resources),'mesgs');
    				header("Location: ".$_SERVER['PHP_SELF'].'?element='.$element.'&element_id='.$element_id);
    			}
    			else
    			{
    				setEventMessage($langs->trans('ErrorWhenLinkingResources'),'errors');
    				header("Location: ".$_SERVER['PHP_SELF'].'?mode=add&resource_type='.$resource_type.'&element='.$element.'&element_id='.$element_id);
    			}
    		}
    	}
    }
}
