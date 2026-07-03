<script>
  $(document).ready(function() {

    $(".main-header form, .main-header ul:last").remove();
    $(".main-header").append('<h4 style="line-height:40px;color:#555; margin:0;">Carrefour St Charles <i class="fas fa-info-circle text-primary" style="margin-left:8px;cursor: pointer;"></i></h4>');

    $("#mobileMenu button").click(function() {
        let i = $(this);
        if( i.hasClass('btn-primary') ) return;
        $("#mobileMenu").find('.btn-primary').removeClass('btn-primary').addClass('btn-secondary');
        i.addClass('btn-primary').removeClass('btn-secondary');
        let name = i.attr('name');
        $("#mobileContent .activeTab").removeClass('activeTab');
        $("#mobileContent #"+name).addClass('activeTab');
    });
  });
</script>
<div class="content-wrapper" id="mobile">


<div class="btn-group" role="group" id="mobileMenu">
  <button type="button" name="fiche" class="btn btn-primary">Fiche</button>
  <button type="button" name="photos" class="btn btn-secondary">Photos</button>
  <button type="button" name="commande" class="btn btn-secondary">Commande</button>
</div>

<div id="mobileContent">
  <div class="mobileContentTab activeTab" id="fiche">
    <?php include("mobile/fiche.php");?>
  </div>
  <div class="mobileContentTab" id="photos">
    <?php include("mobile/photos.php");?>
  </div>
  <div class="mobileContentTab" id="commande">
    <?php include("mobile/commande.php");?>
  </div>    
</div>















</div>



