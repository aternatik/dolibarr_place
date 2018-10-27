<?php
/* Copyright (C) 2010-2012 	Laurent Destailleur <eldy@users.sourceforge.net>
 * Copyright (C) 2012		Juanjo Menent		<jmenent@2byte.es>
 * Copyright (C) 2013		Florian Henry		<florian.henry@ope-concept.pro>
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
 *	\file       htdocs/core/modules/project/pdf/doc_generic_project_odt.modules.php
 *	\ingroup    project
 *	\brief      File of class to build ODT documents for third parties.
 */
require_once DOL_DOCUMENT_ROOT.'/core/modules/project/modules_project.php';
require_once DOL_DOCUMENT_ROOT.'/projet/class/project.class.php';
require_once DOL_DOCUMENT_ROOT.'/projet/class/task.class.php';
require_once DOL_DOCUMENT_ROOT.'/contact/class/contact.class.php';
require_once DOL_DOCUMENT_ROOT.'/user/class/user.class.php';
require_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/doc.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
if (!empty($conf->propal->enabled)) {
    require_once DOL_DOCUMENT_ROOT.'/comm/propal/class/propal.class.php';
}
if (!empty($conf->facture->enabled)) {
    require_once DOL_DOCUMENT_ROOT.'/compta/facture/class/facture.class.php';
}
if (!empty($conf->facture->enabled)) {
    require_once DOL_DOCUMENT_ROOT.'/compta/facture/class/facture-rec.class.php';
}
if (!empty($conf->commande->enabled)) {
    require_once DOL_DOCUMENT_ROOT.'/commande/class/commande.class.php';
}
if (!empty($conf->fournisseur->enabled)) {
    require_once DOL_DOCUMENT_ROOT.'/fourn/class/fournisseur.facture.class.php';
}
if (!empty($conf->fournisseur->enabled)) {
    require_once DOL_DOCUMENT_ROOT.'/fourn/class/fournisseur.commande.class.php';
}
if (!empty($conf->contrat->enabled)) {
    require_once DOL_DOCUMENT_ROOT.'/contrat/class/contrat.class.php';
}
if (!empty($conf->ficheinter->enabled)) {
    require_once DOL_DOCUMENT_ROOT.'/fichinter/class/fichinter.class.php';
}
if (!empty($conf->deplacement->enabled)) {
    require_once DOL_DOCUMENT_ROOT.'/compta/deplacement/class/deplacement.class.php';
}
if (!empty($conf->agenda->enabled)) {
    require_once DOL_DOCUMENT_ROOT.'/comm/action/class/actioncomm.class.php';
}

dol_include_once('/place/core/modules/place/modules_place.php');

/**
 *	Class to build documents using ODF templates generator.
 */
class doc_generic_place_odt extends ModelePdfPlace
{
    public $emetteur;    // Objet societe qui emet

    public $phpmin = array(5, 2, 0);    // Minimum version of PHP required by module
    public $version = 'dolibarr';

    /**
     *	Constructor.
     *
     *  @param		DoliDB		$db      Database handler
     */
    public function __construct($db)
    {
        global $conf,$langs,$mysoc;

        $langs->load('main');
        $langs->load('place@place');

        $this->db = $db;
        $this->name = 'ODT templates';
        $this->description = $langs->trans('DocumentModelOdt');
        $this->scandir = 'PLACE_ADDON_PDF_ODT_PATH';    // Name of constant that is used to save list of directories to scan

        // Dimension page pour format A4
        $this->type = 'odt';
        $this->page_largeur = 0;
        $this->page_hauteur = 0;
        $this->format = array($this->page_largeur, $this->page_hauteur);
        $this->marge_gauche = 0;
        $this->marge_droite = 0;
        $this->marge_haute = 0;
        $this->marge_basse = 0;

        $this->option_logo = 1;                    // Affiche logo
        $this->option_tva = 0;                     // Gere option tva COMMANDE_TVAOPTION
        $this->option_modereg = 0;                 // Affiche mode reglement
        $this->option_condreg = 0;                 // Affiche conditions reglement
        $this->option_codeproduitservice = 0;      // Affiche code produit-service
        $this->option_multilang = 1;               // Dispo en plusieurs langues
        $this->option_escompte = 0;                // Affiche si il y a eu escompte
        $this->option_credit_note = 0;             // Support credit notes
        $this->option_freetext = 1;                   // Support add of a personalised text
        $this->option_draft_watermark = 0;           // Support add of a watermark on drafts

        // Recupere emetteur
        $this->emetteur = $mysoc;
        if (!$this->emetteur->pays_code) {
            $this->emetteur->pays_code = substr($langs->defaultlang, -2);
        }    // Par defaut, si n'etait pas defini
    }

    /**
     * Define array with couple substitution key => substitution value
     *
     * @param   Object          $object             Main object to use as data source
     * @param   Translate       $outputlangs        Lang object to use for output
     * @param   string          $array_key          Name of the key for return array
     * @return  array                               Array of substitution
     */
    function get_substitutionarray_object($object,$outputlangs,$array_key='object')
    {
        global $conf;

        return array(
        $array_key.'_id' => $object->id,
        $array_key.'_ref' => $object->ref,
        $array_key.'_description' => $object->description,
        $array_key.'_lat' => $object->lat,
        $array_key.'_lng' => $object->lng,
        $array_key.'_note_private' => $object->note_private,
        $array_key.'_note_public' => $object->note_public,
        );
    }

    /**
     *	Define array with couple substitution key => substitution value.
     *
     *	@param  array			$task				Task Object
     *	@param  Translate		$outputlangs        Lang object to use for output
     *
     *  @return	array								Return a substitution array
     */
    public function get_substitutionarray_buildings($building, $outputlangs)
    {
        global $conf;

        return array(
        'building_ref' => $task->ref,
        'building_fk_project' => $building->fk_project,
        'building_projectref' => $building->projectref,
        'building_projectlabel' => $building->projectlabel,
        'building_label' => $building->label,
        'building_description' => $building->description,
        'building_fk_parent' => $building->fk_parent,
        'building_duration' => $building->duration,
        'building_progress' => $building->progress,
        'building_public' => $building->public,
        'building_date_start' => dol_print_date($building->date_start, 'day'),
        'building_date_end' => dol_print_date($building->date_end, 'day'),
        'building_note_private' => $building->note_private,
        'building_note_public' => $building->note_public,
        );
    }

    /**
     *	Return description of a module.
     *
     *	@param	Translate	$langs      Lang object to use for output
     *
     *	@return string       			Description
     */
    public function info($langs)
    {
        global $conf,$langs;

        $langs->load('place@place');
        $langs->load('errors');

        $form = new Form($this->db);

        $texte = $this->description.".<br>\n";
        $texte .= '<form action="'.$_SERVER['PHP_SELF'].'" method="POST">';
        $texte .= '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
        $texte .= '<input type="hidden" name="action" value="setModuleOptions">';
        $texte .= '<input type="hidden" name="param1" value="PLACE_ADDON_PDF_ODT_PATH">';
        $texte .= '<table class="nobordernopadding" width="100%">';

        // List of directories area
        $texte .= '<tr><td>';
        $texttitle = $langs->trans('ListOfDirectories');
        $listofdir = explode(',', preg_replace('/[\r\n]+/', ',', trim($conf->global->PLACE_ADDON_PDF_ODT_PATH)));
        $listoffiles = array();
        foreach ($listofdir as $key => $tmpdir) {
            $tmpdir = trim($tmpdir);
            $tmpdir = preg_replace('/DOL_DATA_ROOT/', DOL_DATA_ROOT, $tmpdir);
            if (!$tmpdir) {
                unset($listofdir[$key]);
                continue;
            }
            if (!is_dir($tmpdir)) {
                $texttitle .= img_warning($langs->trans('ErrorDirNotFound', $tmpdir), 0);
            } else {
                $tmpfiles = dol_dir_list($tmpdir, 'files', 0, '\.(ods|odt)');
                if (count($tmpfiles)) {
                    $listoffiles = array_merge($listoffiles, $tmpfiles);
                }
            }
        }
        $texthelp = $langs->trans('ListOfDirectoriesForModelGenODT');
        // Add list of substitution keys
        $texthelp .= '<br>'.$langs->trans('FollowingSubstitutionKeysCanBeUsed').'<br>';
        $texthelp .= $langs->transnoentitiesnoconv('FullListOnOnlineDocumentation');    // This contains an url, we don't modify it

        $texte .= $form->textwithpicto($texttitle, $texthelp, 1, 'help', '', 1);
        $texte .= '<div><div style="display: inline-block; min-width: 100px; vertical-align: middle;">';
        $texte .= '<textarea class="flat" cols="60" name="value1">';
        $texte .= $conf->global->PLACE_ADDON_PDF_ODT_PATH;
        $texte .= '</textarea>';
        $texte .= '</div><div style="display: inline-block; vertical-align: middle;">';
        $texte .= '<input type="submit" class="button" value="'.$langs->trans('Modify').'" name="Button">';
        $texte .= '<br></div></div>';

        // Scan directories
        if (count($listofdir)) {
            $texte .= $langs->trans('NumberOfModelFilesFound').': <b>'.count($listoffiles).'</b>';
        }

        $texte .= '</td>';

        $texte .= '<td valign="top" rowspan="2" class="hideonsmartphone">';
        $texte .= $langs->trans('ExampleOfDirectoriesForModelGen');
        $texte .= '</td>';
        $texte .= '</tr>';

        $texte .= '</table>';
        $texte .= '</form>';

        return $texte;
    }

    /**
     *	Function to build a document on disk using the generic odt module.
     *
     *	@param	Project		$object					Object source to build document
     *	@param	Translate	$outputlangs			Lang output object
     * 	@param	string		$srctemplatepath	    Full path of source filename for generator using a template file
     *
     *	@return	int         						1 if OK, <=0 if KO
     */
    public function write_file($object, $outputlangs, $srctemplatepath)
    {
        global $user,$langs,$conf,$mysoc,$hookmanager;

        if (empty($srctemplatepath)) {
            dol_syslog('doc_generic_place::write_file parameter srctemplatepath empty', LOG_WARNING);

            return -1;
        }

        // Add odtgeneration hook
        if (!is_object($hookmanager)) {
            include_once DOL_DOCUMENT_ROOT.'/core/class/hookmanager.class.php';
            $hookmanager = new HookManager($this->db);
        }
        $hookmanager->initHooks(array('odtgeneration'));
        global $action;

        if (!is_object($outputlangs)) {
            $outputlangs = $langs;
        }
        $sav_charset_output = $outputlangs->charset_output;
        $outputlangs->charset_output = 'UTF-8';

        $outputlangs->load('main');
        $outputlangs->load('dict');
        $outputlangs->load('companies');
        $outputlangs->load('place@place');
        if ($conf->place->multidir_output[$object->entity]) {
            // If $object is id instead of object
            if (!is_object($object)) {
                $id = $object;
                $object = new Place($this->db);
                $result = $object->fetch($id);
                if ($result < 0) {
                    dol_print_error($this->db, $object->error);

                    return -1;
                }
            }

            $objectref = dol_sanitizeFileName($object->ref);
            $relativepathwithnofile = dol_sanitizeFileName($object->id.'-'.str_replace(' ', '-', $object->ref));
            $dir = $conf->place->multidir_output[$object->entity];
            if (!preg_match('/specimen/i', $objectref)) {
                $dir .= '/'.$relativepathwithnofile;
            }
            
            $file = $dir.'/'.$objectref.'.odt';
            if (!file_exists($dir)) {
                if (dol_mkdir($dir) < 0) {
                    $this->error = $langs->transnoentities('ErrorCanNotCreateDir', $dir);

                    return -1;
                }
            }

            if (file_exists($dir)) {
                //print "srctemplatepath=".$srctemplatepath;	// Src filename
                $newfile = basename($srctemplatepath);
                $newfiletmp = preg_replace('/\.od(t|s)/i', '', $newfile);
                $newfiletmp = preg_replace('/template_/i', '', $newfiletmp);
                $newfiletmp = preg_replace('/modele_/i', '', $newfiletmp);
                $newfiletmp = $objectref.'_'.$newfiletmp;
                //$file=$dir.'/'.$newfiletmp.'.'.dol_print_date(dol_now(),'%Y%m%d%H%M%S').'.odt';
                // Get extension (ods or odt)
                $newfileformat = substr($newfile, strrpos($newfile, '.') + 1);
                if (!empty($conf->global->MAIN_DOC_USE_TIMING)) {
                    $filename = $newfiletmp.'.'.dol_print_date(dol_now(), '%Y%m%d%H%M%S').'.'.$newfileformat;
                } else {
                    $filename = $newfiletmp.'.'.$newfileformat;
                }
                $file = $dir.'/'.$filename;
                //print "newdir=".$dir;
                //print "newfile=".$newfile;
                //print "file=".$file;
                //print "conf->place->dir_temp=".$conf->place->dir_temp;

                dol_mkdir($conf->place->dir_temp);

                $socobject = $object->thirdparty;

                // Make substitution
                $substitutionarray = array(
                '__FROM_NAME__' => $this->emetteur->nom,
                '__FROM_EMAIL__' => $this->emetteur->email,
                );
                complete_substitutions_array($substitutionarray, $langs, $object);

                // Open and load template
                require_once ODTPHP_PATH.'odf.php';
                try {
                    $odfHandler = new odf(
                        $srctemplatepath,
                        array(
                        'PATH_TO_TMP' => $conf->place->dir_temp,
                        'ZIP_PROXY' => 'PclZipProxy',    // PhpZipProxy or PclZipProxy. Got "bad compression method" error when using PhpZipProxy.
                        'DELIMITER_LEFT' => '{',
                        'DELIMITER_RIGHT' => '}',
                        )
                    );
                } catch (Exception $e) {
                    $this->error = $e->getMessage();

                    return -1;
                }
                // After construction $odfHandler->contentXml contains content and
                // [!-- BEGIN row.lines --]*[!-- END row.lines --] has been replaced by
                // [!-- BEGIN lines --]*[!-- END lines --]
                //print html_entity_decode($odfHandler->__toString());
                //print exit;

                // Make substitutions into odt of user info
                $tmparray = $this->get_substitutionarray_user($user, $outputlangs);
                foreach ($tmparray as $key => $value) {
                    try {
                        if (preg_match('/logo$/', $key)) { // Image
                            //var_dump($value);exit;
                            if (file_exists($value)) {
                                $odfHandler->setImage($key, $value);
                            } else {
                                $odfHandler->setVars($key, 'ErrorFileNotFound', true, 'UTF-8');
                            }
                        } else {    // Text
                            $odfHandler->setVars($key, $value, true, 'UTF-8');
                        }
                    } catch (OdfException $e) {
                    }
                }
                // Make substitutions into odt of mysoc
                $tmparray = $this->get_substitutionarray_mysoc($mysoc, $outputlangs);
                //var_dump($tmparray); exit;
                foreach ($tmparray as $key => $value) {
                    try {
                        if (preg_match('/logo$/', $key)) {    // Image
                            //var_dump($value);exit;
                            if (file_exists($value)) {
                                $odfHandler->setImage($key, $value);
                            } else {
                                $odfHandler->setVars($key, 'ErrorFileNotFound', true, 'UTF-8');
                            }
                        } else {    // Text
                            $odfHandler->setVars($key, $value, true, 'UTF-8');
                        }
                    } catch (OdfException $e) {
                    }
                }

                // Replace tags of object + external modules
                $tmparray = $this->get_substitutionarray_object($object, $outputlangs);
                complete_substitutions_array($tmparray, $outputlangs, $object);
                // Call the ODTSubstitution hook
                $parameters = array('file' => $file, 'object' => $object, 'outputlangs' => $outputlangs, 'substitutionarray' => &$tmparray);
                $reshook = $hookmanager->executeHooks('ODTSubstitution', $parameters, $this, $action);    // Note that $action and $object may have been modified by some hooks
                foreach ($tmparray as $key => $value) {
                    try {
                        if (preg_match('/logo$/', $key)) { // Image
                            if (file_exists($value)) {
                                $odfHandler->setImage($key, $value);
                            } else {
                                $odfHandler->setVars($key, 'ErrorFileNotFound', true, 'UTF-8');
                            }
                        } else {    // Text
                            $odfHandler->setVars($key, $value, true, 'UTF-8');
                        }
                    } catch (OdfException $e) {
                    }
                }

                // Replace labels translated
                $tmparray = $outputlangs->get_translations_for_substitutions();
                foreach ($tmparray as $key => $value) {
                    try {
                        $odfHandler->setVars($key, $value, true, 'UTF-8');
                    } catch (OdfException $e) {
                    }
                }

                // Call the beforeODTSave hook
                $parameters = array('odfHandler' => &$odfHandler, 'file' => $file, 'object' => $object, 'outputlangs' => $outputlangs);
                $reshook = $hookmanager->executeHooks('beforeODTSave', $parameters, $this, $action);    // Note that $action and $object may have been modified by some hooks

                // Write new file
                if (!empty($conf->global->MAIN_ODT_AS_PDF)) {
                    try {
                        $odfHandler->exportAsAttachedPDF($file);
                    } catch (Exception $e) {
                        $this->error = $e->getMessage();

                        return -1;
                    }
                } else {
                    try {
                        $odfHandler->saveToDisk($file);
                    } catch (Exception $e) {
                        $this->error = $e->getMessage();

                        return -1;
                    }
                }

                if (!empty($conf->global->MAIN_UMASK)) {
                    @chmod($file, octdec($conf->global->MAIN_UMASK));
                }

                $odfHandler = null;    // Destroy object

                return 1;   // Success
            } else {
                $this->error = $langs->transnoentities('ErrorCanNotCreateDir', $dir);

                return -1;
            }
        } else print 'eroooooooooooooooooooooooooooooooor';

        return -1;
    }
}
