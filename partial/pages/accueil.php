<div style="
position:absolute;
top: 0;
left: 0;
width: 100%;
height: 100%;
background: url('./img/bg.jpg') fixed center no-repeat;
background-size : cover;
">

  <p style="
  display: block;
  padding: 0 20px;
  text-align: center;
  margin: 150px auto;
  font-size: 60px;
  text-shadow: 1px 5px 1px #ddd;
  ">
    <?php echo l('welcome-msg-home');?>
    
    <?php 
    if( securite::can(18) ) {
    $nb_alerte = admin::getNbAlerteCom();
    if($nb_alerte > 0){
      ?>
      <br>
      <a href="<?php echo URL;?>Stats/13" class="link">
        <button class="btn btn-light"><i class="fas fa-exclamation-triangle"></i> &nbsp;<?php echo $nb_alerte; ?> Alerte(s) Commerciale(s) ce jour</button>
      </a>
    <?php }} ?>
  </p>


</div>
