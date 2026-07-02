<?php if( !securite::can(5) ) return core::restricted();?>
<?php
global $params;
if( isset($params[1]) ) {
  $id = intval($params[1]);
  $visite = visite::get($id);
  if( $id == $params[1] && $visite ) {
    include(PAGES."visite/fiche.php");
    return;
  }
}
?>
<div class="row">
  <div class="col">
    <div class="small-box bg-success">
      <div class="inner">
        <h3><?php echo visite::countTotal();?></h3>
        <p>visites au total</p>
      </div>
      <div class="icon">
        <i class="fas fa-walking"></i>
      </div>
    </div>
  </div>

  <div class="col">
    <div class="small-box bg-success">
      <div class="inner">
        <h3><?php echo visite::countTotalPhotos();?></h3>
        <p>photos de visites</p>
      </div>
      <div class="icon">
        <i class="fas fa-camera-retro"></i>
      </div>
    </div>
  </div>

  <div class="col">
    <div class="small-box bg-success">
      <div class="inner">
        <h3><?php echo visite::countTotal(true);?></h3>
        <p>visites ce jour</p>
      </div>
      <div class="icon">
        <i class="fas fa-walking"></i>
      </div>
    </div>
  </div>

  <div class="col">
    <div class="small-box bg-success">
      <div class="inner">
        <h3><?php echo visite::countTotalPhotos(true);?></h3>
        <p>photos ce jour</p>
      </div>
      <div class="icon">
        <i class="fas fa-camera-retro"></i>
      </div>
    </div>
  </div>

</div>


<div class="card card-primary card-outline">
  <div class="card-header">
    <h5 class="m-0">Les Visites</h5>
  </div>
  <div class="card-body">
      <table class="table table-striped" id="vTable">
        <thead>
          <th>ID</th>
          <th>Client</th>
          <th>Commercial</th>
          <th>Commande liée</th>
          <th>Date</th>
          <th>Photos</th>
        </thead>
          <?php
          $visites = visite::getBoard();
          /*$tmp = [];
          foreach( $visites as $k=>$e ) {
            $tmp[] = '
            <tr>
              <td>'.$e['id'].'</td>
              <td>'.$e['raison_sociale'].'</td>
              <td>'.core::dateOutput($e['queue_date']).'</td>
              <td>'.$e['user'].'</td>
              <td><i class="far fa-images"></i> '.$e['nb_photo'].'</td>
            </tr>
            ';
          }
          echo implode($tmp);*/
          ?>
      </table>
  </div>
</div>
<?php #echo json_encode($visites);?>
<script>
  $(document).ready(function() {

    $(document).on('click.vTable', '#vTable tbody tr', function () {
        var id = $(this).find('td:first').text();
        loader(true)
        $(location).attr('href', _global.app_url+'Visites/'+id)
    });


    let vTable = $("#vTable").DataTable({
      pagination: "bootstrap",
      filter:true,
      order : [[0, 'desc']],
      data : <?php echo json_encode($visites);?>,
      destroy: true,
      lengthMenu:[5,10,25],
      pageLength: 10,
      columns :[
         {     "data"     :     "id"     },
         {     "data"     :     "client"},
         {     "data"     :     "commercial"},
         {     "data"     :     "commande"},
         {     "data"     :     "date"},
         {     "data"     :     "photos"},
      ]
    });
  })
</script>
