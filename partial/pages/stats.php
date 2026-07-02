<?php
if( !securite::can(18) ) return core::restricted();

if( true ) {
  $statsFiles = [
    1 => l("stat-type-1")."",
    2 => l("stat-type-2")."",
    3 => l("stat-type-3")."",
    4 => l("stat-type-4")."",
    5 => l("stat-type-5")."",
    6 => l("stat-type-6")."",
    7 => l("stat-type-7")."",
    8 => l("stat-type-8")."",
    //10 => "DN présente",
    9 => l("stat-type-9")."",
    12 => l("stat-type-10")."",
    13 => "Alertes Commerciales",
  ];
} 
if( securite::can(21) ) $statsFiles[11] = l("stat-type-11")."";


global $params;
if( isset($params[1]) )
  $id_stat = intval($params[1]);
if( !isset($id_stat) || !isset($statsFiles[$id_stat]) )
  $id_stat = 1;

$from = date("Y-m-d");
$to = date("Y-m-d");

// Définir $from et $to en fonction de l'ID de la statistique
if ($id_stat === 11) { // Remplacez '5' par l'ID de la statistique concernée
  $from = date("Y-m-d", strtotime("-1 day")); // Date d'hier
  $to = date("Y-m-d", strtotime("-1 day"));;
} else {
  $from = date("Y-m-d");
  $to = date("Y-m-d");
}


?>
<div class="row" id="page-stats">
  <div class="col">
    <div class="card card-primary card-outline">
      <div class="card-header">
        <h5 class="m-0">
          <?php echo l("stat-titre");?>
          <div class="float-right">
            <select class="form-control form-control-sm" id="changeStats">
              <option value=""></option>
              <?php
              foreach( $statsFiles as $k=>$e ) {
                $s = ( $id_stat == $k ? 'selected' : '' );
                echo '<option value="'.$k.'" '.$s.'>'.$e.'</option>';
              }
              ?>
            </select>
          </div>
        </h5>
      </div>
      <div class="card-body">
        <h5><span><?php echo $statsFiles[$id_stat];?></span></h5>
        <?php
        include(PAGES."stats/stats_".$id_stat.".php");
        ?>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js" integrity="sha256-ErZ09KkZnzjpqcane4SCyyHsKAXMvID9/xwbl/Aq1pc=" crossorigin="anonymous"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/excellentexport@3.4.3/dist/excellentexport.min.js"></script>
