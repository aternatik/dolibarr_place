<?php
/* Module to manage locations, buildings, floors and rooms into Dolibarr ERP/CRM
 * Copyright (C) 2012	Florian HENRY 		<florian.henry@open-concept.pro>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * 	\file		admin/admin_place.php
 * 	\ingroup	place
 * 	\brief		This file is setup page of place module
 */

$res=0;
if (! $res && file_exists("../main.inc.php")) $res=@include '../main.inc.php';
if (! $res && file_exists("../../main.inc.php")) $res=@include '../../main.inc.php';
if (! $res && file_exists("../../../main.inc.php")) $res=@include '../../../main.inc.php';
if (! $res) die("Include of main fails");


// Libraries
require_once DOL_DOCUMENT_ROOT . "/core/lib/admin.lib.php";
dol_include_once('/place/lib/place.lib.php');

// Translations
$langs->load("place@place");
$langs->load("admin");

$error=0;

// Access control
if ( ! $user->admin) accessforbidden();

// Parameters
$action = GETPOST('action', 'alpha');
$value=GETPOST('value','alpha');

// Define constants for submodules that contains parameters (forms with param1, param2, ... and value1, value2, ...)
if ($action == 'setModuleOptions')
{
    $post_size=count($_POST);

    $db->begin();

    for($i=0;$i < $post_size;$i++)
    {
        if (array_key_exists('param'.$i,$_POST))
        {
            $param=GETPOST("param".$i,'alpha');
            $value=GETPOST("value".$i,'alpha');
            if ($param) $res = dolibarr_set_const($db,$param,$value,'chaine',0,'',$conf->entity);
            if (! $res > 0) $error++;
        }
    }
    if (! $error)
    {
        $db->commit();
        $mesg = "<font class=\"ok\">".$langs->trans("SetupSaved")."</font>";
    }
    else
    {
        $db->rollback();
        $mesg = "<font class=\"error\">".$langs->trans("Error")."</font>";
    }
}

// Activate a document generator module
if ($action == 'set')
{
    $label = GETPOST('label','alpha');
    $scandir = GETPOST('scandir','alpha');

    $type='place';
    $sql = "INSERT INTO ".MAIN_DB_PREFIX."document_model (nom, type, entity, libelle, description)";
    $sql.= " VALUES ('".$db->escape($value)."','".$type."',".$conf->entity.", ";
    $sql.= ($label?"'".$db->escape($label)."'":'null').", ";
    $sql.= (! empty($scandir)?"'".$db->escape($scandir)."'":"null");
    $sql.= ")";

    $resql=$db->query($sql);
    if (! $resql) dol_print_error($db);
}

// Disable a document generator module
if ($action== 'del')
{
    $type='place';
    $sql = "DELETE FROM ".MAIN_DB_PREFIX."document_model";
    $sql.= " WHERE nom='".$db->escape($value)."' AND type='".$type."' AND entity=".$conf->entity;
    $resql=$db->query($sql);
    if (! $resql) dol_print_error($db);
}

// Define default generator
if ($action == 'setdoc')
{
    $label = GETPOST('label','alpha');
    $scandir = GETPOST('scandir','alpha');

    $db->begin();

    if (dolibarr_set_const($db, "PLACE_ADDON_PDF_ODT_PATH",$value,'chaine',0,'',$conf->entity))
    {
        $conf->global->PLACE_ADDON_PDF_ODT_PATH = $value;
    }

    // On active le modele
    $type='place';
    $sql_del = "DELETE FROM ".MAIN_DB_PREFIX."document_model";
    $sql_del.= " WHERE nom = '".$db->escape(GETPOST('value','alpha'))."'";
    $sql_del.= " AND type = '".$type."'";
    $sql_del.= " AND entity = ".$conf->entity;
    dol_syslog("societe.php ".$sql);
    $result1=$db->query($sql_del);

    $sql = "INSERT INTO ".MAIN_DB_PREFIX."document_model (nom, type, entity, libelle, description)";
    $sql.= " VALUES ('".$db->escape($value)."', '".$type."', ".$conf->entity.", ";
    $sql.= ($label?"'".$db->escape($label)."'":'null').", ";
    $sql.= (! empty($scandir)?"'".$db->escape($scandir)."'":"null");
    $sql.= ")";
    dol_syslog("admin_place.php ".$sql);
    $result2=$db->query($sql);
    if ($result1 && $result2)
    {
        $db->commit();
    }
    else
    {
        dol_syslog("admin_place.php ".$db->lasterror(), LOG_ERR);
        $db->rollback();
    }
}



/*
 * View
 */
$page_name = "PlaceSetup";
llxHeader('', $langs->trans($page_name));

// Subheader
$linkback = '<a href="' . DOL_URL_ROOT . '/admin/modules.php">'
	. $langs->trans("BackToModuleList") . '</a>';
print_fiche_titre($langs->trans($page_name), $linkback);

// Configuration header
$head = placeAdminPrepareHead();
dol_fiche_head($head, 'settings', $langs->trans("Module110110Name"), 0,
	"place@place");

$dirplace=array('/place/core/modules/place/');

$form=new Form($db);


/*
 *  Document templates generators
*/
print '<br>';
print_titre($langs->trans("ModelModules"));

// Load array def with activated templates
$def = array();
$sql = "SELECT nom";
$sql.= " FROM ".MAIN_DB_PREFIX."document_model";
$sql.= " WHERE type = 'place'";
$sql.= " AND entity = ".$conf->entity;
$resql=$db->query($sql);
if ($resql)
{
    $i = 0;
    $num_rows=$db->num_rows($resql);
    while ($i < $num_rows)
    {
        $array = $db->fetch_array($resql);
        array_push($def, $array[0]);
        $i++;
    }
}
else
{
    dol_print_error($db);
}

print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';
print '<td width="140">'.$langs->trans("Name").'</td>';
print '<td>'.$langs->trans("Description").'</td>';
print '<td align="center" width="80">'.$langs->trans("Status").'</td>';
print '<td align="center" width="60">'.$langs->trans("ShortInfo").'</td>';
print '<td align="center" width="60">'.$langs->trans("Preview").'</td>';
print "</tr>\n";

foreach ($dirplace as $dirroot)
{
    $dir = dol_buildpath($dirroot.'doc/',0);

    $handle=@opendir($dir);
    if (is_resource($handle))
    {
        while (($file = readdir($handle))!==false)
        {

            if (preg_match('/\.modules\.php$/i',$file))
            {
                $name = substr($file, 4, dol_strlen($file) -16);
                $classname = substr($file, 0, dol_strlen($file) -12);

                try {
                    dol_include_once($dirroot.'doc/'.$file);
                }
                catch(Exception $e)
                {
                    dol_syslog($e->getMessage(), LOG_ERR);
                }

                $module = new $classname($db);
                $modulequalified=1;
                if (! empty($module->version)) {
                    if ($module->version == 'development'  && $conf->global->MAIN_FEATURES_LEVEL < 2) $modulequalified=0;
                    else if ($module->version == 'experimental' && $conf->global->MAIN_FEATURES_LEVEL < 1) $modulequalified=0;
                }
                if ($modulequalified)
                {
                    $var = !$var;
                    print '<tr '.$bc[$var].'><td width="100">';
                    print $module->name;
                    print "</td><td>\n";
                    if (method_exists($module,'info')) print $module->info($langs);
                    else print $module->description;
                    print '</td>';

                    // Activate / Disable
                    if (in_array($name, $def))
                    {
                        print "<td align=\"center\">\n";
                        //if ($conf->global->COMPANY_ADDON_PDF != "$name")
                        //{
                        print '<a href="'.$_SERVER["PHP_SELF"].'?action=del&value='.$name.'&scandir='.$module->scandir.'&label='.urlencode($module->name).'">';
                        print img_picto($langs->trans("Enabled"),'switch_on');
                        print '</a>';
                        //}
                        //else
                        //{
                            //	print img_picto($langs->trans("Enabled"),'on');
                            //}
                        print "</td>";
                        }
                        else
                        {
                            if (versioncompare($module->phpmin,versionphparray()) > 0)
                            {
                                print "<td align=\"center\">\n";
                                print img_picto(dol_escape_htmltag($langs->trans("ErrorModuleRequirePHPVersion",join('.',$module->phpmin))),'switch_off');
                                print "</td>";
                            }
                            else
                            {
                                print "<td align=\"center\">\n";
                                print '<a href="'.$_SERVER["PHP_SELF"].'?action=set&value='.$name.'&scandir='.$module->scandir.'&label='.urlencode($module->name).'">'.img_picto($langs->trans("Disabled"),'switch_off').'</a>';
                                print "</td>";
                            }
                        }

                        // Info
                        $htmltooltip =    ''.$langs->trans("Name").': '.$module->name;
                        $htmltooltip.='<br>'.$langs->trans("Type").': '.($module->type?$module->type:$langs->trans("Unknown"));
                        if ($module->type == 'pdf')
                        {
                            $htmltooltip.='<br>'.$langs->trans("Height").'/'.$langs->trans("Width").': '.$module->page_hauteur.'/'.$module->page_largeur;
                        }
                        $htmltooltip.='<br><br><u>'.$langs->trans("FeaturesSupported").':</u>';
                        $htmltooltip.='<br>'.$langs->trans("WatermarkOnDraft").': '.yn((! empty($module->option_draft_watermark)?$module->option_draft_watermark:''), 1, 1);

                        print '<td align="center" class="nowrap">';
                        print $form->textwithpicto('',$htmltooltip,1,0);
                        print '</td>';

                        // Preview
                        print '<td align="center" class="nowrap">';
                        if ($module->type == 'pdf')
                        {
                            $linkspec='<a href="'.$_SERVER["PHP_SELF"].'?action=specimen&module='.$name.'">'.img_object($langs->trans("Preview"),'bill').'</a>';
                        }
                        else
                        {
                            $linkspec=img_object($langs->trans("PreviewNotAvailable"),'generic');
                        }
                        print $linkspec;
                        print '</td>';

                        print "</tr>\n";
                    }
                }
            }
            closedir($handle);
        }
    }
    print '</table>';

llxFooter();
$db->close();
