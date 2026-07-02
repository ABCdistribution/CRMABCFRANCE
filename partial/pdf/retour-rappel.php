<?php
$id_bordereau = trim((string) ($rappel['id_bordereau'] ?? ''));
?>
<style>
  table.rappel-produits { width:100%; font-size:10px; border-collapse: collapse; }
  table.rappel-produits th, table.rappel-produits td { border: 1px solid #333; padding: 4px; text-align: left; }
  table.rappel-produits th { background: #f0f0f0; }
  table.rappel-produits tr.rappel-ligne-recupere td { background: #d1ecf1; }
  .rappel-info { margin-bottom: 12px; font-size: 11px; }
</style>
<page>
  <h2 style="font-size: 14px;">Détail rappel produit</h2>
  <div class="rappel-info">
    <?php if ( $id_bordereau !== '' ) : ?>
    <p><strong>N° bordereau :</strong> <?php echo htmlspecialchars($id_bordereau); ?></p>
    <?php else : ?>
    <p><strong>Id ligne :</strong> <?php echo (int) ($rappel['id'] ?? ''); ?></p>
    <?php endif; ?>
    <p><strong>Date retour :</strong> <?php echo htmlspecialchars($rappel['date_retour'] ?? '—'); ?></p>
    <p><strong>Promoteur :</strong> <?php echo htmlspecialchars($rappel['promoteur'] ?? '—'); ?></p>
    <p><strong>Magasin :</strong> <?php echo htmlspecialchars($rappel['code_magasin'] ?? '—'); ?> — <?php echo htmlspecialchars($libelle_magasin); ?></p>
  </div>
  <table class="rappel-produits">
    <thead>
      <tr>
        <th>Libellé produit</th>
        <th>N° lot</th>
        <th>Code EAN</th>
        <th>Ref article</th>
        <th>Code Minos</th>
        <th>Qté</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ( $lignes as $L ) : ?>
      <tr<?php echo (($L['action_produit'] ?? '') === 'recupere') ? ' class="rappel-ligne-recupere"' : ''; ?>>
        <td><?php echo htmlspecialchars($getLibelle($L)); ?></td>
        <td><?php echo htmlspecialchars($L['num_lot'] ?? '—'); ?></td>
        <td><?php echo htmlspecialchars($L['scan_produit'] ?? '—'); ?></td>
        <td><?php echo htmlspecialchars((string) $getRef($L)); ?></td>
        <td><?php echo htmlspecialchars((string) $getCodeMinos($L)); ?></td>
        <td><?php echo (int) ($L['quantite'] ?? 0); ?></td>
        <td><?php echo htmlspecialchars($L['action_produit'] ?? '—'); ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</page>
