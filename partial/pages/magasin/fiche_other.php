<div class="row">
    <div class="col-6">
        <h5><span><?php echo l('page-client-dernieres-visites');?></span></h5>
        <div id="lastVisits" data-id="<?php echo $magasin['id_as400'];?>">
            <p class="text-center">
                <i class="fas fa-spinner fa-spin"></i>
            </p>
        </div>
    </div>
    <div class="col-6">
        <h5><span><?php echo l('page-client-dernieres-cmd');?></span></h5>
        <div id="cmdMinos" data-id="<?php echo $magasin['id_as400'];?>">
            <p class="text-center">
                <i class="fas fa-spinner fa-spin"></i>
            </p>
        </div>
    </div>
</div>
