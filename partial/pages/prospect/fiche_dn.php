<?php
if( !isset($prospect['dn']) || empty($prospect['dn']) ) {
    echo "Aucune DN";
    return;
}
?>
<table class="table table-condensed">
    <thead>
        <th><?php echo l('dn-nom');?></th>
        <th><?php echo l('dn-gamme');?></th>
        <th><?php echo l('dn-metrage');?></th>
        <th><?php echo l('dn-broches');?></th>
        <th><?php echo l('dn-plv');?></th>
        <th><?php echo l('dn-pem');?></th>
    </thead>
    <tbody>
        <?php
        $dn = $prospect['dn'];
        $tmp = [];
        foreach( $dn as $k=>$e ) {
            $tmp[] = '
            <tr>
            <td>'.$e['nom'].'</td>
            <td>'.$e['gamme'].'</td>
            <td>'.$e['metrage'].'</td>
            <td>'.$e['broches'].'</td>
            <td>'.$e['plv'].'</td>
            <td>'.$e['pem'].'</td>
            </tr>
            ';
        }
        echo implode("",$tmp);
        ?>
    </tbody>
</table>
