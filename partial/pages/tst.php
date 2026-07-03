<?php
$datas = importAS400::getDatas(" LIMIT  5 ");
$headers = importAS400::getHeaders();

/*
	<table class="table table-sm table-striped table-bordered table-hover" id="as400Table">
		<thead>
			<tr>
			<?php
			foreach( $headers as $k=>$e ) {
				if( $e == "vide" ) continue;
				echo '<th>'.( $e == "" ? $k : $e.'<i>('.$k.')</i>' ).'</th>';
			}
			?>
			</tr>
		</thead>
		<tbody>
			<?php
			foreach( $datas as $d ) {
				echo '<tr>';
				foreach( $headers as $k=>$e ) {
					if( $e == "vide" ) continue;
					echo '<td>'.$d[$k].'</td>';
				}
				echo '<tr>';
			}
			?>
		</tbody>
	</table>
*/
?>
<table class="table table-sm table-striped table-bordered table-hover" id="as400Table">
	<thead>
		<tr>
			<th>Libellé</th>
			<th>Nom réel du champ</th>
			<?php
			for( $i = 0; $i<count($datas); $i++ ) {
				echo '<th>Jeu de données '.($i+1).'</th>';
			}
			?>
		</tr>
	</thead>
	<tbody>
		<?php
			foreach( $headers as $k=>$e ) {
				if( $e == "vide" ) continue;
				echo '<tr>';
					echo '<th>'.( $e == "" ? $k : $e ).'</th>';
					echo '<th><i>'.$k.'</i></th>';
					foreach( $datas as $d ) {
						echo '<td>'.$d[$k].'</td>';
					}
				echo '</tr>';
			}
		?>
	</tbody>
</table>
