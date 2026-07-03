<style>
    table.produits {
        width:100%; 
        vertical-align:middle; 
        font-size:12px; 
        text-align:center;        
    }
    table.produits tbody td,
    table.produits thead th {
        padding: 4px;
    }
</style>

<page backtop="55mm" backbottom="10mm">
    <?php 
    include(PARTIAL."pdf/commande-juva/page_header.php");
    include(PARTIAL."pdf/commande-juva/page_footer.php");
    ?>
    <div style="width:100%;">
        
        <table class="produits" border="1">
        <thead>
                <tr>
                    <th style="text-align:left;">CODE ARTICLE</th>
                    <th style="text-align:left; height:30px;">DESIGNATION</th> 
                    <th>PCB </th>
                    <th>QTE</th>  
                    <th>EAN13</th> 
                    <th>CODE BARRE</th> 
                </tr>
            </thead>
            <tbody>
                <?php
                $table = [];
                
                foreach ($cmd['produits'] as $c) {
                    $tmp = ['<tr>'];
                    $codeProduit = $c['produit'] ?? $c['Produit'] ?? '';
                    $p = produit::get($codeProduit, 'idoriginal', true);
                    
                    // On récupère juste le code produit et la quantité
                    $quantite = $c['quantite'] ?? $c['Quantite'] ;
                    $totalQTE = $quantite * $p['pcb'];

                    $tmp[] = '<td style="width:80px;text-align:left;">' . htmlspecialchars($codeProduit) . '</td>';
                    $tmp[] = '<td style="width:200px; text-align:left;font-size:10px;">' . htmlspecialchars($p['libelle']) . '</td>';
                    $tmp[] = '<td style="width:40px;">' . intval($quantite) . '</td>';
                    $tmp[] = '<td style="width:40px;">' .$totalQTE . '</td>';
                    $tmp[] = '<td style="width:120px;">' . $p['gencode'] . '</td>';
                    $tmp[] = '<td style="width:140px;"><barcode type="EAN13" value="' . $p['gencode'] . '" style="color: #000; width:30mm; height:6mm; font-size:2.5mm"></barcode></td>';
                    

                    $tmp[] = '</tr>';
                    $table[] = implode($tmp);
                }
                echo implode($table);
                ?>
            </tbody>
        </table>

    </div>
</page>
