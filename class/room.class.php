<?php
/* Copyright (C) 2007-2012 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) ---Put here your own copyright and developer email---
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
 *  \file       place/class/room.class.php
 *  \ingroup    place
 *  \brief      This file is a CRUD class file (Create/Read/Update/Delete) for room object
 */

// Put here all includes required by your class file
require_once 'place.class.php';
//require_once(DOL_DOCUMENT_ROOT."/societe/class/societe.class.php");
//require_once(DOL_DOCUMENT_ROOT."/product/class/product.class.php");


/**
 *	Room management
 */
class Room extends Place
{
	var $db;							//!< To store db handler
	var $error;							//!< To return error code (or message)
	var $errors=array();				//!< To return several error codes (or messages)
	var $element='room';			//!< Id that identify managed objects
	var $table_element='place_room';		//!< Name of table without prefix where object is stored

    var $id;

	var $entity;
	var $ref;
	var $label;
	var $fk_place;
	var $fk_building;
	var $fk_floor;
	var $type_code;
	var $capacity;
	var $note_public;
	var $note_private;
	var $fk_user_creat;
	var $tms='';

	var $lines = array();




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
    	global $conf, $langs, $hookmanager;
		$error=0;

		// Clean parameters

		if (isset($this->entity)) $this->entity=trim($this->entity);
		if (isset($this->ref)) $this->ref=trim($this->ref);
		if (isset($this->label)) $this->label=trim($this->label);
		if (isset($this->fk_place)) $this->fk_place=trim($this->fk_place);
		if (isset($this->fk_building)) $this->fk_building=trim($this->fk_building);
		if (isset($this->fk_floor)) $this->fk_floor=trim($this->fk_floor);
		if (isset($this->type_code)) $this->type_code=trim($this->type_code);
		if (isset($this->capacity)) $this->capacity=trim($this->capacity);
		if (isset($this->note_public)) $this->note_public=trim($this->note_public);
		if (isset($this->note_private)) $this->note_private=trim($this->note_private);
		if (isset($this->fk_user_creat)) $this->fk_user_creat=trim($this->fk_user_creat);



		// Check parameters
		// Put here code to add control on parameters values

        // Insert request
		$sql = "INSERT INTO ".MAIN_DB_PREFIX."place_room(";

		$sql.= "entity,";
		$sql.= "ref,";
		$sql.= "label,";
		$sql.= "fk_place,";
		$sql.= "fk_building,";
		$sql.= "fk_floor,";
		$sql.= "type_code,";
		$sql.= "capacity,";
		$sql.= "note_public,";
		$sql.= "note_private,";
		$sql.= "fk_user_creat";


        $sql.= ") VALUES (";

		$sql.= " ".$conf->entity.",";
		$sql.= " ".(! isset($this->ref)?'NULL':"'".$this->db->escape($this->ref)."'").",";
		$sql.= " ".(! isset($this->label)?'NULL':"'".$this->db->escape($this->label)."'").",";
		$sql.= " ".(! isset($this->fk_place)?'NULL':"'".$this->fk_place."'").",";
		$sql.= " ".(! isset($this->fk_building)?'NULL':"'".$this->fk_building."'").",";
		$sql.= " ".(! isset($this->fk_floor)?'NULL':"'".$this->fk_floor."'").",";
		$sql.= " ".(! isset($this->type_code)?'NULL':"'".$this->type_code."'").",";
		$sql.= " ".(! isset($this->capacity)?'NULL':"'".$this->capacity."'").",";
		$sql.= " ".(! isset($this->note_public)?'NULL':"'".$this->db->escape($this->note_public)."'").",";
		$sql.= " ".(! isset($this->note_private)?'NULL':"'".$this->db->escape($this->note_private)."'").",";
		$sql.= " ".$user->id;


		$sql.= ")";

		$this->db->begin();

		dol_syslog(get_class($this)."::create sql=".$sql, LOG_DEBUG);
        $resql=$this->db->query($sql);
    	if (! $resql) { $error++; $this->errors[]="Error ".$this->db->lasterror(); }

		if (! $error)
        {
            $this->id = $this->db->last_insert_id(MAIN_DB_PREFIX."place_room");

            // Actions on extra fields (by external module or standard code)
            // FIXME le hook fait double emploi avec le trigger !!
            $hookmanager->initHooks(array('HookPlacedao'));
            $parameters=array('id'=>$this->id);
            $reshook=$hookmanager->executeHooks('insertExtraFields',$parameters,$this);    // Note that $action and $object may have been modified by some hooks
            if (empty($reshook))
            {
            	if (empty($conf->global->MAIN_EXTRAFIELDS_DISABLED)) // For avoid conflicts if trigger used
            	{
            		$result=$this->insertExtraFields();
            		if ($result < 0)
            		{
            			$error++;
            		}
            	}
            }
            else if ($reshook < 0) $error++;

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
     *    Load object in memory from database
     *
     *    @param    int     $id     Id of object
     *    @param    string  $ref    Ref of object
     *    @return   int             <0 if KO, >0 if OK
     */
    function fetch($id, $ref = '')
    {
    	global $conf,$langs;

        if (!$id && !$ref) {
            return 0;
        }

        $sql = "SELECT";
		$sql.= " t.rowid,";

		$sql.= " t.entity,";
		$sql.= " t.ref,";
		$sql.= " t.label,";
		$sql.= " t.fk_place,";
		$sql.= " t.fk_building,";
		$sql.= " t.fk_floor,";
		$sql.= " t.capacity,";
		$sql.= " t.type_code,";
		$sql.= " t.note_public,";
		$sql.= " t.note_private,";
		$sql.= " t.fk_user_creat,";
		$sql.= " ty.label as type_label,";
		$sql.= " t.tms";


        $sql.= " FROM ".MAIN_DB_PREFIX."place_room as t";
        $sql.= " LEFT JOIN ".MAIN_DB_PREFIX ."c_placeroom_type as ty ON t.type_code=ty.code";
        if ($id) $sql.= " WHERE t.rowid = ".$this->db->escape($id);
        else $sql.= " WHERE t.ref = '".$this->db->escape($ref)."'";

    	dol_syslog(get_class($this)."::fetch sql=".$sql, LOG_DEBUG);
        $resql=$this->db->query($sql);
        if ($resql)
        {
            if ($this->db->num_rows($resql))
            {
                $obj = $this->db->fetch_object($resql);

                $this->id    = $obj->rowid;

				$this->entity = $conf->entity;
				$this->ref = $obj->ref;
				$this->label = $obj->label;
				$this->fk_place = $obj->fk_place;
				$this->fk_building = $obj->fk_building;
				$this->fk_floor = $obj->fk_floor;
				$this->capacity = $obj->capacity;
				$this->type_code = $obj->type_code;
				$this->type_label = $obj->type_label;
				$this->note_public = $obj->note_public;
				$this->note_private = $obj->note_private;
				$this->fk_user_creat = $obj->fk_user_creat;
				$this->tms = $this->db->jdate($obj->tms);

            }

            parent::fetch_building($this->fk_building);
            parent::fetch_place($this->fk_place);
            parent::fetch_floor($this->fk_floor);

            if (!class_exists('ExtraFields'))
            	require_once(DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php');
			$extrafields=new ExtraFields($this->db);
			$extralabels=$extrafields->fetch_name_optionals_label($this->table_element,true);
			if (count($extralabels)>0) {
				$this->fetch_optionals($id,$extralabels);
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
    	$sql.= " t.label,";
    	$sql.= " t.fk_place,";
    	$sql.= " t.fk_building,";
    	$sql.= " t.fk_floor,";
    	$sql.= " t.capacity,";
    	$sql.= " t.type_code,";
    	$sql.= " t.note_public,";
    	$sql.= " t.note_private,";
    	$sql.= " t.fk_user_creat,";
    	$sql.= " t.tms,";
    	$sql.= " f.ref as floor_ref, ";
    	$sql.= " ty.label as type_label";
    	$sql.= ' FROM '.MAIN_DB_PREFIX .'place_room as t ';
    	$sql.= " LEFT JOIN ".MAIN_DB_PREFIX ."place_floor as f ON t.fk_floor=f.rowid";
    	$sql.= " LEFT JOIN ".MAIN_DB_PREFIX ."c_placeroom_type as ty ON t.type_code=ty.code";
    	$sql.= " WHERE t.entity IN (".getEntity('resource', true).")";

    	//Manage filter
    	if (!empty($filter)){
    		foreach($filter as $key => $value) {
    			if (strpos($key,'date')) {
    				$sql.= ' AND '.$key.' = \''.$this->db->idate($value).'\'';
    			}
    			else {
    				$sql.= " AND ".$key." = '".$value."'";
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
    				$line = new stdClass($this->db);
    				$line->id				=	$obj->rowid;
    				$line->ref				=	$obj->ref;
    				$line->label			=	$obj->label;
    				$line->fk_place			=	$obj->fk_place;
    				$line->fk_building		=	$obj->fk_building;
    				$line->fk_floor 		= 	$obj->fk_floor;
    				$line->floor_ref 		= 	$obj->floor_ref;
    				$line->type_code 		= 	$obj->type_code;
    				$line->type_label 		= 	$obj->type_label;
    				$line->capacity 		= 	$obj->capacity;
    				$line->fk_user_create	=	$obj->fk_user_create;

    				$building_stat = new Building($this->db);
    				$line->building	 = $building_stat;

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
     *  Update object into database
     *
     *  @param	User	$user        User that modifies
     *  @param  int		$notrigger	 0=launch triggers after, 1=disable triggers
     *  @return int     		   	 <0 if KO, >0 if OK
     */
    function update($user=0, $notrigger=0)
    {
    	global $conf, $langs, $hookmanager;
		$error=0;

		// Clean parameters

		if (isset($this->entity)) $this->entity=trim($this->entity);
		if (isset($this->ref)) $this->ref=trim($this->ref);
		if (isset($this->label)) $this->label=trim($this->label);
		if (isset($this->fk_place)) $this->fk_place=trim($this->fk_place);
		if (isset($this->fk_building)) $this->fk_building=trim($this->fk_building);
		if (isset($this->fk_floor)) $this->fk_floor=trim($this->fk_floor);
		if (isset($this->type_code)) $this->type_code=trim($this->type_code);
		if (isset($this->capacity)) $this->capacity=trim($this->capacity);
		if (isset($this->note_public)) $this->note_public=trim($this->note_public);
		if (isset($this->note_private)) $this->note_private=trim($this->note_private);
		if (isset($this->fk_user_creat)) $this->fk_user_creat=trim($this->fk_user_creat);



		// Check parameters
		// Put here code to add a control on parameters values

        // Update request
        $sql = "UPDATE ".MAIN_DB_PREFIX."place_room SET";

		$sql.= " entity=".(isset($this->entity)?$this->entity:"null").",";
		$sql.= " ref=".(isset($this->ref)?"'".$this->db->escape($this->ref)."'":"null").",";
		$sql.= " label=".(isset($this->label)?"'".$this->db->escape($this->label)."'":"null").",";
		$sql.= " fk_place=".(isset($this->fk_place)?"'".$this->db->escape($this->fk_place)."'":"null").",";
		$sql.= " fk_building=".(isset($this->fk_building)?"'".$this->db->escape($this->fk_building)."'":"null").",";
		$sql.= " fk_floor=".(isset($this->fk_floor)?"'".$this->db->escape($this->fk_floor)."'":"null").",";
		$sql.= " type_code=".(isset($this->type_code)?"'".$this->db->escape($this->type_code)."'":"null").",";
		$sql.= " capacity=".(isset($this->capacity)?"'".$this->db->escape($this->capacity)."'":"null").",";
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

			// Actions on extra fields (by external module or standard code)
			// FIXME le hook fait double emploi avec le trigger !!
			$hookmanager->initHooks(array('HookPlacedao'));
			$parameters=array('socid'=>$this->id);
			$reshook=$hookmanager->executeHooks('insertExtraFields',$parameters,$this);    // Note that $action and $object may have been modified by some hooks
			if (empty($reshook))
			{
				if (empty($conf->global->MAIN_EXTRAFIELDS_DISABLED)) // For avoid conflicts if trigger used
				{
					$result=$this->insertExtraFields();
					if ($result < 0)
					{
						$error++;
					}
				}
			}
			else if ($reshook < 0) $error++;

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
    		$sql = "DELETE FROM ".MAIN_DB_PREFIX."place_room";
    		$sql.= " WHERE rowid=".$this->id;

    		dol_syslog(get_class($this)."::delete sql=".$sql);
    		$resql = $this->db->query($sql);
        	if (! $resql) { $error++; $this->errors[]="Error ".$this->db->lasterror(); }

        	// Removed extrafields
        	if (! $error)
        	{
        		if (empty($conf->global->MAIN_EXTRAFIELDS_DISABLED)) // For avoid conflicts if trigger used
        		{
        			$result=$this->deleteExtraFields();
        			if ($result < 0)
        			{
        				$error++;
        				dol_syslog(get_class($this)."::delete error ".$this->error, LOG_ERR);
        			}
        		}
        	}
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
	 *      Charge dans cache la liste des types de rooms (paramétrable dans dictionnaire)
	 *
	 *      @return     int             Nb lignes chargees, 0 si deja chargees, <0 si ko
	 */
	function load_cache_types_rooms()
	{
		global $langs;

		if (count($this->cache_types_rooms)) return 0;    // Cache deja charge

		$sql = "SELECT rowid, code, label, use_default, pos, description";
		$sql.= " FROM ".MAIN_DB_PREFIX."c_placeroom_type";
		$sql.= " WHERE active > 0";
		$sql.= " ORDER BY pos";
		dol_syslog(get_class($this)."::load_cache_type_rooms sql=".$sql,LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql)
		{
			$num = $this->db->num_rows($resql);
			$i = 0;
			while ($i < $num)
			{
				$obj = $this->db->fetch_object($resql);
				// Si traduction existe, on l'utilise, sinon on prend le libelle par defaut
				$label=($langs->trans("RoomTypeShort".$obj->code)!=("RoomTypeShort".$obj->code)?$langs->trans("RoomTypeShort".$obj->code):($obj->label!='-'?$obj->label:''));
				$this->cache_types_rooms[$obj->rowid]['code'] =$obj->code;
				$this->cache_types_rooms[$obj->rowid]['label']=$label;
				$this->cache_types_rooms[$obj->rowid]['use_default'] =$obj->use_default;
				$this->cache_types_rooms[$obj->rowid]['pos'] =$obj->pos;
				$i++;
			}
			return $num;
		}
		else
		{
			dol_print_error($this->db);
			return -1;
		}
	}

	/**
	 *	Return clicable link of object (with eventually picto)
	 *
	 *	@param      int		$withpicto		Add picto into link
	 *	@param      string	$option			Where point the link ('compta', 'expedition', 'document', ...)
	 *	@param      string	$get_params    	Parametres added to url
	 *	@return     string          		String with URL
	 */
	function getNomUrl($withpicto=0, $option='', $notooltip=0, $morecss='', $save_lastsearch_value=-1)
	{
		global $langs;

		return parent::getNomUrl($withpicto,'room@place', $notooltip, $morecss, $save_lastsearch_value);

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

		$object=new Room($this->db);

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
		$this->fk_building='';
		$this->fk_floor='';
		$this->type_code='';
		$this->capacity='';
		$this->note_public='';
		$this->note_private='';
		$this->fk_user_creat='';
		$this->tms='';


	}

}
?>
