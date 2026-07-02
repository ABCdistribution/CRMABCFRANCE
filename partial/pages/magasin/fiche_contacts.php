<?php
foreach($magasin['contacts'] as $c ) {
    echo '
    <div class="card card-contact" style="width: 18rem; display:inline-block;">
        <div class="card-body">
            <h5 class="card-title"><span>'.$c['prenom'].' '.$c['nom'].'</span></h5>
            <p class="card-text">
                <i class="fas fa-user-tie"></i> '.$c['poste'].'<br/>
                <i class="fas fa-phone-alt"></i> '.$c['fixe'].'<br/>
                <i class="fas fa-mobile-alt"></i> '.$c['portable'].'<br/>
                <i class="fas fa-at"></i> '.$c['mail'].'<br/>
            </p>
        </div>
    </div>
    ';
}
?>