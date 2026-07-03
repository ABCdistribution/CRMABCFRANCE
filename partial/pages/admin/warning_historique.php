<div class="card card-primary card-outline">
  <div class="card-header">
    <h5 class="m-0">Historique d'intégration des factures</h5>
  </div>
  <div class="card-body m-250" style="font-size:13px;">
    <div class="list-group list-items">
        <?php 
        foreach( admin::getHistoriqueIntegrationFactures() as $e ) {
            echo '
            <a href="#" class="list-group-item">
                <h4 class="list-group-item-heading">
                    <span class="glyphicon glyphicon-file"></span>
                    <span class="name">'.$e['file'].' </span>
                    <em class="float-right">il y a '.$e['from'].'</em>
                </h4>
                <p class="list-group-item-text">'.number_format($e['lines'],0,","," ").' lignes, le '.$e['date'].'</p>
            </a>
            ';
        }
        ?>
    </div>
  </div>
</div>