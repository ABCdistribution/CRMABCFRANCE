<?php
$dn = deportedFiles::getLastDn($magasin['id_as400']);
if( empty($dn['abc']) && empty($dn['concu']) ) {
echo '<p class="tc text-secondary">Non renseigné</p>';
}
else {
    ?>
    <table class="table table-condensed">
    <thead>
        <th><?php echo l('dn-type');?></th>
        <th><?php echo l('dn-marque');?></th>
        <th><?php echo l('dn-gamme');?></th>
        <th class="tc"><?php echo l('dn-metrage');?></th>
    </thead>
    <tbody>
        <?php
        $tmp = [];
        foreach( $dn as $key => $subdn ) {
        foreach( $subdn as $k=>$e ) {
            $c = ( $key == "abc" ? 'bg-abc' : 'bg-concu' );
            $tmp[] = '
            <tr class="'.$c.'">
            <td>'.($key == "concu" ?  l('dn-concurence') : "ABC").'</td>
            <td>'.$e['ma'].'</td>
            <td>'.$e['ga'].'</td>
            <td class="tc">'.$e['me'].' m</td>
            </tr>
            ';
        }
        }
        echo implode($tmp);
        ?>
    </tbody>
    </table>
    <?php
}
?>