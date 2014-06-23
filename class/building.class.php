<?php
/* Copyright (C) 2013	Jean-François Ferry	<jfefe@aternatik.fr>
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
 *  \file       place/class/building.class.php
 *  \ingroup    place
 *  \brief      This file is an example for a CRUD class file (Create/Read/Update/Delete)
 */

// Put here all includes required by your class file
require_once "place.class.php";
//require_once(DOL_DOCUMENT_ROOT."/product/class/product.class.php");


/**
 *	Put here description of your class
 */
class Building extends Place
{
	var $db;							//!< To store db handler
	var $error;							//!< To return error code (or message)
	var $errors=array();				//!< To return several error codes (or messages)
	var $element='building';			//!< Id that identify managed objects
	var $table_element='place_building';		//!< Name of table without prefix where object is stored

    var $id;

	var $entity;
	var $ref;
	var $label;
	var $fk_place;
	var $description;
	var $lat;
	var $lng;
	var $note_public;
	var $note_private;
	var $fk_user_creat;
	var $tms='';




    /**
     *  Constructor
     *
     *  @param	DoliDb		$db      Database handler
     */
    function __construct($db)
    {
        $this->db = $db;
        return 1;
    }


    /**
     *  Create object into database
     *
     *  @param	User	$user        User that creates
     *  @param  int		$notrigger   0=launch triggers after, 1=disable triggers
     *  @return int      		   	 <0 if KO, Id of created object if OK
     */
    function create($user, $notrigger=0)
    {
    	global $conf, $langs;
		$error=0;

		// Clean parameters

		if (isset($this->entity)) $this->entity=trim($this->entity);
		if (isset($this->ref)) $this->ref=trim($this->ref);
		if (isset($this->label)) $this->label=trim($this->label);
		if (isset($this->fk_place)) $this->fk_place=trim($this->fk_place);
		if (isset($this->description)) $this->description=trim($this->description);
		if (isset($this->lat)) $this->lat=trim($this->lat);
		if (isset($this->lng)) $this->lng=trim($this->lng);
		if (isset($this->note_public)) $this->note_public=trim($this->note_public);
		if (isset($this->note_private)) $this->note_private=trim($this->note_private);
		if (isset($this->fk_user_creat)) $this->fk_user_creat=trim($this->fk_user_creat);



		// Check parameters
		// Put here code to add control on parameters values

        // Insert request
		$sql = "INSERT INTO ".MAIN_DB_PREFIX."place_building(";

		$sql.= "entity,";
		$sql.= "ref,";
		$sql.= "label,";
		$sql.= "fk_place,";
		$sql.= "description,";
		$sql.= "lat,";
		$sql.= "lng,";
		$sql.= "note_public,";
		$sql.= "note_private,";
		$sql.= "fk_user_creat";


        $sql.= ") VALUES (";

		$sql.= " ".getEntity('building').",";
		$sql.= " ".(! isset($this->ref)?'NULL':"'".$this->db->escape($this->ref)."'").",";
		$sql.= " ".(! isset($this->label)?'NULL':"'".$this->db->escape($this->label)."'").",";
		$sql.= " ".(empty($this->fk_place)?'0':$this->fk_place).",";
		$sql.= " ".(! isset($this->description)?'NULL':"'".$this->db->escape($this->description)."'").",";
		$sql.= " ".(empty($this->lat)?'NULL':"'".$this->lat."'").",";
		$sql.= " ".(empty($this->lng)?'NULL':"'".$this->lng."'").",";
		$sql.= " ".(! isset($this->note_public)?'NULL':"'".$this->db->escape($this->note_public)."'").",";
		$sql.= " ".(! isset($this->note_private)?'NULL':"'".$this->db->escape($this->note_private)."'").",";
		$sql.= " ".$user->id."";


		$sql.= ")";

		$this->db->begin();

	   	dol_syslog(get_class($this)."::create sql=".$sql, LOG_DEBUG);
        $resql=$this->db->query($sql);
    	if (! $resql) { $error++; $this->errors[]="Error ".$this->db->lasterror(); }

		if (! $error)
        {
            $this->id = $this->db->last_insert_id(MAIN_DB_PREFIX."place_building");

			if (! $notrigger)
			{
	            // Uncomment this and change MYOBJECT to your own tag if you
	            // want this action calls a trigger.

	            //// Call triggers
	            //include_once DOL_DOCUMENT_ROOT . '/core/class/interfaces.class.php';
	            //$interface=new Interfaces($this->db);
	            //$result=$interface->run_triggers('MYOBJECT_CREATE',$this,$user,$langs,$conf);
	            //if ($result < 0) { $error++; $this->errors=$interface->errors; }
	            //// End call triggers
			}
        }

        // Commit or rollback
        if ($error)
		{
			foreach($this->errors as $errmsg)
			{
	            dol_syslog(get_class($this)."::create ".$errmsg, LOG_ERR);
	            $this->error.=($this->error?', '.$errmsg:$errmsg);
			}
			$this->db->rollback();
			return -1*$error;
		}
		else
		{
			$this->db->commit();
            return $this->id;
		}
    }


    /**
     *  Load object in memory from the database
     *
     *  @param	int		$id    Id object
     *  @return int          	<0 if KO, >0 if OK
     */
    function fetch($id)
    {
    	global $langs;
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
        $sql.= " WHERE t.rowid = ".$id;

    	dol_syslog(get_class($this)."::fetch sql=".$sql, LOG_DEBUG);
        $resql=$this->db->query($sql);
        if ($resql)
        {
            if ($this->db->num_rows($resql))
            {
                $obj = $this->db->fetch_object($resql);

                $this->id    = $obj->rowid;

				$this->entity = $obj->entity;
				$this->ref = $obj->ref;
				$this->label = $obj->label;
				$this->fk_place = $obj->fk_place;
				$this->description = $obj->description;
				$this->lat = $obj->lat;
				$this->lng = $obj->lng;
				$this->note_public = $obj->note_public;
				$this->note_private = $obj->note_private;
				$this->fk_user_creat = $obj->fk_user_creat;
				$this->tms = $this->db->jdate($obj->tms);

				// Retrieve place info
				$this->fetch_place();

            }
            $this->db->free($resql);

            return 1;
        }
        else
        {
      	    $this->error="Error ".$this->db->lasterror();
            dol_syslog(get_class($this)."::fetch ".$this->error, LOG_ERR);
            return -1;
        }
    }

    /**
     *	Load all objects into $this->lines
     *
     *  @param	string		$sortorder    sort order
     *  @param	string		$sortfield    sort field
     *  @param	int			$limit		  limit page
     *  @param	int			$offset    	  page
     *  @param	array		$filter    	  filter output
     *  @return int          	<0 if KO, >0 if OK
     */
    function fetch_all($sortorder, $sortfield, $limit, $offset, $filter='')
    {
    	global $conf;
    	$sql="SELECT ";
    	$sql.= " t.rowid,";
    	$sql.= " t.ref,";
    	//$sql.= " t.fk_soc,";
    	$sql.= " t.fk_place,";
    	$sql.= " t.description,";
    	$sql.= " t.lat,";
    	$sql.= " t.lng,";
    	$sql.= " t.note_public,";
    	$sql.= " t.note_private,";
    	$sql.= " t.fk_user_creat,";
    	$sql.= " t.tms";
    	$sql.= ' FROM '.MAIN_DB_PREFIX .'place_building as t ';
    	$sql.= " WHERE t.entity IN (".getEntity('place').")";

    	//Manage filter
    	if (!empty($filter)){
    		foreach($filter as $key => $value) {
    			if (strpos($key,'date')) {
    				$sql.= ' AND '.$key.' = \''.$this->db->idate($value).'\'';
    			}
    			else {
    				$sql.= ' AND '.$key.' LIKE \'%'.$value.'%\'';
    			}
    		}
    	}
    	$sql.= " GROUP BY t.rowid, t.ref";
    	$sql.= " ORDER BY $sortfield $sortorder " . $this->db->plimit( $limit + 1 ,$offset);
    	dol_syslog(get_class($this)."::fetch_all sql=".$sql, LOG_DEBUG);
    	$resql=$this->db->query($sql);
    	if ($resql)
    	{
    		$num = $this->db->num_rows($resql);
    		if ($num)
    		{
    			$i = 0;
    			while ($i < $num)
    			{
    				$obj = $this->db->fetch_object($resql);
    				$line = new Place($this->db);
    				$line->id			=	$obj->rowid;
    				$line->ref				=	$obj->ref;
    				//$line->fk_soc			=	$obj->fk_soc;
    				$line->fk_place			=	$obj->fk_place;
    				$line->description		=	$obj->description;
    				$line->lat				=	$obj->lat;
    				$line->lng				=	$obj->lng;
    				$line->fk_user_create	=	$obj->fk_user_create;

    				$this->lines[$i] = $line;
    				$i++;
    			}
    			$this->db->free($resql);
    		}
    		return $num;
    	}
    	else
    	{
    		$this->error = $this->db->lasterror();
    		return -1;
    	}
    }

    /**
     *    	Load the place of object from id $this->fk_place into this->place
     *
     *		@return		int					<0 if KO, >0 if OK
     */
    function fetch_place()
    {
    	global $conf;

    	if (empty($this->fk_place)) return 0;

    	$place = new Place($this->db);
    	$result=$place->fetch($this->fk_place);
    	$this->place = $place;

    	return $result;
    }


    /**
     *  Update object into database
     *
     *  @param	User	$user        User that modifies
     *  @param  int		$notrigger	 0=launch triggers after, 1=disable triggers
     *  @return int     		   	 <0 if KO, >0 if OK
     */
    function update($user=0, $notrigger=0)
    {
    	global $conf, $langs;
		$error=0;

		// Clean parameters

		if (isset($this->entity)) $this->entity=trim($this->entity);
		if (isset($this->ref)) $this->ref=trim($this->ref);
		if (isset($this->label)) $this->label=trim($this->label);
		if (isset($this->fk_place)) $this->fk_place=trim($this->fk_place);
		if (isset($this->description)) $this->description=trim($this->description);
		if (isset($this->lat)) $this->lat=trim($this->lat);
		if (isset($this->lng)) $this->lng=trim($this->lng);
		if (isset($this->note_public)) $this->note_public=trim($this->note_public);
		if (isset($this->note_private)) $this->note_private=trim($this->note_private);
		if (isset($this->fk_user_creat)) $this->fk_user_creat=trim($this->fk_user_creat);



		// Check parameters
		// Put here code to add a control on parameters values

        // Update request
        $sql = "UPDATE ".MAIN_DB_PREFIX."place_building SET";

		$sql.= " entity=".(isset($this->entity)?$this->entity:"null").",";
		$sql.= " ref=".(isset($this->ref)?"'".$this->db->escape($this->ref)."'":"null").",";
		$sql.= " label=".(isset($this->label)?"'".$this->db->escape($this->label)."'":"null").",";
		$sql.= " fk_place=".(isset($this->fk_place)?$this->fk_place:"null").",";
		$sql.= " description=".(isset($this->description)?"'".$this->db->escape($this->description)."'":"null").",";
		$sql.= " lat=".(isset($this->lat)?$this->lat:"null").",";
		$sql.= " lng=".(isset($this->lng)?$this->lng:"null").",";
		$sql.= " note_public=".(isset($this->note_public)?"'".$this->db->escape($this->note_public)."'":"null").",";
		$sql.= " note_private=".(isset($this->note_private)?"'".$this->db->escape($this->note_private)."'":"null").",";
		$sql.= " fk_user_creat=".(isset($this->fk_user_creat)?$this->fk_user_creat:"null").",";
		$sql.= " tms=".(dol_strlen($this->tms)!=0 ? "'".$this->db->idate($this->tms)."'" : 'null')."";


        $sql.= " WHERE rowid=".$this->id;

		$this->db->begin();

		dol_syslog(get_class($this)."::update sql=".$sql, LOG_DEBUG);
        $resql = $this->db->query($sql);
    	if (! $resql) { $error++; $this->errors[]="Error ".$this->db->lasterror(); }

		if (! $error)
		{
			if (! $notrigger)
			{
	            // Uncomment this and change MYOBJECT to your own tag if you
	            // want this action calls a trigger.

	            //// Call triggers
	            //include_once DOL_DOCUMENT_ROOT . '/core/class/interfaces.class.php';
	            //$interface=new Interfaces($this->db);
	            //$result=$interface->run_triggers('MYOBJECT_MODIFY',$this,$user,$langs,$conf);
	            //if ($result < 0) { $error++; $this->errors=$interface->errors; }
	            //// End call triggers
	    	}
		}

        // Commit or rollback
		if ($error)
		{
			foreach($this->errors as $errmsg)
			{
	            dol_syslog(get_class($this)."::update ".$errmsg, LOG_ERR);
	            $this->error.=($this->error?', '.$errmsg:$errmsg);
			}
			$this->db->rollback();
			return -1*$error;
		}
		else
		{
			$this->db->commit();
			return 1;
		}
    }


 	/**
	 *  Delete object in database
	 *
     *	@param  User	$user        User that deletes
     *  @param  int		$notrigger	 0=launch triggers after, 1=disable triggers
	 *  @return	int					 <0 if KO, >0 if OK
	 */
	function delete($user, $notrigger=0)
	{
		global $conf, $langs;
		$error=0;

		$this->db->begin();

		if (! $error)
		{
			if (! $notrigger)
			{
				// Uncomment this and change MYOBJECT to your own tag if you
		        // want this action calls a trigger.

		        //// Call triggers
		        //include_once DOL_DOCUMENT_ROOT . '/core/class/interfaces.class.php';
		        //$interface=new Interfaces($this->db);
		        //$result=$interface->run_triggers('MYOBJECT_DELETE',$this,$user,$langs,$conf);
		        //if ($result < 0) { $error++; $this->errors=$interface->errors; }
		        //// End call triggers
			}
		}

		if (! $error)
		{
    		$sql = "DELETE FROM ".MAIN_DB_PREFIX."place_building";
    		$sql.= " WHERE rowid=".$this->id;

    		dol_syslog(get_class($this)."::delete sql=".$sql);
    		$resql = $this->db->query($sql);
        	if (! $resql) { $error++; $this->errors[]="Error ".$this->db->lasterror(); }
		}

        // Commit or rollback
		if ($error)
		{
			foreach($this->errors as $errmsg)
			{
	            dol_syslog(get_class($this)."::delete ".$errmsg, LOG_ERR);
	            $this->error.=($this->error?', '.$errmsg:$errmsg);
			}
			$this->db->rollback();
			return -1*$error;
		}
		else
		{
			$this->db->commit();
			return 1;
		}
	}


	/**
	 *  Show html array with informations of object
	 *
	 *  @return	void
	 */
	function printInfoTable()
	{
		global $conf,$langs;
		print '<table width="100%" class="border">';

		// Ref
		print '<tr>';
		print '<td  width="20%">' . $langs->trans("BuildingFormLabel_ref") . '</td>';
		print '<td   width="30%">';
		print $this->ref;
		print '</td>';
		print '</tr>';

		// Description
		print '<tr>';
		print '<td  width="20%">' . $langs->trans("Description") . '</td>';
		print '<td   width="30%">';
		print $this->description;
		print '</td>';
		print '</tr>';

		// Latitude
		print '<tr>';
		print '<td  width="20%">' . $langs->trans("Latitude") . '</td>';
		print '<td   width="30%">';
		print $this->lat;
		print '</td>';
		print '</tr>';

		// Longitude
		print '<tr>';
		print '<td  width="20%">' . $langs->trans("Longitude") . '</td>';
		print '<td   width="30%">';
		print $this->lng;
		print '</td>';
		print '</tr>';

		// Link to OSM
		print '<tr>';
		print '<td  width="20%">' . $langs->trans("OSMLink") . '</td>';
		print '<td   width="30%">';
		print '<a href="http://openstreetmap.org/?mlat='.$this->lat.'&amp;mlon='.$this->lng.'&amp;zoom='.$conf->global->PLACE_DEFAULT_ZOOM_FOR_MAP.'" target="_blank">'.$langs->trans("ShowInOSM").'</a>';
		print '</td>';
		print '</tr>';


		print '</table>';
	}

	/**
	 *  Show html array with informations of object
	 *
	 *  @return	void
	 */
	function printShortInfoTable()
	{
		global $conf,$langs;
		print '<table width="100%" class="border">';

		// Ref
		print '<tr>';
		print '<td  width="20%">' . $langs->trans("BuildingFormLabel_ref") . '</td>';
		print '<td   width="30%">';
		print $this->ref;
		print '</td>';
		print '</tr>';
		
		// Description
		print '<tr>';
		print '<td  width="20%">' . $langs->trans("Description") . '</td>';
		print '<td   width="30%">';
		print $this->description;
		print '</td>';
		print '</tr>';

		print '</table>';
	}

	/**
	 * Function to show floor list from database read
	 */
	function show_floor_list($fk_building)
	{
		global $langs;

		if ( ! $fk_building > 0)
		{
			return '';
		}
		$out = '';
		$langs->load('place@place');

		$list_floor = $this->getFloorList($fk_building);
		if( is_array($list_floor) && sizeof($list_floor) > 0)
		{
			$out .= '<table width="100%;" class="noborder">';
			//$out .=  '<table class="noborder">'."\n";
		    $out .=  '<tr class="liste_titre">';
		    $out .= '<th class="liste_titre">'.$langs->trans('FloorNumber').'</th>';
		    $out .= '<th class="liste_titre">'.$langs->trans('FloorOrder').'</th>';
		    $out .=  '</tr>';

			foreach ($list_floor as $key => $floor)
			{
			    $out .= '<tr>';
			    $out .= '<td>';
			    $out .= $floor->ref;
				$out .= '</td>';
				$out .= '<td>';
				$out .= $floor->pos;
				$out .= '</td>';

				$out .= '</tr>';
			}

			$out .= '</table>';

		}
		else if($list_floor < 0)
			setEventMessage($this->error);
		else {
			$out.='<div class="info">'.$langs->trans('NoFloorFoundForThisBuilding').'</div>';
		}

		return $out;

	}

	/**
	 * Function to show floor select list from database read
	 */
	function show_select_floor($fk_building,$htmlname,$id_floor='')
	{
		global $langs;

		if ( ! $fk_building > 0)
		{
			return '';
		}
		$out = '';
		$langs->load('place@place');

		$list_floor = $this->getFloorList($fk_building);

		if( is_array($list_floor) && sizeof($list_floor) > 0)
		{
			$out .= '<select name="'.$htmlname.'">';


			foreach ($list_floor as $key => $floor)
			{
				$out .= '<option value="'.$floor->id.'"';
				if($id_floor == $floor->id)
					$out .= ' selected="selected"';
				$out .= '>';


				$out .= $floor->pos.' - '.$floor->ref;

				$out .= '</option>';
			}

			$out .= '</select>';

		}
		else if($list_floor < 0)
			setEventMessage($this->error);
		else {
			$out.='<div class="info">'.$langs->trans('NoFloorFoundForThisBuilding').'</div>';
		}

		return $out;

	}


	/**
	 *	Return list of floors for fk_building
	 *
	 *	@param		int		$socid		To filter on a particular third party
	 * 	@return		array				Business list array
	 */
	function getFloorList($fk_building)
	{
		global $conf;

		$error='';
		if(! $fk_building>0)
		{
			$error++;
			$this->error = '$fk_building must be provided';
		}

		if(!$error)
		{
			$floor = array();

			$sql = 'SELECT rowid, ref, pos, fk_building';
			$sql.= ' FROM '.MAIN_DB_PREFIX .'place_floor';
			$sql.= " WHERE entity = ".$conf->entity;
			$sql.= " AND fk_building = ".$fk_building;
			$sql.= " ORDER BY pos ASC";
			dol_syslog(get_class($this)."::getFloorList sql=".$sql, LOG_DEBUG);
			$resql=$this->db->query($sql);
			if ($resql)
			{
				$num = $this->db->num_rows($resql);
				if ($num)
				{
					$i = 0;
					while ($i < $num)
					{
						$obj = $this->db->fetch_object($resql);

						$floorstatic = new stdClass($this->db);
						$floorstatic->id				=	$obj->rowid;
						$floorstatic->ref				=	$obj->ref;
						$floorstatic->pos				=	$obj->pos;
						$floorstatic->fk_building		=	$obj->fk_building;

						$floor[$i] = $floorstatic;
						$i++;
					}
				}
				return $floor;
			}
			else
			{
				$this->error = $this->db->lasterror();
				return -1;
			}

		}
		else
		{
			return -1;
		}

	}

	/**
	 * Function to show floors form to add/edit database
	 */
	function show_floor_form($fk_building,$show_link_delete=0)
	{
		global $langs;

		$formstatic = new Form($this->db);

		$langs->load('place@place');

		$list_floor = $this->getFloorList($fk_building);

		if( is_array($list_floor) && count($list_floor) > 0)
		{
			foreach ($list_floor as $key => $floor)
			{
				$floor_id[]			= $floor->id;
				$floor_ref[] 		= $floor->ref;
				$floor_pos[] 		= $floor->pos;
			}
		}
		else
		{
			$floor_ref		= GETPOST('floor_ref');
			$floor_pos		= GETPOST('floor_pos');
		}

		$out .= '<div id="form_floor" class="dol_form">';


		// Show existent
		$i=0;
		while(isset($floor_ref[$i]))
		{
			$out .= '<ul class="edit_floor">';
			$out .= '<li class="edit">';
			$out .= '<label>'.$langs->trans("FloorNumber");
			$out .= '</label>';
			$out .= '<input type="text" name="floor_ref[]" value="'.$floor_ref[$i].'"/>';
			if($show_link_delete)
				$out .= '<a href="'.$_SERVER['PHP_SELF'].'?action=delete_floor&amp;id='.$fk_building.'&amp;id_floor='. $floor_id[$i].'">'.img_picto($langs->trans('Delete'),'delete').'</a> ';
			$out .= '</li>';


			$out .= '<li class="edit">';
			$out .= '<label for="carac">'.$langs->trans("FloorOrder").'</label>';
			$out .= '<input type="text" name="floor_pos[]"  size="6" value="'.$floor_pos[$i].'"/>';
			$out .= '</li>';

			$out .= '</ul>';

			$i++;
		}

		$out .= '</div>';
		$out .= '<p><a href="#" id="addfloor">'.img_picto('','edit_add').' '.$langs->trans('AddFloor').'</a></p>';
		//$out .= '</form>';

		return $out;

	}

	/**
	 *	Add/Update floors data by $this->floors
	 *
	 *	@return int <0 if KO, >0 if OK
	 */
	function insertFloors($user)
	{
		global $conf, $langs;

		if (sizeof($this->floors) > 0)
		{
			dol_syslog(get_class($this)."::insertFloors id=".$this->id, LOG_DEBUG);

			$error=0;
			$created=array();

			$result=$this->deleteFloorsForBuilding();
			if(!result)
			{
				$error++;
			}

			$this->db->begin();

			foreach($this->floors as $key => $floor)
			{
				// Insert request
				$sql = "INSERT INTO ".MAIN_DB_PREFIX."place_floor (";
				$sql.= " ref,";
				$sql.= " fk_user_create,";
				$sql.= " entity,";
				$sql.= " pos,";
				$sql.= " fk_building";
				$sql.= ") VALUES (";
				$sql.= " '".$floor['ref']."',";
				$sql.= " '".$user->id."',";
				$sql.= " '".$conf->entity."',";
				$sql.= " '".$floor['pos']."',";
				$sql.= " '".$floor['fk_building']."'";
				//...
				$sql.= ")";

				dol_syslog(get_class($this)."::insertFloors sql=".$sql, LOG_DEBUG);
				$resql=$this->db->query($sql);
				if (! $resql) { $error++; $this->errors[]="Error ".$this->db->lasterror(); }

				if (! $error)
				{
					$created[] = $this->db->last_insert_id(MAIN_DB_PREFIX."place_floor");
				}
			}

			// Commit or rollback
			if ($error)
			{
				foreach($this->errors as $errmsg)
				{
					dol_syslog(get_class($this)."::insertFloors ".$errmsg, LOG_ERR);
					$this->error.=($this->error?', '.$errmsg:$errmsg);
				}
				$this->db->rollback();
				return -1*$error;
			}
			else
			{
				$this->db->commit();
				$result = sizeof($created);
			}
		}

		return $result;
	}

	/**
	 *  Delete floors for object in database
	 *
	 *  @return	int					 <0 if KO, >0 if OK
	 */
	function deleteFloorsForBuilding()
	{
		global $conf, $langs;
		$error=0;

		$this->db->begin();

		if (! $error)
		{
			$sql = "DELETE FROM ".MAIN_DB_PREFIX."place_floor";
			$sql.= " WHERE fk_building=".$this->id;

			dol_syslog(get_class($this)."::deleteFloorsForBuilding sql=".$sql);
			$resql = $this->db->query($sql);
			if (! $resql) { $error++; $this->errors[]="Error ".$this->db->lasterror(); }
		}

		// Commit or rollback
		if ($error)
		{
			foreach($this->errors as $errmsg)
			{
				dol_syslog(get_class($this)."::deleteFloorsForBuilding ".$errmsg, LOG_ERR);
				$this->error.=($this->error?', '.$errmsg:$errmsg);
			}
			$this->db->rollback();
			return -1*$error;
		}
		else
		{
			$this->db->commit();
			return 1;
		}
	}

	/**
	 *  Delete unique floor in database
	 *
	 *  @param  int		$id	 		id of floor to delete
	 *  @return	int					<0 if KO, >0 if OK
	 */
	function deleteFloor($id)
	{
		global $conf, $langs;
		$error=0;

		$this->db->begin();

		if (! $error)
		{
			$sql = "DELETE FROM ".MAIN_DB_PREFIX."place_floor";
			$sql.= " WHERE rowid=".$id;

			dol_syslog(get_class($this)."::deleteFloor sql=".$sql);
			$resql = $this->db->query($sql);
			if (! $resql) { $error++; $this->errors[]="Error ".$this->db->lasterror(); }
		}

		// Commit or rollback
		if ($error)
		{
			foreach($this->errors as $errmsg)
			{
				dol_syslog(get_class($this)."::deleteFloor ".$errmsg, LOG_ERR);
				$this->error.=($this->error?', '.$errmsg:$errmsg);
			}
			$this->db->rollback();
			return -1*$error;
		}
		else
		{
			$this->db->commit();
			return 1;
		}
	}


	/**
	 *	Load an object from its id and create a new one in database
	 *
	 *	@param	int		$fromid     Id of object to clone
	 * 	@return	int					New id of clone
	 */
	function createFromClone($fromid)
	{
		global $user,$langs;

		$error=0;

		$object=new Placebuilding($this->db);

		$this->db->begin();

		// Load source object
		$object->fetch($fromid);
		$object->id=0;
		$object->statut=0;

		// Clear fields
		// ...

		// Create clone
		$result=$object->create($user);

		// Other options
		if ($result < 0)
		{
			$this->error=$object->error;
			$error++;
		}

		if (! $error)
		{


		}

		// End
		if (! $error)
		{
			$this->db->commit();
			return $object->id;
		}
		else
		{
			$this->db->rollback();
			return -1;
		}
	}


	/**
	 *	Initialise object with example values
	 *	Id must be 0 if object instance is a specimen
	 *
	 *	@return	void
	 */
	function initAsSpecimen()
	{
		$this->id=0;

		$this->entity='';
		$this->ref='';
		$this->label='';
		$this->fk_place='';
		$this->description='';
		$this->lat='';
		$this->lng='';
		$this->note_public='';
		$this->note_private='';
		$this->fk_user_creat='';
		$this->tms='';


	}

}
?>
