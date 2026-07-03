<?php
global $params;
if( in_array("tst",$params) ) {

/*
$import = new importAS400();
echo "L'import a prit au total ".$import->lastChrono()." secondes";
*/


die('ok');
$login = 'gescomtest';
$pass = '9hpoizea90uYNyJs51xw';
$obj = new login( $login, $pass );
if( $obj->ldap->error ) die('Impossible de se connecter');

echo 'LDAP ENTRIES : <pre>';
print_r($obj->ldap->ldap_entries);

/*
$o = new ldap();
$o->dump();*/

?>














<?php
return;
}

?>
<p style="text-align:center;margin-top:150px;">
  Work in progress<br/>
  <br/>
  <a href="http://snew.fr/">
    <img src="http://snew.fr/img/logo_snew.jpg" target="_blank" height="40px" alt="SNEW">
  </a>
</p>
