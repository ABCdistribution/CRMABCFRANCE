
<div class="card card-primary card-outline">
  <div class="card-header">
    <h5 class="m-0"><?php echo l('page-traduction-titre');?></h5>
  </div>
  <div class="card-body">

    <form method="POST" action="#" id="formAddTrad">
      <div class="row">
        <div class="col">
          <div class="form-group">
            <label><?php echo l('page-traduction-code');?></label>
            <input type="text" class="form-control" name="code">
          </div>
        </div>
        <?php
        foreach( lang::getLangues() as $l ) {
          echo '
          <div class="col">
            <div class="form-group">
              <label>'.$l['libelle'].'</label>
              <input type="text" class="form-control" name="'.$l['code'].'">
            </div>
          </div>
          ';
        }
        ?>
      </div>
      <div class="text-right">
        <div class="btn btn-primary" onclick="createTrad()"><?php echo l('page-traduction-create');?></div>
      </div>
    </form>

    <hr/>

    <table class="table table-traduction" id="tradTable">
        <thead>
            <tr>
                <th><em><?php echo l('page-traduction-code');?></em></th>
                <?php
                    $langues = lang::getLangues();
                    foreach( $langues as $l ) {
                        echo '<th data-col="'.$l['code'].'">'.$l['libelle'].'</th>';
                    }
                ?>
            </tr>
        </thead>
        <tbody></tbody>
    </table>

  </div>
</div>