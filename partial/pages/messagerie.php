<?php if( !securite::can(19) ) return core::restricted();?>

<style>
    #page { background: #eee; }
</style>

<p class="rd" id="mailboxDisclaimer"><?php echo l('messagerie-disclaimer');?></p>

<div id="mailbox">
    <div id="left-panel">
        <div class="bloc bloc-title">
            <p><i class="far fa-user-circle"></i> <?php echo $_SESSION['user']['displayname'];?></p>
        </div>
        <div class="bloc">
            <div class="row">
                <div class="col-8 text-center">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text" id="basic-addon1">
                            <i class="fas fa-search"></i>
                            </span>
                        </div>
                        <input type="text" class="form-control" id="filter" placeholder="Rechercher" />
                    </div>                    
                </div>
                <div class="col text-center">
                    <button class="btn btn-primary" onclick="newMsg()" >
                        <i class="far fa-plus-square"></i> <?php echo l('messagerie-nouveau');?>
                    </button>
                </div>                
            </div>
        </div>
        <div id="mail-list">
            <div class="loader">
                <i class="fas fa-circle-notch spin"></i>
                <br/>
                <?php echo l('messagerie-chargement-conversations');?>
            </div>
        </div>
    </div>
    <div id="right-panel">
        
    </div>    
</div>


<template id="disclaimerTemplate">
    <div class="disclaimer">
        <i class="far fa-comments"></i><br/>
        <p>
            <?php echo l('messagerie-welcome-msg');?>
            <br/>
            <span><?php echo l('messagerie-welcome-submsg');?></span>
        </p>
    </div>    
</template>
<template id="newMsgTemplate">
    <div class="windowNewMsg">
        <div class="top">
            <?php echo l('messagerie-start');?>
            <i class="fas fa-times" onclick="closeWindowNewMsg()"></i>
        </div>
        <div class="body">
            <div class="row">
                <div class="col">
                    <h2><i class="fas fa-users"></i> <?php echo l('messagerie-liste-destinataires');?></h2>
                    <div class="list-group listFrom">
                        <?php 
                        foreach( user::getAll() as $k=>$e ) { 
                            echo '<a href="#" data-id="'.$k.'" class="list-group-item list-group-item-action">'.$e['displayname'].'</a>';
                        }
                        ?>
                    </div>
                </div>
                <div class="col-1"></div>
                <div class="col">
                    <h2><i class="fas fa-user-check"></i> <?php echo l('messagerie-destinataires-select');?></h2>
                    <div class="list-group listTo"></div>
                </div>
            </div>
        </div>
        <div class="bot">
            <div class="input"><input type="text" placeholder="<?php echo l('messagerie-taper-message');?>"/></div>
            <div class="sbtn"><button class="btn btn-primary" onclick="sendNewMessage()"><i class="far fa-paper-plane"></i></button></div>
        </div>
    </div>
</template>