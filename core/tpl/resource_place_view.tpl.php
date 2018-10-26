<?php
//var_dump($linked_resources);

$form = new Form($db);

if ((array) $linked_resources && count($linked_resources) > 0) {
    $var = false;

    // TODO: DEBUT DU TPL
    if ($mode == 'edit') {
        echo '<div class="tagtable centpercent noborder allwidth">';
        echo '<form class="tagtr liste_titre">';
        echo '<div class="tagtd">'.$langs->trans('Place').'</div>';
        echo '<div class="tagtd">'.$langs->trans('Busy').'</div>';
        echo '<div class="tagtd">'.$langs->trans('Mandatory').'</div>';
        echo '<div class="tagtd right">'.$langs->trans('Edit').'</div>';
        echo '</form>';
        //print '</div>';
    } else {
        echo '<div class="tagtable centpercent noborder allwidth">';
        echo '<form class="tagtr liste_titre">';
        echo '<div class="tagtd">'.$langs->trans('Place').'</div>';
        echo '<div class="tagtd">'.$langs->trans('Busy').'</div>';
        echo '<div class="tagtd">'.$langs->trans('Mandatory').'</div>';
        echo '<div class="tagtd right">'.$langs->trans('Edit').'</div>';
        echo '</form>';
        //print '</div>';
    }

    foreach ($linked_resources as $linked_resource) {
        $var = !$var;
        $object_resource = fetchObjectByElement($linked_resource['resource_id'], $linked_resource['resource_type']);
        if ($mode == 'edit' && $linked_resource['rowid'] == GETPOST('lineid')) {
            echo '<form class="tagtr '.($var == true ? 'pair' : 'impair').'" action="'.$_SERVER['PHP_SELF'].'?element='.$element.'&element_id='.$element_id.'" method="POST">';
            echo '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'" />';
            echo '<input type="hidden" name="id" value="'.$object->id.'" />';
            echo '<input type="hidden" name="action" value="update_linked_resource" />';
            echo '<input type="hidden" name="resource_type" value="'.$resource_type.'" />';
            echo '<input type="hidden" name="lineid" value="'.$linked_resource['rowid'].'" />';

            echo '<div class="tagtd">'.$object_resource->getNomUrl(1).'</div>';
            echo '<div class="tagtd">'.$form->selectyesno('busy', $linked_resource['busy'] ? 1 : 0, 1).'</div>';
            echo '<div class="tagtd">'.$form->selectyesno('mandatory', $linked_resource['mandatory'] ? 1 : 0, 1).'</div>';
            echo '<div class="tagtd right"><input type="submit" class="button" value="'.$langs->trans('Update').'"></div>';
            echo '</form>';
        } else {
            $style = '';
            if ($linked_resource['rowid'] == GETPOST('lineid')) {
                $style = 'style="background: orange;"';
            }

            echo '<div class="tagtr '.($var == true ? 'pair' : 'impair').'" '.$style.'>';

            echo '<div class="tagtd">';
            echo $object_resource->getNomUrl(1);
            echo '</div class="tagtd">';

            echo '<div class="tagtd">';
            echo $linked_resource['busy'] ? 1 : 0;
            echo '</div>';

            echo '<div class="tagtd">';
            echo $linked_resource['mandatory'] ? 1 : 0;
            echo '</div>';

            echo '<div class="tagtd right">';
            echo '<a href="'.$_SERVER['PHP_SELF'].'?action=delete_resource&element='.$element.'&element_id='.$element_id.'&lineid='.$linked_resource['rowid'].'">'.$langs->trans('Delete').'</a>';
            echo '<a href="'.$_SERVER['PHP_SELF'].'?mode=edit&resource_type='.$linked_resource['resource_type'].'&element='.$element.'&element_id='.$element_id.'&lineid='.$linked_resource['rowid'].'">'.$langs->trans('Edit').'</a>';
            echo '</div>';

            echo '</div>';
        }
    }
    echo '</div>';
} else {
    echo '<div class="warning">'.$langs->trans('NoPlaceResourceLinked').'</div>';
}
// FIN DU TPL
