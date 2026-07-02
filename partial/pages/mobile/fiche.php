<form class="container">

	<br/>

	<div class="card card-primary card-outline">
		<div class="card-header">
			<h5 class="m-0">Magasin</h5>
		</div>
		<div class="card-body">   

			<div class="form-group">
				<label>Nom du magasin</label>
				<input type="text" class="form-control" value="Carrefour St Charles" />
			</div>

			<div class="form-group">
				<label>Adresse</label>
				<textarea class="form-control"><?php echo "15 rue du général Leclerc\r\n75019 St Charles";?></textarea>
				<small class="form-text text-muted">A maintenir à jour pour les GPS</small>
			</div>		
			<div class="form-group">
				<label>Horaires d'ouverture</label>
				<div class="input-group mb-3">
					<div class="input-group-prepend">
						<label class="input-group-text">Ouverture</label>
					</div>
					<select class="custom-select">
						<?php
						for( $i = 6; $i <= 23; $i++ ) {
							echo '<option value="'.$i.'.00">'.$i.'h00</option>';
							echo '<option value="'.$i.'.15">'.$i.'h15</option>';
							echo '<option value="'.$i.'.30" '.($i==8 ? 'selected':'').'>'.$i.'h30</option>';
							echo '<option value="'.$i.'.45">'.$i.'h45</option>';
						}
						?>
					</select>
				</div>	
				<div class="input-group mb-3">
					<div class="input-group-prepend">
						<label class="input-group-text">Fermeture</label>
					</div>
					<select class="custom-select">
						<?php
						for( $i = 6; $i <= 23; $i++ ) {
							echo '<option value="'.$i.'.00">'.$i.'h00</option>';
							echo '<option value="'.$i.'.15">'.$i.'h15</option>';
							echo '<option value="'.$i.'.30" '.($i==20 ? 'selected':'').'>'.$i.'h30</option>';
							echo '<option value="'.$i.'.45">'.$i.'h45</option>';
						}
						?>
					</select>
				</div>											
			</div>	
			<div class="form-group">
				<label>Jours d'ouverture</label>
				<div class="btn-group" role="group" aria-label="Basic example">
					<button type="button" class="btn btn-primary">Lu</button>
					<button type="button" class="btn btn-primary">Ma</button>
					<button type="button" class="btn btn-primary">Me</button>
					<button type="button" class="btn btn-primary">Je</button>
					<button type="button" class="btn btn-primary">Ve</button>
					<button type="button" class="btn btn-primary">Sa</button>
					<button type="button" class="btn btn-secondary">Di</button>
				</div>
			</div>	

			<div class="form-group">
				<label>Spécificités d'ouvertures</label>
				<textarea class="form-control">Fermé le mercredi après-midi</textarea>
				<small class="form-text text-muted">Merci de préciser les periodes de fermeture</small>
			</div>									

		</div>
	</div>


	<div class="card card-success card-outline">
		<div class="card-header">
			<h5 class="m-0">Responsables/Contacts magasin</h5>
		</div>
		<div class="card-body">   

			<div class="input-group mb-3">
				<div class="input-group-prepend">
					<span class="input-group-text" id="basic-addon1"><i class="fas fa-user-tie"></i></span>
				</div>
				<input type="text" class="form-control" value="Pierre Dupont">
				<input type="text" class="form-control" value="0602030599">
				<div class="input-group-append">
					<button class="btn btn-danger" type="button"><i class="far fa-trash-alt"></i></button>
				</div>				
			</div>
			<div class="input-group mb-3">
				<div class="input-group-prepend">
					<span class="input-group-text" id="basic-addon1"><i class="fas fa-user-tie"></i></span>
				</div>
				<input type="text" class="form-control" value="Mr Jean">
				<input type="text" class="form-control" value="0953687889">
				<div class="input-group-append">
					<button class="btn btn-danger" type="button"><i class="far fa-trash-alt"></i></button>
				</div>					
			</div>			

		</div>
	</div>

</form>