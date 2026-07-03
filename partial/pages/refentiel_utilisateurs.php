<?php if( !securite::can(3) ) return core::restricted();?>
<div class="row">
	<div class="col-lg-12">
		<div class="card card-primary card-outline">
			<div class="card-header">
				<h5 class="m-0"><?php echo l('page-user-title');?></h5>
			</div>
			<div class="card-body">
				<div class="alert alert-info" role="alert">
					<?php echo l('page-user-warning');?>
				</div>
				<div class="table-responsive">
				<table class="table table-condensed table-striped" style="font-size:15px;" id="tableUsers">
					<thead>
						<tr>
							<th><?php echo l('page-user-table-id');?></th>
							<th><?php echo l('page-user-table-mail');?></th>
							<th><?php echo l('page-user-table-poste');?></th>
							<th><?php echo l('page-user-table-profil');?></th>
							<th class="tc"><?php echo l('page-user-table-id-rep');?></th>
							<th class="tc" rel="tooltip" title="<?php echo l('page-user-table-tooltip-crm-apk');?>">
								CRM <i class="fas fa-exchange-alt"></i> APK
							</th>
							<th class="tc" rel="tooltip" title="<?php echo l('page-user-table-tooltip-apk-sync');?>">
								<i class="fas fa-sync-alt"></i> APK
							</th>

							<th class="tc" rel="tooltip" title="<?php echo l('page-user-table-tooltip-apk-call');?>">
								<i class="fas fa-hashtag"></i> APK
							</th>
							<th><?php echo l('page-user-table-actions');?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						$table = [];
						$editProfile = ( securite::isAdmin() ? 'editableProfile' : '' );
						foreach( user::getAll(true) as $k=>$e ) {
							$t = [];
							$t[] = '<tr data-id="'.$e['id'].'" data-id-profile="'.$e['id_profile'].'">';
							$t[] = '<td>'.$e['displayname'].'<br/><i>'.$e['login'].' #'.$e['id'].'</i></td>';
							$t[] = '<td>'.$e['mail'].'</td>';
							$t[] = '<td>'.$e['poste'].'<br/>' .( $e['secteur'] != "" ? '<i>'.$e['secteur'].'</i>':'').'</td>';
							$t[] = '<td><span class="'.$editProfile.'">'.securite::getUserProfileLibelle($e['id']).'</span></td>';
							$t[] = '<td class="tc">'.$e['id_repr'].'</td>';
							$t[] = '<td class="tc">'.core::dateFrom($e['api_query'], true).'</td>';
							$t[] = '<td class="tc">'.core::dateFrom(user::getLastLoginApk($k), true).'</td>';

							$t[] = '<td class="tc">'.($e['apk_version'] != "" ? 'v'.$e['apk_version']: '').'</td>';
							$t[] = '<td class="actions">';
							$t[] = '<a href="'.URL.'Historique_Utilisateur/'.$k.'" class="btn btn-sm btn-primary" rel="tooltip" title="Historiques"><i class="fas fa-history"></i></a>';
							$t[] = '<a href="#" onclick="deleteUser(this,'.$k.')" class="btn btn-sm btn-danger" rel="tooltip" title="Supprimer"><i class="fas fa-user-slash"></i></a>';
							$t[] = '</td>';
							$t[] = '</tr>';
							$table[] = implode($t);
						}
						echo implode($table);
						?>
					</tbody>
				</table>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
	$("#tableUsers").dataTable();
</script>
<?php
	$profiles = securite::getProfils();
	$opt = [];
	foreach( $profiles as $k=>$e ) $opt[$k] = $e['libelle'];
	echo '<script>let profiles = '.json_encode($opt).';</script>';
?>
