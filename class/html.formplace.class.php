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
     * 		@return	void
     */
    function select_types_rooms($selected='',$htmlname='roomtype',$filtertype='',$format=0, $empty=0, $noadmininfo=0,$maxlength=0)
    {
    	global $langs,$user;

    	$roomstat = new Room($this->db);

    	dol_syslog(get_class($this)."::select_types_rooms ".$selected.", ".$htmlname.", ".$filtertype.", ".$format,LOG_DEBUG);

    	$filterarray=array();

    	if ($filtertype != '' && $filtertype != '-1') $filterarray=explode(',',$filtertype);

    	$roomstat->load_cache_types_rooms();

    	print '<select id="select'.$htmlname.'" class="flat select_roomtype" name="'.$htmlname.'">';
    	if ($empty) print '<option value="">&nbsp;</option>';
    	if (is_array($roomstat->cache_types_rooms) && count($roomstat->cache_types_rooms))
    	{
    		foreach($roomstat->cache_types_rooms as $id => $arraytypes)
    		{
    			// On passe si on a demande de filtrer sur des modes de paiments particuliers
    			if (count($filterarray) && ! in_array($arraytypes['type'],$filterarray)) continue;

    			// We discard empty line if showempty is on because an empty line has already been output.
    			if ($empty && empty($arraytypes['code'])) continue;

    			if ($format == 0) print '<option value="'.$id.'"';
    			if ($format == 1) print '<option value="'.$arraytypes['code'].'"';
    			if ($format == 2) print '<option value="'.$arraytypes['code'].'"';
    			if ($format == 3) print '<option value="'.$id.'"';
    			// Si selected est text, on compare avec code, sinon avec id
    			if (preg_match('/[a-z]/i', $selected) && $selected == $arraytypes['code']) print ' selected="selected"';
    			elseif ($selected == $id) print ' selected="selected"';
    			print '>';
    			if ($format == 0) $value=($maxlength?dol_trunc($arraytypes['label'],$maxlength):$arraytypes['label']);
    			if ($format == 1) $value=$arraytypes['code'];
    			if ($format == 2) $value=($maxlength?dol_trunc($arraytypes['label'],$maxlength):$arraytypes['label']);
    			if ($format == 3) $value=$arraytypes['code'];
    			print $value?$value:'&nbsp;';
    			print '</option>';
    		}
    	}
    	print '</select>';
    	if ($user->admin && ! $noadmininfo) print info_admin($langs->trans("YouCanChangeValuesForThisListFromDictionnarySetup"),1);
    }

}

?>
