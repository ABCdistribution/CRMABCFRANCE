<aside class="main-sidebar sidebar-dark-primary elevation-4">
  <a href="index3.html" class="brand-link text-center">
    <span class="brand-text font-weight-light">ABC Distribution</span>
  </a>

  <!-- Sidebar -->
  <div class="sidebar">
    <!-- Sidebar user panel (optional) -->
    <div class="user-panel mt-3 pb-3 mb-3 d-flex" id="disconnect">
      <div class="image">
        <img src="<?php echo URL;?>dist/img/user_default.jpg" class="img-circle elevation-2" alt="User Image">
      </div>
      <div class="info">
        <a href="#" class="d-block" ><?php echo $_SESSION['user']['displayname'];?></a>
      </div>
    </div>

    <!-- Sidebar Menu -->
    <nav class="mt-2">
      <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
        <!-- Add icons to the links using the .nav-icon class
             with font-awesome or any other icon font library -->
        <li class="nav-item has-treeview menu-open">
          <a href="#" class="nav-link">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <p>
              Accueil
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="<?php echo URL;?>" class="nav-link">
                <i class="fas fa-circle nav-icon text-primary"></i>
                <p>Mon tableau de bord</p>
              </a>
            </li>
          </ul>

      <li class="nav-item has-treeview menu-open">
          <a href="#" class="nav-link">
            <i class="fas fa-tags"></i>
            <p>
              Produits ABC
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="<?php echo URL;?>Produits" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Gérer les produits</p>
              </a>
            </li>
          </ul>
        </li>

        <li class="nav-item has-treeview menu-open">
          <a href="#" class="nav-link">
            <i class="fas fa-store"></i>
            <p>
              Magasins
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="<?php echo URL;?>Magasins" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Gérer les magasins</p>
              </a>
            </li>
          </ul>
        </li>

        <li class="nav-item has-treeview menu-open">
          <a href="#" class="nav-link">
            <i class="fas fa-user"></i>
            <p>
              Utilisateurs
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="<?php echo URL;?>Refentiel_Utilisateurs" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Réferentiel</p>
              </a>
            </li>
          </ul>
        </li>



	  <li class="nav-item has-treeview menu-open">
          <a href="#" class="nav-link">
            <i class="fas fa-history"></i>
            <p>
              Rapports
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="#" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Commandes</p>
              </a>
            </li>
          </ul>
        </li>
      </ul>
    </nav>
    <!-- /.sidebar-menu -->
  </div>
  <!-- /.sidebar -->
</aside>
