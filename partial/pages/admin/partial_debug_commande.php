<?php

$txt = [];
$txt[] = "Détails franco : ".core::getReason($c['fp_raison']);
$txt[] = "Commande CRM #".$id_commande;
$txt[] = "Magasin : ".$c['magasin']['enseigne'].' - '.$c['magasin']['id_as400'];
$txt[] = "Commercial : ".implode(" - ",$c['user']);
$txt[] = "Date livraison souhaitee : ".core::dateOutput($c['date_liv_estimee']);
$txt[] = "Date prochaine visite : ".core::dateOutput($c['date_next_commande']);
$txt[] = "Produits :";
foreach( $c['produits'] as $a=>$b ) {
  $txt[] = $b['id_as400'].' x '.$b['qte'];
}


echo '
<div class="card ">
  <div class="card-header text-white bg-primary">
    <strong>Commande</strong> :  <u>'.$c['magasin']['enseigne'].'</u> - <em>'.core::dateOutput($c['date_creation']).'</em>
    <span class="float-right">
       <strong>'.$c['user']['displayname'].'</strong> ('.$c['user']['apk_version'].')
    </span>
  </div>
  <div class="card-body" style="font-size:11px;">
  <div class="row">
  <div class="col">
    <h5><span>Objet commande</span></h5>
    <table class="table">
      <tbody>
        <tr>
          <th>ID</th>
          <td>'.$id_commande.'</td>
        </tr>
        <tr>
          <th>Magasin</th>
          <td>'.$c['magasin']['enseigne'].'</td>
        </tr>
        <tr>
          <th>Commercial</th>
          <td>'.$c['user']['displayname'].'</td>
        </tr>
        <tr>
          <th>Date creation</th>
          <td>'.core::dateOutput($c['date_creation']).'</td>
        </tr>
        <tr>
          <th>Date livraison</th>
          <td>'.core::dateOutput($c['date_liv_estimee']).'</td>
        </tr>
        <tr>
          <th>Date prochaine visite</th>
          <td>'.core::dateOutput($c['date_next_commande']).'</td>
        </tr>
        <tr>
          <th>Présence d\'une visite</th>
          <td>'.($c['no_visit'] == false ? 'Oui' : 'Non').'</td>
        </tr>
        <tr>
          <th>Pas de visite car</th>
          <td>'.$c['no_visit_reason'].'</td>
        </tr>
        <tr>
          <th>ID visite liée</th>
          <td>'.$c['visite']['id_visite'].'</td>
        </tr>
        <tr>
          <th>Fraco de port non atteints car</th>
          <td>'.$c['fp_raison'].'</td>
        </tr>
      </tbody>
    </table>
    <h5><span>Etat objet</span></h5>
    <table class="table">
      <tbody>
        <tr>
          <th>Commande envoyée</th>
          <td>'.($c['envoye'] == 1 ? 'Oui' : 'Non').'</td>
        </tr>
        <tr>
          <th>Commande en attente</th>
          <td>'.($c['attente'] == 1 ? 'Oui' : 'Non').'</td>
        </tr>
        <tr>
          <th>Commande en attente de synchronisation</th>
          <td>'.($c['queued'] == 1 ? 'Oui' : 'Non').'</td>
        </tr>
      </tbody>
    </table>
    <h5><span>Export</span></h5>
    <textarea class="form-control" style="height:150px">'.implode("\r\n",$txt).'</textarea>
    </div>1
    <div class="col-5">
      <h5><span>Produits</span></h5>
      <ul class="list-group list-group-flush">
      ';
    foreach( $c['produits'] as $a=>$b ) {
      echo '<li class="list-group-item">'.$b['qte'].' x '.$b['libelle'].' ('.$b['id_as400'].')</li>';
    }

echo '
      </ul>
    </div>
    </div>
  </div>
</div>';
