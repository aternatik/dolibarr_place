<?php


//$langs->load($resource_type);

$form = new Form($db);
if(!class_exists('FormPlace'))
	dol_include_once('/place/class/html.formplace.class.php');
$formplace = new FormPlace($db);

$out .= '<div class="tagtable centpercent border allwidth">';

$out .= '<form class="tagtr '.($var==true?'pair':'impair').'" action="'.$_SERVER["PHP_SELF"].'" method="POST">';
$out .= '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
$out .= '<input type="hidden" name="action" value="add_resource_place">';
$out .= '<input type="hidden" name="element" value="'.$element.'">';
$out .= '<input type="hidden" name="element_id" value="'.$element_id.'">';
$out .= '<input type="hidden" name="resource_type" value="'.$resource_type.'">';


// Place
$out .= '<div class="tagtd">'.$langs->trans("SelectPlace").'</div><div>';
$events=array();
$out .= $formplace->select_place_list('','fk_resource_place','',1,1,0,$events);
$out .= '</div>';

$out .= '<div class="tagtd"><label>'.$langs->trans('Busy').'</label> '.$form->selectyesno('busy',$linked_resource['busy']?1:0,1).'</div>';
$out .= '<div class="tagtd"><label>'.$langs->trans('Mandatory').'</label> '.$form->selectyesno('mandatory',$linked_resource['mandatory']?1:0,1).'</div>';

$out .= '<div class="tagtd">';
$out .='<input type="submit" id="add-resource-place" class="button" value="'.$langs->trans("Add").'"';
$out .=' />';
$out .='<input type="submit" name="cancel" class="button" value="'.$langs->trans("Cancel").'" />';
$out .= '</div>';

$out .='</form>';

$out .= '</div>';
$out .= '<br />';

print $out;



