<?php
    global $pageLink;
    $alert = ( core::isOkMountPoints() ? '' : '<i class="fas fa-exclamation-triangle red alert-icon"></i>' );

    $msgs = mailbox::getNotification();
    $notifMsg = "";
    if( !empty($msgs) ) $notifMsg = '<span class="notif nbmsg">'.count($msgs).'</span>';
?>

<div id="navigation">
  <a href="<?php echo URL;?>" title="Accueil" id="logoAccueil">
    <img src="<?Php echo URL;?>img/logo.png" />
  </a>

  <div id="loginBox">
    <small><?php echo l("menu-info-connected");?> :</small><br/>
    <span><?php echo $_SESSION['user']['displayname'];?></span>
    <a id="disconnect" href="#"><i class="fas fa-power-off"></i></a>
    <div class="profile">
      <i class="fas fa-key"></i>
      <span class="badgeProfile"><?php echo securite::printProfileName();?></span>
    </div>
  </div>
  


  <button class="btn rd" id="btnMenuMobile"><i class="fas fa-bars"></i></button>

  <div id="nav-menu">

  <div class="row nav-buttons">
    <?php if( securite::can(18) ) { ?>
    <div class="col">
      <a href="<?php echo URL;?>Stats" class="link" rel="tooltip" title="<?php echo l("menu-info-tooltip-stats");?>">
        <i class="far fa-chart-bar"></i>
      </a>
    </div>
    <?php } ?>
    <?php if( securite::can(19) ) { ?>
    <div class="col">
      <a href="<?php echo URL;?>Messagerie" class="link" rel="tooltip" title="<?php echo l("menu-info-tooltip-messagerie");?>">
        <?php echo $notifMsg;?>
        <i class="fas fa-comments"></i>
      </a>
    </div>
    <?php } ?>
    <?php if( securite::can(8) ) { ?>
    <div class="col">
      <a href="<?php echo URL;?>Admin" class="link" rel="tooltip" title="<?php echo l("menu-info-tooltip-admin");?>">
        <i class="fas fa-user-shield"></i>
      </a>
    </div>   
    <?php } ?>     
  </div>

    <?php
    $menu = [
      '<i class="fas fa-sitemap"></i> '.l("menu-titre-referentiels") => [
        [ "title" => l("menu-titre-produits"), "url" => "Produits", "id_droit" => 1 ],
        [ "title" => l("menu-titre-clients"), "url" => "Magasins", "id_droit" => 2 ],
        [ "title" => l("menu-titre-utilisateurs"), "url" => "Refentiel_Utilisateurs", "id_droit" => 3 ],
        [ "title" => l("menu-titre-produits-pem"), "url" => "Produits-PEM", "id_droit" => 26 ],
        [ "title" => l("menu-titre-strats-pem"), "url" => "Strats-PEM", "id_droit" => 26 ],
      ],
      '<i class="fas fa-user"></i> '.l("menu-titre-promoteurs") => [
        [ "title" => l("menu-titre-commandes"), "url" => "Commandes", "id_droit" => 4 ],
        [ "title" => l("menu-titre-commandes-juva"), "url" => "Commandesjuva", "id_droit" => 27 ],
        [ "title" => "Rappels produits", "url" => "Retours", "id_droit" => 28 ],
        [ "title" => l("menu-titre-visites"), "url" => "Visites", "id_droit" => 5 ],
        [ "title" => l("menu-titre-visites-juva"), "url" => "Visitesjuva", "id_droit" => 5 ],
        // [ "title" => l("menu-titre-planning"), "url" => "Planning", "id_droit" => 17 ],
        [ "title" => "Vue Planning", "url" => "Planning_View", "id_droit" => 17 ],
        [ "title" => l("menu-titre-planning-validation"), "url" => "ValidationPlanning", "id_droit" => 25 ],
        [ "title" => "Gestion du planning", "url" => "Gestion_Secteur", "id_droit" => 25 ],
      ],
      '<i class="fas fa-user-tie"></i> '.l("menu-titre-cs") => [
        [ "title" => l("menu-titre-prospects-encours"), "url" => "Prospects", "id_droit" => 20 ],
        [ "title" => l("menu-titre-visite-prospection"), "url" => "Prospections", "id_droit" => 20 ],
        [ "title" => l("menu-titre-tache-prospection"), "url" => "PlanningProspection", "id_droit" => 23 ],
        [ "title" => l("menu-titre-tache-commercial"), "url" => "PlanningProspection/Clients", "id_droit" => 24 ],
        [ "title" => l("menu-titre-gestion-ca"), "url" => "ProspectionGestionCA", "id_droit" => 8 ],
        [ "title" => l("menu-titre-visite-commerciales"), "url" => "VisitesCS", "id_droit" => 22 ],
      ],
      '<i class="fas fa-satellite"></i> '.l("menu-titre-autre") => [
        [ "title" => l("menu-titre-op-promo"), "url" => "Promos", "id_droit" => 6 ],
        [ "title" => l("menu-titre-news-abc"), "url" => "News", "id_droit" => 7 ],
        [ "title" => l("menu-titre-jours-off"), "url" => "JoursOFF", "id_droit" => 8 ],
        [ "title" => l("menu-titre-traductions"), "url" => "Traductions", "id_droit" => 8 ],
      ]
    ];
    $tmp = [];
    foreach( $menu as $title=>$sub ) {
      $frag = [];
      foreach( $sub as $k=>$e ) {
        if( $e['id_droit'] > 0 && !securite::can($e['id_droit']) ) continue;
        $s = ( strtolower($e['url']) == $pageLink ? 'selected':'' );
        $frag[] = '<a class="nav-menu-link '.$s.'" href="'.URL.$e['url'].'">'.$e['title'].'</a>';
      }
      if( !empty($frag) ) {
        $tmp[] = '<div class="menu"><span class="label">'.$title.'</span>';
        $tmp[] = implode($frag);
        $tmp[] = '</div>';
      }
    }
    echo implode($tmp);
    ?>

    <div id="lang-switcher">
      <?php
        $lang = lang::getLang();
        foreach( lang::getLangues() as $l ) {
          echo '<img 
            src="'.URL_APP_ROOT.'img/lang/'.$l['code'].'.png" 
            alt="'.$l['libelle'].'" 
            data-code="'.$l['code'].'" 
            class="'.($l['code'] == $lang ? 'active' : '').'"
          >';
        }
      ?>
    </div>

  </div>




</div>
