<?php
global $db;


// if (empty($commande['id_as400'])) {
//   echo '<div class="alert alert-danger text-center" style="margin: 20px auto; max-width: 600px;">
//       <strong>Erreur :</strong> Aucun code client JUVA (id_as400) trouvé pour cette commande.
//   </div>';
//   return;
// }
$client = client::getByCode($commande['id_as400']); // client = code juva


$user = user::getUserFromLogin($commande['utilisateur']);
$db->execute("SELECT * FROM juva_commandeligne WHERE commande = '".$db->escape($commande['code'])."'");
$produits = $db->getArray();


// echo '<div style="
//     background-color:rgba(230, 46, 0, 0.53);
//     color: #red;
//     border: 1px solid #ffeeba;
//     padding: 15px;
//     border-radius: 8px;
//     font-family: Arial, sans-serif;
//     font-size: 16px;
//     max-width: 600px;
//     margin: 20px auto;
//     text-align: center;
// ">
//     🚧 DEV page fichejuva.php.
// </div>';
// ?>

<div class="card card-primary card-outline">
  <div class="card-header">
    <h5 class="m-0">
      <i class="fas fa-file-alt"></i> Commande JUVA #<?php echo $commande['id']; ?> — <?php echo $commande['id_as400']; ?>
      <a class="btn btn-default btn-sm" href="<?php echo URL; ?>CommandesJuva">
        <i class="fas fa-long-arrow-alt-left"></i> <?php echo l('bouton-retour'); ?>
      </a>
    </h5>
  </div>
  <div class="card-body">
    <div class="row">
      <div class="col">
        <h5>Détails</h5>
        <table class="table table-striped">
          <tbody>
            <tr><th>Code interne :</th><td>#<?php echo $commande['id']; ?></td></tr>
            <tr><th>Code JUVA :</th><td><?php echo $commande['code']; ?></td></tr>
            <tr><th>Client :</th><td><?php echo $client['enseigne'] ?? $commande['client']; ?></td></tr>
            <tr><th>Créé par :</th><td><?php echo $user['displayname'] ?? $commande['utilisateur']; ?></td></tr>
            <tr><th>Date de réalisation :</th><td><?php echo core::dateOutput($commande['daterealisation']); ?></td></tr>
            <tr><th>Date de livraison :</th><td><?php echo core::dateOutput($commande['datelivraison']); ?></td></tr>
            <tr><th>Référence :</th><td><?php echo $commande['reference']; ?></td></tr>
            <tr><th>Origine :</th><td><?php echo $commande['origine']; ?></td></tr>
            <tr><th>Statut :</th><td><?php echo $commande['statut']; ?></td></tr>
            <tr><th>Commentaire :</th><td><?php echo nl2br($commande['commentaire']); ?></td></tr>
            <?php if ($commande['externe'] == 1): ?>
              <tr><th>Commande externe :</th><td><?php echo $commande['externeMail']; ?></td></tr>
              <tr><th>PDF de la commande :</th>
            <td><a target="_blank" href="<?php echo URL_APP_ROOT.'CmdPDFJuva/'.$commande['id']; ?>"><?php echo l('bouton-ouvrir'); ?></a></td>
          </tr>
            <?php endif; ?>


          </tbody>
        </table>

          <h5><span><?php echo l('page-cmd-total-cmd');?></span></h5>
              <?php 
              // Calcul du total si non défini
              if (empty($commande['total'])) {
                  $total = 0;
                  foreach ($produits as $ligne) {
                      $prod = produit::getByCodeJuva($ligne['produit']);
                      $total += $ligne['quantite'] * $prod['prix'] * $prod['pcb'];
                  }
              } else {
                  $total = $commande['total'];
              }

              ?>

        <p class="text-center" style="font-size:60px;color:#264d3a">
           <?php echo core::n($total); ?>€
        </p>
      </div>

      <div class="col">
        <h5>Produits (<?php echo count($produits); ?>)</h5>
        <div class="list-group" style="font-size:14px;">
          <?php foreach ($produits as $ligne): 
            $prod = produit::getByCodeJuva($ligne['produit']);
          ?>
            <div class="list-group-item list-group-item-action">
              <span><?php echo $ligne['quantite']; ?> x <?php echo $prod['libelle']; ?> <em>(#<?php echo $prod['idoriginal']; ?>)</em></span>
              <small class="float-right"><?php echo $ligne['quantite']; ?> x <?php echo core::n($prod['prix']); ?>€ x <?php echo $prod['pcb']; ?> = <?php echo core::n($ligne['quantite'] * $prod['prix'] * $prod['pcb']); ?>€</small>
            </div>
          <?php endforeach; ?>
        </div>


      </div>
    </div>
  </div>
</div>
