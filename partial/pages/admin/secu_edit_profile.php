<div class="card card-primary card-outline" id="editProfile">
  <div class="card-header">
    <h5 class="m-0"><?php echo l('admin-edit-profile-titre');?></h5>
  </div>
  <div class="card-body m-250">

    <div class="hidden wrapperSelectProfile">
      <h5><span class="profileName"></span></h5>
      <div class="row">
        <div class="col">
          <div class="form-group">
            <label><?php echo l('admin-edit-profile-name');?></label>
            <input type="text" class="form-control" name="libelle_edit_profil" value=""/>
            <small class="form-text text-muted"><?php echo l('admin-edit-profile-name-warn');?></small>
          </div>
        </div>
        <div class="col">
          <div class="form-group">
            <label><?php echo l('admin-edit-profile-default');?></label>
            <select class="form-control" name="defaut">
              <option value="0"><?php echo l('non');?></option>
              <option value="1"><?php echo l('oui');?></option>
            </select>
            <small class="form-text text-muted">
              <?php echo l('admin-edit-profile-info');?>
            </small>
          </div>
        </div>
        <div class="col">
          <div class="form-group">
            <label><?php echo l('admin-edit-profile-acceuil');?></label>
            <input type="text" class="form-control" name="homepage_edit_profil" value=""/>
            <small class="form-text text-muted">
            <?php echo l('admin-edit-profile-acceuil-exemple');?>

            </small>
          </div>
        </div>
      </div>
      <br/>
      <div class="row">
        <div class="col">
          <button class="btn btn-danger btn-sm" onClick="deleteProfil()"><?php echo l('admin-edit-profile-delete');?></button>
        </div>
        <div class="col text-right">
          <button class="btn btn-primary btn-sm" onClick="editProfil()"><?php echo l('admin-edit-profile-edit');?></button>
        </div>
      </div>

    </div>

    <div class="tc text-secondary mt-3 disclaimerEditProfile">
      <?php echo l('admin-edit-profile-disclaimer');?>
      
    </div>

  </div>
</div>
