<?php
/* Copyright (C) - 2013	Jean-François FERRY	<jfefe@aternatik.fr>
*
* This program is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

/**
 *       \file       place/class/html.place.class.php
 *       \ingroup    core
 *       \brief      Fichier de la classe permettant la generation du formulaire html d'envoi de mail unitaire
 */
require_once(DOL_DOCUMENT_ROOT ."/core/class/html.form.class.php");


/**
 *
 * Classe permettant la gestion des formulaire du module place
 *
 * @package place

* \remarks Utilisation: $formplace = new FormPlace($db)
* \remarks $formplace->proprietes=1 ou chaine ou tableau de valeurs
*/
class FormPlace
{
    var $db;

    var $substit=array();
    var $param=array();

    var $error;

	public $num;


	/**
	* Constructor
	*
	* @param DoliDB $DB Database handler
	*/
    function __construct($db)
    {
        $this->db = $db;

        return 1;
    }

    /**
     *      Return html list of rooms type
     *
     *      @param	string	$selected       Id du type pre-selectionne
     *      @param  string	$htmlname       Nom de la zone select
     *      @param  string	$filtertype     To filter on field type in llx_c_roomsup_type (array('code'=>xx,'label'=>zz))
     *      @param  int		$format         0=id+libelle, 1=code+code, 2=code+libelle, 3=id+code
     *      @param  int		$empty			1=peut etre vide, 0 sinon
     * 		@param	int		$noadmininfo	0=Add admin info, 1=Disable admin info
     *      @param  int		$maxlength      Max length of label
     * 		@return	string  HTML select element
     */
    function select_types_rooms($selected='',$htmlname='roomtype',$filtertype='',$format=0, $empty=0, $noadmininfo=0,$maxlength=0)
    {
    	global $langs,$user;

    	$roomstat = new Room($this->db);

    	dol_syslog(get_class($this)."::select_types_rooms ".$selected.", ".$htmlname.", ".$filtertype.", ".$format,LOG_DEBUG);

    	$filterarray=array();

    	if ($filtertype != '' && $filtertype != '-1') $filterarray=explode(',',$filtertype);

    	$roomstat->load_cache_types_rooms();

    	$out = '<select id="select'.$htmlname.'" class="flat select_roomtype" name="'.$htmlname.'">';
    	if ($empty) $out .= '<option value="">&nbsp;</option>';
    	if (is_array($roomstat->cache_types_rooms) && count($roomstat->cache_types_rooms))
    	{
    		foreach($roomstat->cache_types_rooms as $id => $arraytypes)
    		{
    			// On passe si on a demande de filtrer sur des modes de paiments particuliers
    			if (count($filterarray) && ! in_array($arraytypes['type'],$filterarray)) continue;

    			// We discard empty line if showempty is on because an empty line has already been output.
    			if ($empty && empty($arraytypes['code'])) continue;

    			if ($format == 0) $out .= '<option value="'.$id.'"';
    			if ($format == 1) $out .= '<option value="'.$arraytypes['code'].'"';
    			if ($format == 2) $out .= '<option value="'.$arraytypes['code'].'"';
    			if ($format == 3) $out .= '<option value="'.$id.'"';
    			// Si selected est text, on compare avec code, sinon avec id
    			if (preg_match('/[a-z]/i', $selected) && $selected == $arraytypes['code']) $out .= ' selected="selected"';
    			elseif ($selected == $id) $out .= ' selected="selected"';
    			$out .= '>';
    			if ($format == 0) $value=($maxlength?dol_trunc($arraytypes['label'],$maxlength):$arraytypes['label']);
    			if ($format == 1) $value=$arraytypes['code'];
    			if ($format == 2) $value=($maxlength?dol_trunc($arraytypes['label'],$maxlength):$arraytypes['label']);
    			if ($format == 3) $value=$arraytypes['code'];
    			$out .= $value?$value:'&nbsp;';
    			$out .= '</option>';
    		}
    	}
    	$out .= '</select>';
    	if ($user->admin && ! $noadmininfo) print info_admin($langs->trans("YouCanChangeValuesForThisListFromDictionnarySetup"),1);

    	return $out;
    }

    /**
     *  Output html form to select a location (place)
     *
     *	@param	string	$selected       Preselected type
     *	@param  string	$htmlname       Name of field in form
     *  @param  string	$filter         Optionnal filters criteras (example: 's.rowid <> x')
     *	@param	int		$showempty		Add an empty field
     * 	@param	int		$showtype		Show third party type in combolist (customer, prospect or supplier)
     * 	@param	int		$forcecombo		Force to use combo box
     *  @param	array	$event			Event options. Example: array(array('method'=>'getContacts', 'url'=>dol_buildpath('/core/ajax/contacts.php',1), 'htmlname'=>'contactid', 'params'=>array('add-customer-contact'=>'disabled')))
     *  @param	string	$filterkey		Filter on key value
     *  @param	int		$outputmode		0=HTML select string, 1=Array
     *  @param	int		$limit			Limit number of answers
     * 	@return	string					HTML string with
     */
    function select_place_list($selected='',$htmlname='fk_place',$filter='',$showempty=0, $showtype=0, $forcecombo=0, $event=array(), $filterkey='', $outputmode=0, $limit=20)
    {
    	global $conf,$user,$langs;

    	$out='';
    	$outarray=array();

    	// On recherche les societes
    	$sql = "SELECT p.rowid, p.ref";
    	$sql.= " FROM ".MAIN_DB_PREFIX ."place as p";
    	$sql.= " WHERE p.entity IN (".getEntity('place', 1).")";
    	if ($filter) $sql.= " AND (".$filter.")";
    	//if (!$user->rights->societe->client->voir && !$user->societe_id) $sql.= " AND s.rowid = sc.fk_soc AND sc.fk_user = " .$user->id;
    	//if (! empty($conf->global->COMPANY_HIDE_INACTIVE_IN_COMBOBOX)) $sql.= " AND s.status<>0 ";
    	// Add criteria
    	if ($filterkey && $filterkey != '')
    	{
    		$sql.=" AND (";

    		// For natural search
    		$scrit = explode(' ', $filterkey);
    		foreach ($scrit as $crit) {
    			$sql.=" AND (p.ref LIKE '%".$crit."%'";
    			$sql.=")";
    		}


    		$sql.=")";
    	}
    	$sql.= " ORDER BY ref ASC";

    	dol_syslog(get_class($this)."::select_place_list sql=".$sql);
    	$resql=$this->db->query($sql);
    	if ($resql)
    	{
    		if ($conf->use_javascript_ajax && $conf->global->COMPANY_USE_SEARCH_TO_SELECT && ! $forcecombo)
    		{
    			//$minLength = (is_numeric($conf->global->COMPANY_USE_SEARCH_TO_SELECT)?$conf->global->COMPANY_USE_SEARCH_TO_SELECT:2);
    			$out.= ajax_combobox($htmlname, $event, $conf->global->COMPANY_USE_SEARCH_TO_SELECT);
    		}

    		// Construct $out and $outarray
    		$out.= '<select id="'.$htmlname.'" class="flat" name="'.$htmlname.'">'."\n";
    		if ($showempty) $out.= '<option value="-1"></option>'."\n";
    		$num = $this->db->num_rows($resql);
    		$i = 0;
    		if ($num)
    		{
    			while ($i < $num)
    			{
    				$obj = $this->db->fetch_object($resql);
    				$label=$obj->ref;

    				if ($selected > 0 && $selected == $obj->rowid)
    				{
    					$out.= '<option value="'.$obj->rowid.'" selected="selected">'.$label.'</option>';
    				}
    				else
    				{
    					$out.= '<option value="'.$obj->rowid.'">'.$label.'</option>';
    				}

    				array_push($outarray, array('key'=>$obj->rowid, 'value'=>$obj->name, 'label'=>$obj->name));

    				$i++;
    				if (($i % 10) == 0) $out.="\n";
    			}
    		}
    		$out.= '</select>'."\n";
    	}
    	else
    	{
    		dol_print_error($this->db);
    	}

    	if ($outputmode) return $outarray;
    	return $out;
    }

    /**
     *	Return list of all rooms (for a place or all)
     *
     *	@param	int		$fk_place      	Id of place or 0 for all
     *	@param  string	$selected   	Id room pre-selectionne
     *	@param  string	$htmlname  	    Name of HTML field ('none' for a not editable field)
     *	@param  int		$showempty     	0=no empty value, 1=add an empty value
     *	@param  string	$exclude        List of rooms id to exclude
     *	@param	string	$limitto		Disable answers that are not id in this array list
     *	@param	string	$showfunction   Add function into label
     *	@param	string	$moreclass		Add more class to class style
     *	@param	bool	$options_only	Return options only (for ajax treatment)
     *	@param	string	$showbuilding	    Add building into label
     * 	@param	int		$forcecombo		Force to use combo box
     *  @param	array	$event			Event options. Example: array(array('method'=>'getContacts', 'url'=>dol_buildpath('/core/ajax/contacts.php',1), 'htmlname'=>'contactid', 'params'=>array('add-customer-contact'=>'disabled')))
     *	@return	 int						<0 if KO, Nb of contact in list if OK
     */
    function selectrooms($fk_place,$selected='',$htmlname='fk_resource_room',$showempty=0,$exclude='',$limitto='',$showfunction=0, $moreclass='', $options_only=false, $showbuilding=0, $forcecombo=0, $event=array())
    {
    	global $conf,$langs;

    	$langs->load('companies');

    	$out='';

    	// On recherche les salles
    	$sql = "SELECT s.rowid, s.ref, s.label, s.type_code, s.capacity";
    	if ($showbuilding > 0) {
    		$sql.= " , b.ref as building";
    	}
    	$sql.= " FROM ".MAIN_DB_PREFIX ."place_room as s";
   		$sql.= " LEFT OUTER JOIN  ".MAIN_DB_PREFIX ."place_building as b ON b.rowid=s.fk_building ";
   		$sql.= " LEFT OUTER JOIN  ".MAIN_DB_PREFIX ."place as p ON p.rowid=b.fk_place ";

    	$sql.= " WHERE s.entity IN (".getEntity('room', 1).")";
    	if ($fk_place > 0) $sql.= " AND b.fk_place=".$fk_place;
    	$sql.= " ORDER BY s.ref ASC, s.fk_floor ASC";
    	
    	dol_syslog(get_class($this)."::selectrooms sql=".$sql);
    	$resql=$this->db->query($sql);
    	if ($resql)
    	{
    		if ($conf->use_javascript_ajax && $conf->global->CONTACT_USE_SEARCH_TO_SELECT && ! $forcecombo && ! $options_only)
    		{
    			$out.= ajax_combobox($htmlname, $event, $conf->global->CONTACT_USE_SEARCH_TO_SELECT);
    		}

    		if ($htmlname != 'none' || $options_only) $out.= '<select class="flat'.($moreclass?' '.$moreclass:'').'" id="'.$htmlname.'" name="'.$htmlname.'">';
    		if ($showempty == 1) $out.= '<option value="0"'.($selected=='0'?' selected="selected"':'').'></option>';
    		if ($showempty == 2) $out.= '<option value="0"'.($selected=='0'?' selected="selected"':'').'>'.$langs->trans("Internal").'</option>';
    		$num = $this->db->num_rows($resql);
    		$i = 0;
    		if ($num)
    		{
    			if(!class_exists("Room"))
    				require_once 'room.class.php';
    			$roomstatic=new Room($this->db);

    			while ($i < $num)
    			{
    				$obj = $this->db->fetch_object($resql);

    				$roomstatic->id=$obj->rowid;


    					if ($htmlname != 'none')
    					{
    						$disabled=0;
    						if (is_array($exclude) && count($exclude) && in_array($obj->rowid,$exclude)) $disabled=1;
    						if (is_array($limitto) && count($limitto) && ! in_array($obj->rowid,$limitto)) $disabled=1;
    						if ($selected && $selected == $obj->rowid)
    						{
    							$out.= '<option value="'.$obj->rowid.'"';
    							if ($disabled) $out.= ' disabled="disabled"';
    							$out.= ' selected="selected">';
    							$out.= $obj->ref;
    							$out.= ' '.$obj->label;
    							if (($showbuilding > 0) && $obj->building) $out.= ' - ('.$obj->building.')';
    							$out.= '</option>';
    						}
    						else
    						{
    							$out.= '<option value="'.$obj->rowid.'"';
    							if ($disabled) $out.= ' disabled="disabled"';
    							$out.= '>';
    							$out.= $obj->ref;
    							$out.= ' '.$obj->label;
    							if (($showbuilding > 0) && $obj->building) $out.= ' - ('.$obj->building.')';
    							$out.= '</option>';
    						}
    					}
    					else
    					{
    						if ($selected == $obj->rowid)
    						{
    							$out.= $roomstatic->getFullName($langs);
    							if ($showfunction && $obj->poste) $out.= ' ('.$obj->poste.')';
							    //FIXME: $showsoc is undeclared
    							if (($showsoc > 0) && $obj->company) $out.= ' - ('.$obj->company.')';
    						}
    					}

    				$i++;
    			}
    		}
    		else
    		{
    			$out.= '<option value="-1"'.($showempty==2?'':' selected="selected"').' disabled="disabled">'.$langs->trans("NoRoomDefined").'</option>';
    		}
    		if ($htmlname != 'none' || $options_only)
    		{
    			$out.= '</select>';
    		}

    		$this->num = $num;
    		return $out;
    	}
    	else
    	{
    		dol_print_error($this->db);
    		return -1;
    	}
    }

}

?>
