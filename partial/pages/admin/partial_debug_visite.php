<?php
echo '
<div class="card ">
  <div class="card-header text-white bg-info">
    <strong>Visite</strong> :  <u>'.$c['magasin']['enseigne'].'</u> - <em>'.core::dateOutput($v['steps']['step_0']).'</em>
    <span class="float-right">
      <strong>'.$c['user']['displayname'].'</strong> ('.$c['user']['apk_version'].')
    </span>
  </div>
  <div class="card-body" style="font-size:11px;">
  <div class="row">
  <div class="col">
    <h5><span>Visite</span></h5>
    <table class="table">
      <tbody>
        <tr>
          <th>ID</th>
          <td>'.$v['id_visite'].'</td>
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
          <td>'.core::dateOutput($v['steps']['step_0']).'</td>
        </tr>
      </tbody>
    </table>
    <h5><span>Etat objet</span></h5>
    <table class="table">
      <tbody>
        <tr>
          <th>Visite envoyée</th>
          <td>'.($v['envoye'] == 1 ? 'Oui' : 'Non').'</td>
        </tr>
        <tr>
          <th>Visite en attente de synchronisation</th>
          <td>'.($v['queued'] == 1 ? 'Oui' : 'Non').'</td>
        </tr>
      </tbody>
    </table>
    </div>
    <div class="col">
';
if( !isset($v['dn']) ) {
  $v['dn'] = ["abc" => $v['dn_abc'], "concu" => $v['dn_concurence'] ];
}
$dn = $v['dn'];
if( empty($dn['abc']) && empty($dn['concu']) ) {
  echo '<p class="tc text-secondary">Non renseigné</p>';
}
else {
    echo '<h5><span>DN</span></h5>';
    echo '
    <table class="table table-condensed">
      <thead>
        <th>Type</th>
        <th>Marque</th>
        <th>Gamme</th>
        <th class="tc">Metrage</th>
      </thead>
      <tbody>';
        $tmp = [];
        foreach( $dn as $key => $subdn ) {
          foreach( $subdn as $ka=>$ea ) {
            $cd = ( $key == "abc" ? 'bg-abc' : 'bg-concu' );
            $tmp[] = '
            <tr class="'.$cd.'">
              <td>'.($key == "concu" ? "Concurence" : "ABC").'</td>
              <td>'.$ea['ma'].'</td>
              <td>'.$ea['ga'].'</td>
              <td class="tc">'.$ea['me'].' m</td>
            </tr>
            ';
          }
        }
        echo implode($tmp);
        echo '</tbody></table>';
}

if( !isset($v['promos']) ) $v['promos'] = [];
$st = [];
if( !empty($v['promos']) )
  echo '<h5><span>OP</span></h5>';
foreach( $v['promos'] as $promo ) {
  $p = promo::get($promo['id_as400']);
  if( !$p ) continue;
  $st[] = '
    <div class="card clat">
      <div class="card-body">
        <i class="fas fa-check green"></i>'.$p['libelle'].' <em class="float-right text-secondary">'.$p['id_as400'].'</em><br/>
      </div>
    </div>
  ';
}
echo implode($st);


echo'
    </div>
    </div>
  </div>
</div>';
