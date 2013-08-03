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
 *  \file      	place/class/place.class.php
 *  \ingroup    place
 *  \brief      CRUD class file (Create/Read/Update/Delete) for place object
 *				Initialy built by build_class_from_table on 2013-07-24 16:03
 */

// Put here all includes required by your class file
require_once(DOL_DOCUMENT_ROOT."/core/class/commonobject.class.php");


/**
 *	DAO Place object
 */
class Place extends CommonObject
{
	var $db;							//!< To store db handler
	var $error;							//!< To return error code (or message)
	var $errors=array();				//!< To return several error codes (or messages)
	var $element='place';			//!< Id that identify managed objects
	var $table_element='place';		//!< Name of table without prefix where object is stored

    var $id;

	var $ref;
	var $fk_soc;
	var $fk_socpeople;
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

		if (isset($this->ref)) $this->ref=trim($this->ref);
		if (isset($this->fk_soc)) $this->fk_soc=trim($this->fk_soc);
		if (isset($this->fk_socpeople)) $this->fk_socpeople=trim($this->fk_socpeople);
		if (isset($this->description)) $this->description=trim($this->description);
		if (isset($this->lat)) $this->lat=trim($this->lat);
		if (isset($this->lng)) $this->lng=trim($this->lng);
		if (isset($this->note_public)) $this->note_public=trim($this->note_public);
		if (isset($this->note_private)) $this->note_private=trim($this->note_private);


        // Insert request
		$sql = "INSERT INTO ".MAIN_DB_PREFIX."place(";

		$sql.= "ref,";
		$sql.= "fk_soc,";
		$sql.= "fk_socpeople,";
		$sql.= "description,";
		$sql.= "lat,";
		$sql.= "lng,";
		$sql.= "note_public,";
		$sql.= "note_private,";
		$sql.= "fk_user_creat";


        $sql.= ") VALUES (";

		$sql.= " ".(! isset($this->ref)?'NULL':"'".$this->db->escape($this->ref)."'").",";
		$sql.= " ".(! isset($this->fk_soc)?'NULL':"'".$this->fk_soc."'").",";
		$sql.= " ".(! isset($this->fk_socpeople)?'NULL':"'".$this->fk_socpeople."'").",";
		$sql.= " ".(! isset($this->description)?'NULL':"'".$this->db->escape($this->description)."'").",";
		$sql.= " ".(! isset($this->lat)?'NULL':"'".$this->lat."'").",";
		$sql.= " ".(! isset($this->lng)?'NULL':"'".$this->lng."'").",";
		$sql.= " ".(! isset($this->note_public)?'NULL':"'".$this->db->escape($this->note_public)."'").",";
		$sql.= " ".(! isset($this->note_private)?'NULL':"'".$this->db->escape($this->note_private)."'").",";
		$sql.= " ".(! isset($this->fk_user_creat)?'NULL':"'".$user->id."'")."";


		$sql.= ")";

		$this->db->begin();

		dol_syslog(get_class($this)."::create sql=".$sql, LOG_DEBUG);
		$resql=$this->db->query($sql);
		if (! $resql) {
			$error++; $this->errors[]="Error ".$this->db->lasterror();
		}

		if (! $error)
		{
			$this->id = $this->db->last_insert_id(MAIN_DB_PREFIX."place");

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
    	$sql.= " t.ref,";
    	$sql.= " t.fk_soc,";
    	$sql.= " t.fk_socpeople,";
    	$sql.= " t.description,";
    	$sql.= " t.lat,";
    	$sql.= " t.lng,";
    	$sql.= " t.note_public,";
    	$sql.= " t.note_private,";
    	$sql.= " t.fk_user_creat,";
    	$sql.= " t.tms";


    	$sql.= " FROM ".MAIN_DB_PREFIX."place as t";
    	$sql.= " WHERE t.rowid = ".$id;

    	dol_syslog(get_class($this)."::fetch sql=".$sql, LOG_DEBUG);
    	$resql=$this->db->query($sql);
    	if ($resql)
        {
            if ($this->db->num_rows($resql))
            {
                $obj = $this->db->fetch_object($resql);

                $this->id    = $obj->rowid;

				$this->ref = $obj->ref;
				$this->fk_soc = $obj->fk_soc;
				$this->fk_socpeople = $obj->fk_socpeople;
				$this->description = $obj->description;
				$this->lat = $obj->lat;
				$this->lng = $obj->lng;
				$this->note_public = $obj->note_public;
				$this->note_private = $obj->note_private;
				$this->fk_user_creat = $obj->fk_user_creat;
				$this->tms = $this->db->jdate($obj->tms);


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
     *	Load all objects into $this->line
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
		$sql.= " t.fk_soc,";
		$sql.= " t.fk_socpeople,";
		$sql.= " t.description,";
		$sql.= " t.lat,";
		$sql.= " t.lng,";
		$sql.= " t.note_public,";
		$sql.= " t.note_private,";
		$sql.= " t.fk_user_creat,";
		$sql.= " t.tms";
   		$sql.= ' FROM '.MAIN_DB_PREFIX .'place as t ';
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
   		$sql.= " GROUP BY t.rowid,t.ref";
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
   					$line->rowid			=	$obj->rowid;
   					$line->fk_soc			=	$obj->fk_soc;
   					$line->fk_socpeople		=	$obj->fk_socpeople;
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

		if (isset($this->ref)) $this->ref=trim($this->ref);
		if (isset($this->fk_soc)) $this->fk_soc=trim($this->fk_soc);
		if (isset($this->fk_socpeople)) $this->fk_socpeople=trim($this->fk_socpeople);
		if (isset($this->description)) $this->description=trim($this->description);
		if (isset($this->lat)) $this->lat=trim($this->lat);
		if (isset($this->lng)) $this->lng=trim($this->lng);
		if (isset($this->note_public)) $this->note_public=trim($this->note_public);
		if (isset($this->note_private)) $this->note_private=trim($this->note_private);
		if (isset($this->fk_user_creat)) $this->fk_user_creat=trim($this->fk_user_creat);



		// Check parameters
		// Put here code to add a control on parameters values

        // Update request
        $sql = "UPDATE ".MAIN_DB_PREFIX."place SET";

		$sql.= " ref=".(isset($this->ref)?"'".$this->db->escape($this->ref)."'":"null").",";
		$sql.= " fk_soc=".(isset($this->fk_soc)?$this->fk_soc:"null").",";
		$sql.= " fk_socpeople=".(isset($this->fk_socpeople)?$this->fk_socpeople:"null").",";
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
    		$sql = "DELETE FROM ".MAIN_DB_PREFIX."place";
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
	 *	Load an object from its id and create a new one in database
	 *
	 *	@param	int		$fromid     Id of object to clone
	 * 	@return	int					New id of clone
	 */
	function createFromClone($fromid)
	{
		global $user,$langs;

		$error=0;

		$object=new Place($this->db);

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

		$this->ref='';
		$this->fk_soc='';
		$this->fk_socpeople='';
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
