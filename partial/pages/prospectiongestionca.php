<div class="row" id="page-prospections">
  <div class="col">
    <div class="card card-primary card-outline">
      <div class="card-header">
        <h5 class="m-0"><?php echo l('page-prospectionca-titre');?></h5>
      </div>
      <div class="card-body">
        <div class="input-group">
          <div class="input-group-prepend">
            <label class="input-group-text" for="inputGroupSelect01"><?php echo l('page-prospectionca-select');?></label>
          </div>
          <select class="custom-select" id="selectCS">
              <option value="0"><?php echo l('page-prospectionca-choisir');?></option>
              <?php
                $o = prospection::getCSOptions();
                foreach( $o as $k=>$e ) echo '<option value="'.$k.'">'.$e.'</option>';
              ?>
          </select>
        </div>
        <div id="resultCA"></div>
      </div>
    </div>
  </div>
  <div class="col-4">
    <div class="card card-primary card-outline">
      <div class="card-header">
        <h5 class="m-0">Importer un fichier</h5>
      </div>
      <div class="card-body" id="importDiv" style="position:relative">
        <p>
          <?php echo l('page-prospectionca-tuto');?>
            
        </p>
        <div class="text-center">
            <button class="btn btn-primary" onclick="uploadCSV()"><?php echo l('bouton-importer');?></button>
            <input type="file" id="fileInput" accept=".csv" style="display:none"/>
        </div>
      </div>
    </div>
  </div>  
</div>