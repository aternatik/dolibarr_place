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
 * 	\brief		This file is setup page of place module.
 */
$res = 0;
if (!$res && file_exists('../main.inc.php')) {
    $res = @include '../main.inc.php';
}
if (!$res && file_exists('../../main.inc.php')) {
    $res = @include '../../main.inc.php';
}
if (!$res && file_exists('../../../main.inc.php')) {
    $res = @include '../../../main.inc.php';
}
if (!$res) {
    die('Include of main fails');
}

// Libraries
require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';
dol_include_once('/place/lib/place.lib.php');

// Translations
$langs->load('place@place');
$langs->load('admin');

$error = 0;

// Access control
if (!$user->admin) {
    accessforbidden();
}

// Parameters
$action = GETPOST('action', 'alpha');
$value = GETPOST('value', 'alpha');

// Define constants for submodules that contains parameters (forms with param1, param2, ... and value1, value2, ...)
if ($action == 'setModuleOptions') {
    $post_size = count($_POST);

    $db->begin();

    for ($i = 0; $i < $post_size; ++$i) {
        if (array_key_exists('param'.$i, $_POST)) {
            $param = GETPOST('param'.$i, 'alpha');
            $value = GETPOST('value'.$i, 'alpha');
            if ($param) {
                $res = dolibarr_set_const($db, $param, $value, 'chaine', 0, '', $conf->entity);
            }
            if (!$res > 0) {
                $error++;
            }
        }
    }
    if (!$error) {
        $db->commit();
        $mesg = '<font class="ok">'.$langs->trans('SetupSaved').'</font>';
    } else {
        $db->rollback();
        $mesg = '<font class="error">'.$langs->trans('Error').'</font>';
    }
}

// Activate a document generator module
if ($action == 'set') {
    $label = GETPOST('label', 'alpha');
    $scandir = GETPOST('scandir', 'alpha');

    $type = 'place';
    $sql = 'INSERT INTO '.MAIN_DB_PREFIX.'document_model (nom, type, entity, libelle, description)';
    $sql .= " VALUES ('".$db->escape($value)."','".$type."',".$conf->entity.', ';
    $sql .= ($label ? "'".$db->escape($label)."'" : 'null').', ';
    $sql .= (!empty($scandir) ? "'".$db->escape($scandir)."'" : 'null');
    $sql .= ')';

    $resql = $db->query($sql);
    if (!$resql) {
        dol_print_error($db);
    }
}

// Disable a document generator module
if ($action == 'del') {
    $type = 'place';
    $sql = 'DELETE FROM '.MAIN_DB_PREFIX.'document_model';
    $sql .= " WHERE nom='".$db->escape($value)."' AND type='".$type."' AND entity=".$conf->entity;
    $resql = $db->query($sql);
    if (!$resql) {
        dol_print_error($db);
    }
}

/*
 * View
 */
$page_name = 'PlaceSetup';
llxHeader('', $langs->trans($page_name));

// Subheader
$linkback = '<a href="'.DOL_URL_ROOT.'/admin/modules.php">'
    .$langs->trans('BackToModuleList').'</a>';
print load_fiche_titre($langs->trans($page_name), $linkback);

// Configuration header
$head = placeAdminPrepareHead();
dol_fiche_head($head, 'settings', $langs->trans('Module110110Name'), 0,
    'place@place');

$dirplace = array('/place/core/modules/place/');

$form = new Form($db);

/*
 *  Document templates generators
*/
echo '<br>';
print_titre($langs->trans('ModelModules'));

// Load array def with activated templates
$def = array();
$sql = 'SELECT nom';
$sql .= ' FROM '.MAIN_DB_PREFIX.'document_model';
$sql .= " WHERE type = 'place'";
$sql .= ' AND entity = '.$conf->entity;
$resql = $db->query($sql);
if ($resql) {
    $i = 0;
    $num_rows = $db->num_rows($resql);
    while ($i < $num_rows) {
        $array = $db->fetch_array($resql);
        array_push($def, $array[0]);
        ++$i;
    }
} else {
    dol_print_error($db);
}

echo '<table class="noborder" width="100%">';
echo '<tr class="liste_titre">';
echo '<td width="140">'.$langs->trans('Name').'</td>';
echo '<td>'.$langs->trans('Description').'</td>';
echo '<td align="center" width="80">'.$langs->trans('Status').'</td>';
echo '<td align="center" width="60">'.$langs->trans('ShortInfo').'</td>';
echo '<td align="center" width="60">'.$langs->trans('Preview').'</td>';
echo "</tr>\n";

foreach ($dirplace as $dirroot) {
    $dir = dol_buildpath($dirroot.'doc/', 0);

    $handle = @opendir($dir);
    if (is_resource($handle)) {
        while (($file = readdir($handle)) !== false) {
            if (preg_match('/\.modules\.php$/i', $file)) {
                $name = substr($file, 4, dol_strlen($file) - 16);
                $classname = substr($file, 0, dol_strlen($file) - 12);

                try {
                    dol_include_once($dirroot.'doc/'.$file);
                } catch (Exception $e) {
                    dol_syslog($e->getMessage(), LOG_ERR);
                }

                $module = new $classname($db);
                $modulequalified = 1;
                if (!empty($module->version)) {
                    if ($module->version == 'development' && $conf->global->MAIN_FEATURES_LEVEL < 2) {
                        $modulequalified = 0;
                    } elseif ($module->version == 'experimental' && $conf->global->MAIN_FEATURES_LEVEL < 1) {
                        $modulequalified = 0;
                    }
                }
                if ($modulequalified) {
                    $var = !$var;
                    echo '<tr '.$bc[$var].'><td width="100">';
                    echo $module->name;
                    echo "</td><td>\n";
                    if (method_exists($module, 'info')) {
                        print $module->info($langs);
                    } else {
                        print $module->description;
                    }
                    echo '</td>';

                    // Activate / Disable
                    if (in_array($name, $def)) {
                        echo "<td align=\"center\">\n";
                        //if ($conf->global->COMPANY_ADDON_PDF != "$name")
                        //{
                        echo '<a href="'.$_SERVER['PHP_SELF'].'?action=del&value='.$name.'&scandir='.$module->scandir.'&label='.urlencode($module->name).'">';
                        echo img_picto($langs->trans('Enabled'), 'switch_on');
                        echo '</a>';
                        //}
                        //else
                        //{
                            //	print img_picto($langs->trans("Enabled"),'on');
                            //}
                        echo '</td>';
                    } else {
                        if (versioncompare($module->phpmin, versionphparray()) > 0) {
                            echo "<td align=\"center\">\n";
                            echo img_picto(dol_escape_htmltag($langs->trans('ErrorModuleRequirePHPVersion', implode('.', $module->phpmin))), 'switch_off');
                            echo '</td>';
                        } else {
                            echo "<td align=\"center\">\n";
                            echo '<a href="'.$_SERVER['PHP_SELF'].'?action=set&value='.$name.'&scandir='.$module->scandir.'&label='.urlencode($module->name).'">'.img_picto($langs->trans('Disabled'), 'switch_off').'</a>';
                            echo '</td>';
                        }
                    }

                        // Info
                        $htmltooltip = ''.$langs->trans('Name').': '.$module->name;
                    $htmltooltip .= '<br>'.$langs->trans('Type').': '.($module->type ? $module->type : $langs->trans('Unknown'));
                    if ($module->type == 'pdf') {
                        $htmltooltip .= '<br>'.$langs->trans('Height').'/'.$langs->trans('Width').': '.$module->page_hauteur.'/'.$module->page_largeur;
                    }
                    $htmltooltip .= '<br><br><u>'.$langs->trans('FeaturesSupported').':</u>';
                    $htmltooltip .= '<br>'.$langs->trans('WatermarkOnDraft').': '.yn((!empty($module->option_draft_watermark) ? $module->option_draft_watermark : ''), 1, 1);

                    echo '<td align="center" class="nowrap">';
                    echo $form->textwithpicto('', $htmltooltip, 1, 0);
                    echo '</td>';

                        // Preview
                        echo '<td align="center" class="nowrap">';
                    if ($module->type == 'pdf') {
                        $linkspec = '<a href="'.$_SERVER['PHP_SELF'].'?action=specimen&module='.$name.'">'.img_object($langs->trans('Preview'), 'bill').'</a>';
                    } else {
                        $linkspec = img_object($langs->trans('PreviewNotAvailable'), 'generic');
                    }
                    echo $linkspec;
                    echo '</td>';

                    echo "</tr>\n";
                }
            }
        }
        closedir($handle);
    }
}
    echo '</table>';

llxFooter();
$db->close();
