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
 *  \file      	place/class/resource.class.php
 *  \ingroup    place
 *  \brief      Class file for resource object

 */

// Put here all includes required by your class file
require_once(DOL_DOCUMENT_ROOT."/core/class/commonobject.class.php");


/**
 *	DAO Place object
 */
class Resource extends CommonObject
{
	var $db;							//!< To store db handler
	var $error;							//!< To return error code (or message)
	var $errors=array();				//!< To return several error codes (or messages)
	//var $element='resource';			//!< Id that identify managed objects
	//var $table_element='llx_resource';	//!< Name of table without prefix where object is stored

    var $id;

	var $resource_id;
	var $resource_type;
	var $element_id;
	var $element_type;
	var $busy;
	var $mandatory;
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
     *	Add resources to the actioncom object
     *
     *	@param		int		$element_id			Element id
     *	@param		string	$element_type		Element type
     *	@param		int		$resource_id		Resource id
     *	@param		string	$resource_type		Resource type
     *	@param		array	$resource   		Resources linked with element
     *	@return		int					<=0 if KO, >0 if OK
     */
    function add_element_resource($element_id,$element_type,$resource_id,$resource_element)
    {
	    	$this->db->begin();

	    	$sql = "INSERT INTO ".MAIN_DB_PREFIX."element_resources (";
	    	$sql.= "resource_id";
	    	$sql.= ", resource_type";
	    	$sql.= ", element_id";
	    	$sql.= ", element_type";
	    	$sql.= ") VALUES (";
	    	$sql.= $resource_id;
	    	$sql.= ", '".$resource_element."'";
	    	$sql.= ", '".$element_id."'";
	    	$sql.= ", '".$element_type."'";
	    	$sql.= ")";

	    	dol_syslog(get_class($this)."::add_element_resource sql=".$sql, LOG_DEBUG);
	    	if ($this->db->query($sql))
	    	{
	    		$this->db->commit();
	    		return 1;
	    	}
	    	else
	    	{
	    		$this->error=$this->db->lasterror();
	    		$this->db->rollback();
	    		return  0;
	    	}
    	}


    function getElementResources($element,$element_id)
    {

	    // Links beetween objects are stored in this table
	    $sql = 'SELECT resource_id, resource_type, busy, mandatory';
	    $sql.= ' FROM '.MAIN_DB_PREFIX.'element_resources';
	    $sql.= " WHERE element_id='".$element_id."' AND element_type='".$element."'";
	    $sql .= ' ORDER BY resource_type';

	    dol_syslog(get_class($this)."::getElementResources sql=".$sql);
	    $resql = $this->db->query($sql);
	    if ($resql)
	    {
	    	$num = $this->db->num_rows($resql);
	    	$i = 0;
	    	while ($i < $num)
	    	{
	    		$obj = $this->db->fetch_object($resql);

	    		$resources[$i] = array('resource_id' => $obj->resource_id, 'resource_type'=>$obj->resource_type,'busy'=>$obj->busy,'mandatory'=>$obj->mandatory);
	    		$i++;
	    	}
	    }

	    return $resources;
    }

    function fetchElementResources($element,$element_id)
    {
    	$resources = getElementResources($element,$element_id);

    	foreach($resources as $nb => $resource)
    	{

    	}

    }

}
?>
