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
    include(PARTIAL."pdf/commande/page_header.php");
    include(PARTIAL."pdf/commande/page_footer.php");
    ?>

    <div style="width:100%;">
        
        <table class="produits" border="1">
            <thead>
                <tr>
                    <th style="text-align:left;">Code article</th>
                    <th style="text-align:left; height:30px;">Désignation</th>
                    <th>PCB</th>
                    <th>Qté Cdéé</th>
                    <th>EAN13</th>
                    <th>Code barre</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $table = [];
                foreach( $cmd['produits'] as $c ) {
                    $tmp = ['<tr>'];
                    $p = produit::get($c['id_produit'],"id_as400");
                    if( !$p ) continue;
                    $tmp[] = '<td style="width:80px;text-align:left;">'.$p['id_as400'].'</td>';
                    $tmp[] = '<td style="width:200px; text-align:left;font-size:10px;">'.$p['libelle'].'</td>';
                    $tmp[] = '<td style="width:40px;">'.$c['pcb'].'</td>';
                    $tmp[] = '<td style="width:60px;">'.$c['quantite'].'</td>';
                    $tmp[] = '<td style="width:100px;">'.$p['gencode'].'</td>';
                    $tmp[] = '<td style="width:140px;"><barcode type="EAN13" value="'.$p['gencode'].'" style="color: #000; width:30mm; height:6mm; font-size:2.5mm"></barcode></td>';
                    $tmp[] = '</tr>';
                    $table[] = implode($tmp);
                }
                echo implode($table);
                ?>
            </tbody>
        </table>

    </div>
    <?php # <barcode type="EAN13" value="3666085151135" style="color: #770000" ></barcode> ?>


</page>