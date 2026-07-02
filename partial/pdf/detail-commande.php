<style>
    .c1 {color: #0087E0;}
    .b1 {background-color: #0087E0; color: #fff;}
    table.produits {
        width:100%; 
        vertical-align:middle; 
        font-size:12px; 
        text-align:center;        
    }
    table.produits tbody td,
    table.produits thead th {
        padding: 5px 0px;
        line-height: 18px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    table.produits tfoot td {
        padding: 0 10px;
        font-weight: bold;
        font-size: 15px;
    }

    table.produits thead th {
        text-align: center;
        min-width: 150px;
    }
    .te {
        text-align: right;
    }
    .ts {
        text-align: left;
    }
</style>

<page backtop="30mm" backbottom="8mm">

    <page_header>
        <div style="width:100%;">
            <table style="width:100%">
                <tbody>
                    <tr>
                        <td style="width: 60%;">
                            <table style="width:100%">
                                <tbody>
                                    <tr>
                                        <td style="width:100px;">
                                            <img src="<?php echo APP_ROOT;?>/img/logo.png" style="width:90px;height: 90px;"/>
                                        </td>
                                        <td style="font-size:16px;">
                                            <?php echo $cde['repr'];?><br/>
                                            <br/>
                                            <?php
                                            echo '<strong>'.$cde['client']['enseigne'].'</strong><br/>';
                                            echo $cde['client']['adresse1'].', ';
                                            echo $cde['client']['code_postal'].' ';
                                            echo $cde['client']['ville'].' (';
                                            echo $cde['client']['pays'].')<br/>';
                                            if( $cde['client']['siret'] != "" ) {
                                                echo 'SIRET : '.$cde['client']['siret'].' ('.$cde['client']['forme_entreprise'].') ';
                                            }
                                            echo ' <span class="c1">ID Cde : #'.$numero.'</span>';
                                            ?>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </page_header>
    <page_footer>
        <div style="font-size:9px;color:#888;text-align:center;">
        Accessoires Beauté Cosmétique Distribution - Siège social : 3, avenue des Violettes, Zac des petits Carreaux - 94386 Bonneuil-sur-Marne Cedex<br/>
        RCS Créteil B 439 749 615 - SAS au capital de 701 000 euros - www.abcosmetique.com
        </div>
        <div style="font-size:12px; font-weight:bold; text-align:right;">
            Page [[page_cu]]/[[page_nb]]
        </div>
    </page_footer>




    <div style="width:100%;">
        <table style="width:100%" class="produits" cellpadding="0" cellspacing="0">
            <thead>
                <tr class="b1">
                    <th style="height:50px; width: 100px;"></th>
                    <th>Famille commerciale</th>
                    <th style="width:90px">No<br/>Article</th>
                    <th>Libellé Article</th>
                    <th style="width:150px">EAN</th>
                    <th style="width:80px">Qté<br/>Prévue</th>
                    <th style="width:80px">Tarif<br/>Unitaire</th>
                    <th style="width:100px">C.A.</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $c = [];
                foreach( $cde['produits'] as $p ) {
                    $c[] = '<tr>';

                    $c[] = '<td class="ts">'.($p['famille_a'] ?? "" ).'</td>';                    
                    $c[] = '<td class="ts">'.($p['famille_b'] ?? "").'</td>';                    
                    $c[] = '<td class="ts">'.$p['id'].'</td>';                    
                    $c[] = '<td class="ts">'.$p['libelle'].'</td>';                    
                    $c[] = '<td>'.$p['barcode'].'</td>';                    
                    $c[] = '<td>'.$p['qte'].'</td>';                    
                    $c[] = '<td class="te">'.$p['tu'].' €</td>';                    
                    $c[] = '<td class="te">'.$p['total'].' €</td>';                    

                    $c[] = '</tr>';
                }
                echo implode($c);
                ?>
            </tbody>
            <tfoot>
                <tr class="b1">
                    <td colspan="5" style="height:45px"></td>
                    <td><?php echo $qteTotal;?></td>
                    <td></td>
                    <td class="te"><?php echo $total;?> €</td>
                </tr>
            </tfoot>
        </table>
    </div>
</page>