<?php if( !securite::can(3) ) return core::restricted();?>
<?php
$id_user = core::getParamId();
if( !$id_user ) return core::error404();
$user = user::getFullUser( $id_user );
if( !$user ) return core::error404();
?>


<div>

  <!-- Nav tabs -->
  <ul class="nav nav-tabs" role="tablist">
    <li role="presentation"><a class="active" href="#backoffice" aria-controls="backoffice" role="tab" data-toggle="tab">Backoffice GESCOM</a></li>
    <li role="presentation"><a href="#apk" aria-controls="apk" role="tab" data-toggle="tab">Application mobile</a></li>
  </ul>

  <!-- Tab panes -->
  <div class="tab-content">
    <div role="tabpanel" class="tab-pane active" id="backoffice">

    	<div class="row">
    		<div class="col-lg-6">

				<div class="card card-primary card-outline">
					<div class="card-header">
						<h5 class="m-0">Connexions au backoffice</h5>
					</div>
					<div class="card-body">
						<table class="table table-condensed table-striped table-bordered table-hover nowrap dataTable">
							<thead>
								<tr>
									<th>#</th>
									<th>Action</th>
									<th>Date</th>
								</tr>
							</thead>
							<tbody>
								<?php
								$t = [];
								foreach( $user['histo']['login'] as $k=>$e )
									$t[] = '<tr><td>'.$k.'</td><td>Authentification</td><td>'.core::dateOutput($e['date_creation'], true).'</td></tr>';
								echo implode($t);
								?>
							</tbody>
						</table>
					</div>
				</div>

    		</div>
    		<div class="col-lg-6">

				<div class="card card-primary card-outline">
					<div class="card-header">
						<h5 class="m-0">Navigation dans le backoffice</h5>
					</div>
					<div class="card-body">
						<table class="table table-condensed table-striped table-bordered table-hover nowrap dataTable">
							<thead>
								<tr>
									<th>#</th>
									<th>Action</th>
									<th>Date</th>
								</tr>
							</thead>
							<tbody>
								<?php
								$t = [];
								foreach( $user['histo']['navigation'] as $k=>$e ) {
									$location = substr($e['location'],1);
									$lien = URL.$location;
									$t[] = '<tr><td>'.$k.'</td><td><a href="'.$lien.'">'.$location.'</a></td><td>'.core::dateOutput($e['date_creation'], true).'</td></tr>';
								}
								echo implode($t);
								?>
							</tbody>
						</table>
					</div>
				</div>

    		</div>
		</div>


    </div>
    <div role="tabpanel" class="tab-pane" id="apk">

      <?php if(!empty($user['histo']['application']) ) { ?>

      <div class="callout callout-success">
        <h4><?php echo core::dateOutput($user['app_db_update'], true);?></h4>
        <p>Date de la dernière synchronisation de l'application de <?php echo $user['displayname'];?> avec le serveur Gescom.</p>
      </div>




      <div class="row">
    		<div class="col-lg-12">

				<div class="card card-primary card-outline">
					<div class="card-header">
						<h5 class="m-0">Dernières authentifications sur l'application ABC (informations appareil)</h5>
					</div>
					<div class="card-body">
						<table class="table table-condensed table-striped table-bordered table-hover nowrap dataTable">
							<thead>
								<tr>
									<th>Date & heure de connexion</th>
									<th>Type d'appareil</th>
									<!--<th>Marque</th>
                  <th>Version Android</th>
                  <th>Modèle</th>
                  <th>Langue & Région</th>
                  <th>UID Appareil</th>-->
								</tr>
							</thead>
							<tbody>
								<?php
								$t = [];
								foreach( $user['histo']['application'] as $k=>$e )
									$t[] = '<tr>
                    <td>'.core::dateOutput($e['date_creation'], true).', il y à : '.core::dateFrom($e['date_creation'], true).'</td>
                    <td>'.$e['_deviceType'].'</td></tr>';/*
                    <td>'.$e['_manufacturer'].'</td>
                    <td>'.$e['_osVersion'].'</td>
                    <td>'.$e['_model'].'</td>
                    <td>'.$e['_language'].'/'.$e['_region'].'</td>
                    <td>'.$e['_uuid'].'</td>
                  </tr>';*/
								echo implode($t);
								?>
							</tbody>
						</table>
					</div>
				</div>

    		</div>
      </div>

      <?php } ?>
    </div>
  </div>

</div>
