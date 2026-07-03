<div class="card card-primary card-outline">
    <div class="card-header">
    <h5 class="m-0"><?php echo l('menu-titre-jours-off');?></h5>
    </div>
    <div class="card-body">

        <p>
            Veuillez préciser tous les jours fériés ou non travaillés chez ABC pour chaque année. 
        </p>

        <form class="form-inline">
            <div class="form-group">
                <label>Choisir une année :</label>
                <input type="number" class="form-control ml-3 text-center" id="yearOffSelected" value="<?php echo date('Y');?>">
            </div>
        </form>

        <div id="dayz"></div>


    </div>
</div>