<?php
$i = importAS400::getLastImportDate();
?>
<div class="alert alert-info isLink" onclick="importAS400(this)">
	Afin de lancer un import du fichier de l'AS400 cliquez ici.<br/>
	Le dernier import était le <?php echo core::dateOutput($i['date_creation']);?> et a duré <?php echo core::printableSeconds($i['total']);?>
</div>