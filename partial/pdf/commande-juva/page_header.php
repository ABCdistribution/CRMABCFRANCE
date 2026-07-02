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
                                    <td style="font-size:10px;">
                                        <strong>Abcosmetique</strong><br/>
                                        <br/>
                                        SA BONNEUIL EXPLOI LEC 94 BONNEUIL<br/>
                                        1/3, RUE DU BI-CENTENAIRE<br/>
                                        CENTRE CIAL ACHALAND<br/>
                                        MAG 42<br/>
                                        94380 BONNEUIL SUR MARNE<br/>
                                        FRANCE<br/>                     
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        
                        <div style="font-size:10px; margin-top:5px;">
                            <table style="width:100%;" cellpadding="0" cellspacing="0">
                                <tbody>
                                    <tr><td style="width:80px;">Téléphone :</td><td>+33 143777309</td></tr>
                                    <tr><td>Fax :</td><td>+33 143777308</td></tr>
                                    <tr><td>SIRET :</td><td>43974961500027</td></tr>
                                    <tr><td>N°TVA :</td><td>FR66439749615</td></tr>
                                    <tr><td>Client :</td><td><strong>#<?php echo $cmd['client']['num_juva'];?></strong></td></tr>
                                </tbody>
                            </table>
                        </div>
                    </td>
                    <td style="width: 40%;">
                        Bonneuil, le <?php echo date('d/m/Y');?><br/>
                        <br/>
                        <div style="font-size:10px; width:100%;">
                            A l'attention de :<br/>
                            <br/>
                            <?php 
                                echo '<strong>'.$cmd['client']['enseigne'].'</strong><br/>';
                                if( $cmd['client']['adresse1'] != "" ) echo $cmd['client']['adresse1'].'<br/>';
                                if( $cmd['client']['adresse2'] != "" ) echo $cmd['client']['adresse2'].'<br/>';
                                if( $cmd['client']['adresse3'] != "" ) echo $cmd['client']['adresse3'].'<br/>';
                                echo $cmd['client']['code_postal'].' ';
                                echo $cmd['client']['ville'];
                            ?>

                            <br/><br/>

                            <table cellpadding="0" cellspacing="0">
                                <tbody>
                                    <?php if( $cmd['commande']['client'] != "" ) { ?>
                                    <tr>
                                        <td style="width:120px;">N° Commande client :</td>
                                        <td><?php echo $cmd['commande']['client'];?></td>
                                    </tr>
                                    <?php } ?>

                                    <tr>
                                        <td style="width:120px;">N° Commande ABC :</td>
                                        <td><?php echo str_pad($cmd['commande']['code'],14,"0",STR_PAD_LEFT);?></td>
                                    </tr>
                                    <tr>
                                        <td>Date de livraison :</td>
                                        <td>
                                            <strong><?php echo core::dateOutput($cmd['commande']['datelivraison']);?></strong>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Promoteur :</td>
                                        <td>
                                            <?php echo $cmd['user']['displayname'];?>
                                        </td>
                                    </tr>                                        
                                </tbody>
                            </table>     

                        </div>

                                                
                    </td>                        
                </tr>
            </tbody>
        </table>

        <div style="text-align: center; font-size:14px; font-weight:bold;">
            Bon de commande au <?php echo date('d/m/Y');?>
        </div>
    </div>
</page_header>