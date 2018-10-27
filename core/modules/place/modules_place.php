<?php
/* Copyright (C) 2003-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2010 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2004      Eric Seigne          <eric.seigne@ryxeo.com>
 * Copyright (C) 2005-2012 Regis Houssin        <regis.houssin@capnetworks.com>
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
 * or see http://www.gnu.org/
 */

/**
 *	    \file       htdocs/core/modules/societe/modules_societe.class.php
 *		\ingroup    societe
 *		\brief      File with parent class of submodules to manage numbering and document generation.
 */
require_once DOL_DOCUMENT_ROOT.'/core/class/commondocgenerator.class.php';

/**
 *	\class      ModelePlaceDoc
 *	\brief      Parent class for Place models of doc generators.
 */
abstract class ModelePdfPlace extends CommonDocGenerator
{
    public $error = '';

    /**
     *  Return list of active generation modules.
     *
     * 	@param	DoliDB		$db					Database handler
     *  @param	string		$maxfilenamelength  Max length of value to show
     *
     * 	@return	array							List of templates
     */
    public static function liste_modeles($db, $maxfilenamelength = 0)
    {
        global $conf;

        $type = 'place';
        $liste = array();

        include_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
        $liste = getListOfModels($db, $type, $maxfilenamelength);

        return $liste;
    }
}

/**
 *	    \class      ModeleThirdPartyCode
 *		\brief  	Parent class for third parties code generators.
 */
abstract class ModelePlaceCode
{
    public $error = '';

    /**     Renvoi la description par defaut du modele de numerotation
     *		@param	Translate	$langs		Object langs
     *
     *      @return string      			Texte descripif
     */
    public function info($langs)
    {
        $langs->load('bills');

        return $langs->trans('NoDescription');
    }

    /**     Renvoi nom module
     *		@param	Translate	$langs		Object langs
     *
     *      @return string      			Nom du module
     */
    public function getNom($langs)
    {
        return $this->nom;
    }

    /**     Renvoi un exemple de numerotation
     *		@param	Translate	$langs		Object langs
     *
     *      @return string      			Example
     */
    public function getExample($langs)
    {
        $langs->load('bills');

        return $langs->trans('NoExample');
    }

    /**     Test si les numeros deja en vigueur dans la base ne provoquent pas de
     *      de conflits qui empechera cette numerotation de fonctionner.
     *
     *      @return     bool     false si conflit, true si ok
     */
    public function canBeActivated()
    {
        return true;
    }

    /**
     *  Return next value available.
     *
     *	@param	Societe		$objsoc		Object thirdparty
     *	@param	int			$type		Type
     *
     *  @return string      			Value
     */
    public function getNextValue($objsoc = 0, $type = -1)
    {
        global $langs;

        return $langs->trans('Function_getNextValue_InModuleNotWorking');
    }

    /**     Return version of module
     *      @return     string      Version
     */
    public function getVersion()
    {
        global $langs;
        $langs->load('admin');

        if ($this->version == 'development') {
            return $langs->trans('VersionDevelopment');
        }
        if ($this->version == 'experimental') {
            return $langs->trans('VersionExperimental');
        }
        if ($this->version == 'dolibarr') {
            return DOL_VERSION;
        }

        return $langs->trans('NotAvailable');
    }

    /**
     *  Renvoi la liste des modeles de numéroation.
     *
     *  @param	DoliDB	$db     			Database handler
     *  @param  string	$maxfilenamelength  Max length of value to show
     *
     *  @return	array						List of numbers
     */
    public static function liste_modeles($db, $maxfilenamelength = 0)
    {
        $liste = array();
        $sql = '';

        $resql = $db->query($sql);
        if ($resql) {
            $num = $db->num_rows($resql);
            $i = 0;
            while ($i < $num) {
                $row = $db->fetch_row($resql);
                $liste[$row[0]] = $row[1];
                ++$i;
            }
        } else {
            return -1;
        }

        return $liste;
    }

    /**
     *      Return description of module parameters.
     *
     *      @param	Translate	$langs      Output language
     *		@param	Societe		$soc		Third party object
     *		@param	int			$type		-1=Nothing, 0=Customer, 1=Supplier
     *
     *		@return	string					HTML translated description
     */
    public function getToolTip($langs, $soc, $type)
    {
        global $conf;

        $langs->load('admin');

        $s = '';
        if ($type == -1) {
            $s .= $langs->trans('Name').': <b>'.$this->nom.'</b><br>';
        }
        if ($type == -1) {
            $s .= $langs->trans('Version').': <b>'.$this->getVersion().'</b><br>';
        }
        if ($type == 0) {
            $s .= $langs->trans('CustomerCodeDesc').'<br>';
        }
        if ($type == 1) {
            $s .= $langs->trans('SupplierCodeDesc').'<br>';
        }
        if ($type != -1) {
            $s .= $langs->trans('ValidityControledByModule').': <b>'.$this->getNom($langs).'</b><br>';
        }
        $s .= '<br>';
        $s .= '<u>'.$langs->trans('ThisIsModuleRules').':</u><br>';
        if ($type == 0) {
            $s .= $langs->trans('RequiredIfCustomer').': ';
            if (!empty($conf->global->MAIN_COMPANY_CODE_ALWAYS_REQUIRED) && !empty($this->code_null)) {
                $s .= '<strike>';
            }
            $s .= yn(!$this->code_null, 1, 2);
            if (!empty($conf->global->MAIN_COMPANY_CODE_ALWAYS_REQUIRED) && !empty($this->code_null)) {
                $s .= '</strike> '.yn(1, 1, 2).' ('.$langs->trans('ForcedToByAModule', $langs->transnoentities('yes')).')';
            }
            $s .= '<br>';
        }
        if ($type == 1) {
            $s .= $langs->trans('RequiredIfSupplier').': ';
            if (!empty($conf->global->MAIN_COMPANY_CODE_ALWAYS_REQUIRED) && !empty($this->code_null)) {
                $s .= '<strike>';
            }
            $s .= yn(!$this->code_null, 1, 2);
            if (!empty($conf->global->MAIN_COMPANY_CODE_ALWAYS_REQUIRED) && !empty($this->code_null)) {
                $s .= '</strike> '.yn(1, 1, 2).' ('.$langs->trans('ForcedToByAModule', $langs->transnoentities('yes')).')';
            }
            $s .= '<br>';
        }
        if ($type == -1) {
            $s .= $langs->trans('Required').': ';
            if (!empty($conf->global->MAIN_COMPANY_CODE_ALWAYS_REQUIRED) && !empty($this->code_null)) {
                $s .= '<strike>';
            }
            $s .= yn(!$this->code_null, 1, 2);
            if (!empty($conf->global->MAIN_COMPANY_CODE_ALWAYS_REQUIRED) && !empty($this->code_null)) {
                $s .= '</strike> '.yn(1, 1, 2).' ('.$langs->trans('ForcedToByAModule', $langs->transnoentities('yes')).')';
            }
            $s .= '<br>';
        }
        $s .= $langs->trans('CanBeModifiedIfOk').': ';
        $s .= yn($this->code_modifiable, 1, 2);
        $s .= '<br>';
        $s .= $langs->trans('CanBeModifiedIfKo').': '.yn($this->code_modifiable_invalide, 1, 2).'<br>';
        $s .= $langs->trans('AutomaticCode').': '.yn($this->code_auto, 1, 2).'<br>';
        $s .= '<br>';
        if ($type == 0 || $type == -1) {
            $nextval = $this->getNextValue($soc, 0);
            if (empty($nextval)) {
                $nextval = $langs->trans('Undefined');
            }
            $s .= $langs->trans('NextValue').($type == -1 ? ' ('.$langs->trans('Customer').')' : '').': <b>'.$nextval.'</b><br>';
        }
        if ($type == 1 || $type == -1) {
            $nextval = $this->getNextValue($soc, 1);
            if (empty($nextval)) {
                $nextval = $langs->trans('Undefined');
            }
            $s .= $langs->trans('NextValue').($type == -1 ? ' ('.$langs->trans('Supplier').')' : '').': <b>'.$nextval.'</b>';
        }

        return $s;
    }

    /**
     *   Check if mask/numbering use prefix.
     *
     *   @return	int		0=no, 1=yes
     */
    public function verif_prefixIsUsed()
    {
        return 0;
    }
}

/**
 *	Create a document for place.
 *
 *	@param	DoliDB		$db  			Database handler
 *	@param  Societe		$object			Object of third party to use
 *	@param	string		$message		Message
 *	@param	string		$modele			Force model to use ('' to not force). model can be a model name or a template file
 *	@param	Translate	$outputlangs	Object lang to use for translation
 *
 *	@return int        					<0 if KO, >0 if OK
 */
function place_doc_create($db, $object, $message, $modele, $outputlangs)
{
    global $conf,$langs,$user;
    $langs->load('bills');

    //$dir = DOL_DOCUMENT_ROOT . "/core/modules/societe/doc";
    $dir = dol_buildpath('/place/core/modules/place/doc');
    $srctemplatepath = $conf->place->dir_temp;

    // Positionne modele sur le nom du modele a utiliser
    if (!dol_strlen($modele)) {
        if (!empty($conf->global->PLACE_ADDON_PDF_ODT_PATH)) {
            $modele = $conf->global->PLACE_ADDON_PDF_ODT_PATH;
        } else {
            echo $langs->trans('Error').' '.$langs->trans('Error_PLACE_ADDON_PDF_ODT_PATH_NotDefined');

            return 0;
        }
    }

    // If selected modele is a filename template (then $modele="modelname:filename")
    $tmp = explode(':', $modele, 2);
    if (!empty($tmp[1])) {
        $modele = $tmp[0];
        $srctemplatepath = $tmp[1];
    }

    // Search template
    $file = 'doc_'.$modele.'.modules.php';
    if (file_exists($dir.'/'.$file)) {
        $classname = 'doc_'.$modele;
        require_once $dir.'/'.$file;

        $obj = new $classname($db);
        $obj->message = $message;

        // We save charset_output to restore it because write_file can change it if needed for
        // output format that does not support UTF8.
        $sav_charset_output = $outputlangs->charset_output;
        if ($obj->write_file($object, $outputlangs, $srctemplatepath) > 0) {
            $outputlangs->charset_output = $sav_charset_output;

            // Appel des triggers
            include_once DOL_DOCUMENT_ROOT.'/core/class/interfaces.class.php';
            $interface = new Interfaces($db);
            $result = $interface->run_triggers('PLACE_BUILDDOC', $object, $user, $langs, $conf);
            if ($result < 0) {
                ++$error;
                $this->errors = $interface->errors;
            }
            // Fin appel triggers

            return 1;
        } else {
            $outputlangs->charset_output = $sav_charset_output;
            dol_print_error($db, 'place_doc_create Error: '.$obj->error);

            return -1;
        }
    } else {
        dol_print_error('', $langs->trans('Error').' '.$langs->trans('ErrorFileDoesNotExists', $dir.'/'.$file));

        return -1;
    }
}

/**
 *	Create a document for place.
 *
 *	@param	DoliDB		$db  			Database handler
 *	@param  Societe		$object			Object of third party to use
 *	@param	string		$message		Message
 *	@param	string		$modele			Force model to use ('' to not force). model can be a model name or a template file
 *	@param	Translate	$outputlangs	Object lang to use for translation
 *
 *	@return int        					<0 if KO, >0 if OK
 */
function room_doc_create($db, $object, $message, $modele, $outputlangs)
{
    global $conf,$langs,$user;
    $langs->load('bills');

    //$dir = DOL_DOCUMENT_ROOT . "/core/modules/societe/doc";
    $dir = dol_buildpath('/place/core/modules/place/doc');
    $srctemplatepath = '';

    // Positionne modele sur le nom du modele a utiliser
    if (!dol_strlen($modele)) {
        if (!empty($conf->global->PLACE_ROOM_ADDON_PDF_ODT_PATH)) {
            $modele = $conf->global->PLACE_ROOM_ADDON_PDF_ODT_PATH;
        } else {
            echo $langs->trans('Error').' '.$langs->trans('Error_PLACE_ROOM_ADDON_PDF_ODT_PATH_NotDefined');

            return 0;
        }
    }

    // If selected modele is a filename template (then $modele="modelname:filename")
    $tmp = explode(':', $modele, 2);
    if (!empty($tmp[1])) {
        $modele = $tmp[0];
        $srctemplatepath = $tmp[1];
    }

    // Search template
    $file = 'doc_'.$modele.'.modules.php';
    if (file_exists($dir.'/'.$file)) {
        $classname = 'doc_'.$modele;
        require_once $dir.'/'.$file;

        $obj = new $classname($db);
        $obj->message = $message;

        // We save charset_output to restore it because write_file can change it if needed for
        // output format that does not support UTF8.
        $sav_charset_output = $outputlangs->charset_output;
        if ($obj->write_file($object, $outputlangs, $srctemplatepath) > 0) {
            $outputlangs->charset_output = $sav_charset_output;

            // Appel des triggers
            include_once DOL_DOCUMENT_ROOT.'/core/class/interfaces.class.php';
            $interface = new Interfaces($db);
            $result = $interface->run_triggers('PLACE_ROOM_BUILDDOC', $object, $user, $langs, $conf);
            if ($result < 0) {
                ++$error;
                $this->errors = $interface->errors;
            }
            // Fin appel triggers

            return 1;
        } else {
            $outputlangs->charset_output = $sav_charset_output;
            dol_print_error($db, 'room_doc_create Error: '.$obj->error);

            return -1;
        }
    } else {
        dol_print_error('', $langs->trans('Error').' '.$langs->trans('ErrorFileDoesNotExists', $dir.'/'.$file));

        return -1;
    }
}
