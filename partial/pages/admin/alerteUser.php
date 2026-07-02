<div class="card card-primary card-outline">
  <div class="card-header">
    <h5 class="m-0">Liste mails Alertes Commerciales</h5>
  </div>
  <div class="card-body m-250 noverflow rel" id="">
  <div class="input-group mb-3" style="width:300px;margin: 10px auto">
        <select name="select_mail_alertes_com" class="form-control select_mail_alertes_com">
            <?php echo admin::getHtmlSelectMailAlerteCom(); ?>
        </select>
    </div>
  <table class="table table-condensed tc tab-mail-alerte-com">
      <thead>
        <tr>
          <th class="tl">Utilisateur</th>
          <th>Mail</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php echo admin::getHtmlTabMailAlerteCom(); ?>
      </tbody>
    </table>
  </div>
</div>
