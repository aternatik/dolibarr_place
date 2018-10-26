<?php

$resource_type = 'room@place';
$langs->load('place@place');

$morehtmlright = '';
$out = load_fiche_titre($langs->trans('AddRoom'), $morehtmlright, 'room@place');

$form = new Form($db);
if (!class_exists('FormPlace')) {
    dol_include_once('/place/class/html.formplace.class.php');
}
$formplace = new FormPlace($db);

$out .= '<div class="tagtable centpercent border allwidth">';

$out .= '<form class="tagtr '.($var == true ? 'pair' : 'impair').'" action="'.$_SERVER['PHP_SELF'].'" method="POST">';
$out .= '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
$out .= '<input type="hidden" name="action" value="add_resource_room">';
$out .= '<input type="hidden" name="element" value="'.$element.'">';
$out .= '<input type="hidden" name="element_id" value="'.$element_id.'">';
$out .= '<input type="hidden" name="resource_type" value="'.$resource_type.'">';

// Place & Room
$out .= '<div class="tagtd">'.$langs->trans('Place').'</div>';
$out .= '<div>';
if (GETPOST('fk_resource_place', 'int') > 0) {
    if (!class_exists('Place')) {
        dol_include_once('/place/class/place.class.php');
    }
    $room = new Place($db);
    $room->fetch(GETPOST('fk_resource_place', 'int'));
    $out .= $room->getNomUrl(1);
    $out .= '<input type="hidden" name="fk_resource_place" value="'.GETPOST('fk_resource_place', 'int').'">';
} else {
    $events = array();
    $events[] = array('method' => 'getRooms', 'url' => dol_buildpath('/place/core/ajax/rooms.php', 1), 'htmlname' => 'fk_resource_room', 'params' => array());
    $out .= $formplace->select_place_list('', 'fk_resource_place', '', 1, 1, 0, $events);
}

$out .= '</div>';
$out .= '<div  class="tagtd">'.$langs->trans('Room').'</div>';
$out .= '<div>';
$out .= $formplace->selectrooms(GETPOST('fk_resource_room', 'int'), GETPOST('fk_resource_room'), 'fk_resource_room', 1);
$out .= '</div>';

$out .= '<div class="tagtd"><label>'.$langs->trans('Busy').'</label> '.$form->selectyesno('busy', $linked_resource['busy'] ? 1 : 0, 1).'</div>';
$out .= '<div class="tagtd"><label>'.$langs->trans('Mandatory').'</label> '.$form->selectyesno('mandatory', $linked_resource['mandatory'] ? 1 : 0, 1).'</div>';
$out .= '<div>';
$out .= '<input type="submit" id="add-resource-room" class="button" value="'.$langs->trans('Add').'"';
$out .= ' />';
$out .= '<input type="submit" name="cancel" class="button" value="'.$langs->trans('Cancel').'" />';

$out .= '</div>';

$out .= '</form>';
$out .= '</div>';

echo $out;
