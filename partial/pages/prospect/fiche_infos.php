<div class="row">
    <div class="col-8">
        <div class="p-3 mb-2 bg-info text-white">
            <?php echo  l('page-prospect-info-disclaimer');?>  
        </div>
        <form method="post" action="#" class="form-save" data-table="prospect" data-id="<?php echo $id;?>">
            <?php
            echo core::colSplit([
                core::printInput( l('page-prospect-info-type') . '','ptype',$prospect['ptype'],false),
                core::printInput( l('page-prospect-info-ancien') . '','ancien',$prospect['ancien']?'Oui':'Non',false),
                core::printInput( l('page-prospect-info-enseigne') . '','enseigne',$prospect['enseigne']),
                core::printInput( l('page-prospect-info-nom') . '','nom',$prospect['nom']),
                core::printInput( l('page-prospect-info-adresse') . '','adresse',$prospect['adresse']),
                core::printInput( l('page-prospect-info-cp') . '','cp',$prospect['cp']),
                core::printInput( l('page-prospect-info-ville') . '','ville',$prospect['ville']),
                core::printInput( l('page-prospect-info-telephone') . '','telephone',$prospect['telephone']),
                core::printInput( l('page-prospect-info-email') . '','email',$prospect['email']),
                core::printInput( l('page-prospect-info-categoie') . '','categorie',$prospect['categorie']),
            ],2);
            ?>
        </form>
        <h3 class="text-center text-primary" style="margin-top:15px"> <?php echo l('page-prospect-info-cree-par').' '.user::getNameFromId($prospect['id_user']);?></h3>
    </div>
    <div class="col-4">

        <?php
        $id_prospect = $prospect['id'];
        $prospection = prospection::getFromIdProspect( $id_prospect );
        if( !$prospection ) {
            echo '<div class="p-3 mb-2 bg-danger text-white">'.l('page-prospect-info-no-start').'</div>';
        }
        else {
            echo "<h5><span>".l('page-prospect-info-avancement')."</span></h5>";
            $st = [];
            foreach( $prospection['steps'] as $step ) {
              $st[] = '
                <div class="card clat">
                  <div class="card-body">
                    <div class="numstep">'.$step['step'].'°</div>
                    <strong>'.prospection::$steps[$step['name']].'</strong><br/>
                    <i class="far fa-clock"></i> '.core::dateOutput($step['value'],true).'
                  </div>
                </div>
              ';
            }
            echo implode($st);            
        }
        ?>


        
        <h5><span><?php echo l('page-prospect-info-ouvertures');?></span></h5>
        <table class="table">
        <?php
            $days = [l('date-lundi'),l('date-mardi'),l('date-mercredi'),l('date-jeudi'),l('date-vendredi')];
            $ok = '<i class="fas fa-check" style="color:green"></i>';
            $ko = '<i class="fas fa-times" style="color:red"></i>';
            $j = array_pop($prospect['jours']);
            foreach( $days as $day ) {
                echo '<tr>';
                echo '<th>'.$day.'</th>';
                echo '<td>'.($j[$day]?$ok:$ko).'</td>';
                echo '</tr>';
            }
        ?>
        </table>
        <h5><span><?php echo l('page-prospect-info-ouvertures-horaires');?></span></h5>
        <?php $h = array_pop($prospect['horaires']);?>
        <div class="row text-center">
            <div class="col">
                <?php echo l('page-prospect-info-matin');?><br/>
                <?php echo $h['am_start']." ➡️ ".$h['am_end'];?>
            </div>
            <div class="col">
            <?php echo l('page-prospect-info-aprem');?><br/>
                <?php echo $h['pm_start']." ➡️ ".$h['pm_end'];?>                
            </div>            
        </div>
    </div>
</div>



<script>
    $(document).ready(function() {
        $("input").each(function() {
            $(this).css({
                border : '1px solid #aaa',
                background : '#ddd',
                cursor: 'not-allowed'
            }).attr('disabled','disabled')
        })
    })
</script>




