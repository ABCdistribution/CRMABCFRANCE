<?php
echo '<h1>'.l('page-validation-planning-titre').'</h1>';

global $params;
if( isset($params[1]) ) {
    showValidationTournee($params[1]);
    return;
}

/** Home  */
$liste = planning::getTourneesAValider();
if( empty($liste) ) {
    echo '<p class="text-muted text-center" style="margin-top:150px">'.l('page-validation-planning-vide').'</p>';
    return;
}

echo '<p class="info-val">'.count($liste).' '.l('page-validation-planning-a-valider').'.</p>';
echo '<div class="list-group list-validation">';
foreach( $liste as $k=>$e ) {
    echo ' <a href="'.URL.'ValidationPlanning/'.$e['id'].'" class="list-group-item list-group-item-action">';
    echo '<div class="d-flex w-100 justify-content-between">
        <h5 class="mb-1">'.user::getNameFromIdRepr( $e['id_repr']).'</h5>
        <small class="text-muted">Il y à '.core::dateFrom($e['date_creation'],true).', '.l('date-le').' '.core::dateOutput($e['date_creation'],true).'</small>
    </div></a>
    ';
}
echo '</div>';


/** Page dfe validation */
function showValidationTournee( $id ) {
    global $db;
    $t = planning::getPlanningTournee($id);
    if( !$t ) {
        echo '<p class="text-muted text-center" style="margin-top:150px">'.l('page-validation-planning-erreur').'</p>';
        return;
    }
    $user = user::getFromIdRepr($t['id_repr']);
    echo '<h4>'.l('page-validation-planning-plan-de').' '.$user['displayname'].' '.l('page-validation-planning-envoye-le').' '.core::dateOutput($t['date_creation'],true);
    echo '<a class="btn btn-default btn-sm float-right" href="'.URL.'ValidationPlanning"><i class="fas fa-long-arrow-alt-left"></i> '.l('bouton-retour').'</a></h4>';

    parse_str($t['tournee'],$p);

    $current = [];
    $db->execute("SELECT * FROM tournee WHERE id_repr = '".$t['id_repr']."' ");
    while( $r = $db->assoc() )
        $current[$r['id_as400']] = $r;
    
    echo '
    <style>
        #tablePlanning em {
            color: red;
            text-decoration: line-through;
        }
        #tablePlanning u {
            color: green;
            text-decoration: underline;
        }
    </style>
    
    <div class="card"><div class="card-header"><h5 class="m-0">'.l('page-validation-planning-magasins-prevus').'</h5>
        </div><div class="card-body"><table class="table" id="tablePlanning"><thead>
        <tr>
            <th>'.l('page-validation-planning-magasins-table-code').'</th>
            <th>'.l('page-validation-planning-magasins-table-magasin').'</th>
            <th>'.l('page-validation-planning-magasins-table-rec-promoteur').'</th>
            <th>'.l('page-validation-planning-magasins-table-rec-fiche').'</th>
            <th>'.l('page-validation-planning-magasins-table-calcul').'</th>
        </tr>
    </thead><tbody>    
    ';
    $tmp = [];
    $ids_presents = [];
    $jours_manquants = [];

    foreach( $p as $e ) {

        if( $e['days'] == "" ) {
            $jours_manquants[] = $e['id_as400'];
            continue;
        }
        
        $db->execute("
            SELECT
                p.libelle
            FROM 
                ref_client_periodicite rcp
                LEFT JOIN periodicite p ON rcp.id_periodicite = p.id
            WHERE
                id_client_as400 = '".$e['id_as400']."'
        ");
        $dv = $db->num() ? $db->assoc()['libelle'] : "??";


        $when = l('page-validation-planning-tous-les').' <strong>'.$e['days'].'</strong>, '.l('page-validation-planning-toutes-les').' '.$e['weeks'].' '.l('date-semaine');
        if( isset($current[$e['id_as400']]) ){
            $o = $current[$e['id_as400']];
            if( $o['days'] != $e['days'] || $o['weeks'] != $e['weeks'] ) {
                $when = '<em>'.l('page-validation-planning-tous-les').' <strong>'.$o['days'].'</strong>, '.l('page-validation-planning-toutes-les').' '.$o['weeks'].' '.l('date-semaine').'</em><br/>';
                $when .= l('page-validation-planning-tous-les').' <strong>'.$e['days'].'</strong>, '.l('page-validation-planning-toutes-les').' '.$e['weeks'].' '.l('date-semaine').'';
            }
        }
        else {
            $when = '✨ <u>'.l('page-validation-planning-tous-les').' '.$e['days'].', '.l('page-validation-planning-toutes-les').' '.$e['weeks'].' '.l('date-semaine').'</u>';
        }

        $tmp[] = trim('
            <tr>
                <td>'.$e['id_as400'].'</td>
                <td>'.client::getByCode($e['id_as400'])['enseigne'].'</td>
                <td>'.$when.'</td>
                <td>'.$dv.'</td>
                <td>Semaine '.$e['start'].', '.($e['annee']??"").'</td>
            </tr>
        ');
        $ids_presents[] = intval($e['id_as400']);
    }
    echo implode($tmp);
    echo '</tbody></table></div></div>';
    echo '
    <script>
        $( () => {
            $("#tablePlanning").DataTable({
                order: [[1, \'asc\']],
            });
        })
    </script>';

    
    if( count($ids_presents) > 0 ) {
        $db->execute("
            SELECT 
                rc.id_as400,
                rc.enseigne,
                p.libelle
            FROM 
                ref_client rc
                LEFT JOIN ref_client_periodicite rcp ON rcp.id_client_as400 = rc.id_as400
                LEFT JOIN periodicite p ON rcp.id_periodicite = p.id
            WHERE 
                rc.id_commercial_1 = '".$user['id_repr']."' 
                AND CAST(rc.id_as400 as UNSIGNED) NOT IN (".implode(',',$ids_presents).")
        ");
        if( !$db->num() ) {
            echo '<p class="text-muted text-center" style="margin-top:150px">'.l('page-validation-planning-all-clients').'</p>';
        }
        else {
            $datas = $db->get();
        
            echo '<div class="card"><div class="card-header"><h5 class="m-0">'.l('page-validation-planning-client-not').'</h5>
            </div><div class="card-body">
            <table class="table" id="tablePlanning2">
                <thead>
                    <tr>
                        <th>'.l('page-validation-planning-magasins-table-code').'</th>
                        <th>'.l('page-validation-planning-magasins-table-magasin').'</th>
                        <th>'.l('page-validation-planning-magasins-table-raison').'</th>
                        <th>'.l('page-validation-planning-magasins-table-rec-fiche').'</th>
                    </tr>
                </thead>
                <tbody>    
            ';
        
            $tmp = [];
            foreach( $datas as $e ) {
                $tmp[] = trim('
                    <tr>
                        <td>'.$e['id_as400'].'</td>
                        <td>'.$e['enseigne'].'</td>
                        <td>'.( in_array($e['id_as400'],$jours_manquants) ? l('page-validation-planning-jours-manquants') : '' ).'</td>
                        <td>'.$e['libelle'].'</td>
                    </tr>
                ');
                $ids_presents[] = intval($e['id_as400']);
            }
            echo implode($tmp);
            echo '</tbody></table></div>';
            echo '</div>';
            echo '
            <script>
            $( () => {
                $("#tablePlanning2").DataTable({
                    order: [[1, \'asc\']],
                });
            })
            </script>'; 
        }
    }
        


    echo '    
    <div class="valid-bloc-wrapper">

        <p class="t">'.l('page-validation-planning-validation').''.$user['displayname'].' ?</p>

        <div class="row">
            <div class="col-md-6 text-center">
                <button class="btn btn-danger" onclick="refusePlanning('.$id.')">'.l('bouton-refuser').'</button>
            </div>
            <div class="col-md-6 text-center">
                <button class="btn btn-success" onclick="acceptPlanning('.$id.')">'.l('bouton-accepter').'</button>
            </div>
        </div>

    </div>
    ';

}

?>

