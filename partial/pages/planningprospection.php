<?php 
global $params;
$client = in_array("Clients", (array)$params);

if( $client && !securite::can(24) ) return core::restricted();
if( !$client && !securite::can(23) ) return core::restricted();

echo '<script>let showTacheClient = '.($client?'true':'false').';</script>'
?>

  <h1>
    <?php echo l('page-planning-cs-titre');?>
    <?php echo $client ? l('page-planning-cs-titre-commercial') : l('page-planning-cs-titre-prospection');?>
  </h1>




    <div class="card card-primary card-outline">
      <div class="card-header">
        <h5 class="m-0"><?php echo l('page-planning-cs-rechercher');?></h5>
      </div>
      <div class="card-body">
        <div class="form-group">
          <label><?php echo l('page-planning-cs-prospecteur');?></label>
          <select class="form-control getPlanning" id="planning_sel_id_repr">
          <option value="0" data-id="0"></option>
            <?php
            foreach( user::getAllCS() as $id => $o ) {
                echo '<option value="'.$o['id'].'" data-id="'.$o['id_repr'].'">#'.$o['id_repr'].' : '.$o['displayname'].'</option>';
            }
            ?>
          </select>
        </div>

        <div class="input-group mb-3">
          <div class="input-group-prepend">
            <span class="input-group-text" id="basic-addon1"><?php echo l('page-planning-cs-dates');?></span>
          </div>
          <input type="date" class="form-control getPlanning" name="from" autocomplete="off" value="<?php echo date("Y-m-d");?>" placeholder="<?php echo l('date-du');?>">
          <input type="date" class="form-control getPlanning" name="to" autocomplete="off" placeholder="<?php echo l('date-au');?>">
        </div>
      </div>
    </div>
  <div id="pl-wrapper"></div>


<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js" integrity="sha512-uto9mlQzrs59VwILcLiRYeLKPPbS/bT71da/OEBYEwcdNUk8jYIy+D176RYoop1Da+f9mvkYrmj5MCLZWEtQuA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css" integrity="sha512-aOG0c6nPNzGk+5zjwyJaoRUgCdOrfSDhmMID2u4+OIslr0GjpLKo7Xm0Ao3xmpM4T8AmIouRkqwj1nrdVsLKEQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
