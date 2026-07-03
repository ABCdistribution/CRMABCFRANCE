<?php
include_once("init_scripts.php");

importAS400::importClient();
importAS400::importArticle();
importAS400::importFactures();
importAS400::importTarifs();
importAS400::importCentrales();
importAS400::importReferentiel();
importAS400::importStockArticle();
importAS400::importColis();

die("_FIN_ referentiels.php");
