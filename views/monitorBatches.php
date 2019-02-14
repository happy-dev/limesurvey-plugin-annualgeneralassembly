<div id="monitor-batches-page">
  <h3 class='pagetitle'><?php echo gT("Gestion des votes") ?></h3>

<?php
$html  = '';

if (count($batches) > 0) {
  $html .= '<form method="post" action="'. $href .'" id="monitor-batches-form">';
  $html .=  '<input type="hidden" value="'. Yii::app()->request->csrfToken .'" name="YII_CSRF_TOKEN">';
  $html .=  '<input type="hidden" type="text" name="batch-name" id="batch-name"/>';
  $html .=  '<table class="table table-bordered table-hover table-condensed">';
  $html .=   '<tr class="active">';
  $html .=     '<td><strong>Nom</strong></td>';
  //*** Added by Nathanaël Drouard ***/
  $html .=     '<td><strong>Collège</strong></td>';
  $html .=     '<td><strong>Nombre de votes</strong></td>';
  $html .=     '<td><strong>Date</strong></td>';
  $html .=     '<td><strong>Supprimer</strong></td>';
  $html .=   '</tr>';

  foreach($batches as $batch) {
    $html .= '<tr>';
    $html .=  '<td>'. $batch['startlanguage'] .'</td>';
    //*** Added by Nathanaël Drouard ***/
    $html .=  '<td>'. $batch['college'] .'</td>';
    $html .=  '<td>'. $batch['count'] .'</td>';
    $html .=  '<td>'. $batch['submitdate'] .'</td>';
    $html .=  '<td><a href="#" data-toggle="modal" data-target="#confirm-deletion-modal" data-name="'. $batch['startlanguage'] .'" class="delete-btn text-danger h2">&times;</a></td>';
    $html .= '</tr>';
  }

  $html .=  '</table>';
  $html .= '</form>';
}
else {
  $html .= '<p>Aucun vote renseigné manuellement pour ce scrutin.</p>';
}

echo $html;
?>

  <div id="confirm-deletion-modal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title">Confirmez-vous la suppression de ces votes ?</h4>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">J'annule</button>
          <button type="button" class="btn btn-primary" id="i-confirm">Je confirme</button>
        </div>
      </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
  </div><!-- /.modal -->
</div><!-- #monitor-batches-page -->

