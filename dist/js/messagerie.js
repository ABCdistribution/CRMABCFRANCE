let mailList = null,
    readerBody = null,
    id_partenaire = null,
    id_lastMsg = null;

let intervalGetConversations = setInterval(()=>getMessages(), 10000)
let intervalGetDialog = setInterval(()=>getDialogUpdates(), 10000)

$( () => initMailBox() )
let initMailBox = () => {

    mailList = $("#mail-list");
    readerBody = $("#right-panel");
    readerBody.html( $("#disclaimerTemplate").html())
    getMessages();

    $(document).on('click.bloc-msg','.bloc-msg',function() {
        selectConversation($(this))
    })
    $(document).on('keyup.filter','#filter', ()=>{
        filterConversations()
    })
    $(document).on('keyup.message','.bot input', function(e) {
        if( id_partenaire == null || e.which != 13 ) return;
        e.stopPropagation();
        e.preventDefault();
        sendMessage();
    });


    $(document).on('click.windowNewMsgList','.windowNewMsg .list-group a',function() {
        selectUserOnList($(this))
    })
    $(document).on('keyup.sendNewConvers','#right-panel .windowNewMsg .input input',function(e) {
        if( e.which == 13 )
            sendNewMessage();
    })
}
let getMessages = () => {
    return new Promise( (resolve,reject) => {
        ajax({methode : 'mailbox::getMessages'}, function(){
            if( ajaxDatas.nomsg ) {
                mailList.find(".loader").html(l("js-messagerie-vide"));
                return;
            }
            mailList.empty();
            let zIndex = Object.keys(ajaxDatas).length + 1;
            ajaxDatas.forEach( item => {
                let d = $('<div>');
                d.addClass('bloc bloc-msg');
                d.attr('data-id',item.id)
                if( item.r == 0 ) d.addClass('unread');
                d.css('z-index', zIndex-- );
                d.append('<div class="icon"><i class="fas fa-user-circle"></i></div>');
                let r = $('<div>');
                r.addClass('right');
                r.append('<div class="row"><div class="col n">'+item.n+'</div><div class="col-3 d">'+item.d+'</div></div>');

                let iconRead = ( item.r == 1 ? '<i class="fas fa-check"></i>' : '<i class="far fa-envelope pulse"></i>' );
                r.append('<div class="prev">'+item.m+' '+iconRead+'</div>');
                d.append(r);
                mailList.append(d)
            });
        });
    })
}

let selectConversation = item => {
    if( item.hasClass('selected') ) return;
    // reset
    mailList.find('.bloc-msg.selected').removeClass('selected');
    item.addClass('selected')
    item.removeClass('unread')
    item.find('.fa-envelope').replaceWith('<i class="fas fa-check"></i>')
    readerBody.html('<div class="loader"><i class="fas fa-circle-notch spin"></i><br/>'+l("js-messagerie-chargement")+'</div>');
    // getmessages
    let id = item.attr('data-id')
    ajax({
        methode : 'mailbox::getEchanges',
        id : id
    },function() {
        showConversation(ajaxDatas)
    })
}
let showConversation = dialog => {

    id_partenaire = dialog.id_partenaire;

    // title bar
    let t = $('<div>');
    t.addClass('top')
    t.append('<div class="icon"><i class="fas fa-user-circle"></i></div>')
    t.append('<div class="name">'+dialog.name+'</div>')
    readerBody.html(t);

    // Bottom bar
    if( id_partenaire > 0 ) {
        let b = $('<div>');
        b.addClass('bot')
        b.append('<div class="input"><input type="text" placeholder="'+l("js-messagerie-taper")+'"/></div>')
        b.append('<div class="sbtn"><button class="btn btn-primary" onclick="sendMessage()"><i class="far fa-paper-plane"></i></button></div>')
        readerBody.append(b);    
    }

    // Dialog body
    let body = $('<div class="body">')
    dialog.msgs.forEach( item => body.append(createMsg(item)) )    
    readerBody.append(body)
    updateTooltip();

    
    readerBody.find('.input input').focus()
    scrollBot();
    updateNotifs( dialog.notif );
}

let createMsg = item => {
    let msg = $('<div class="msg">')
    msg.attr('data-id', item.id)
    id_lastMsg = item.id
    msg.addClass( item.o == 1 ? 'me' : 'notme' )
    let bubble = $('<div class="bu">');
    let txt = $('<div class="txt">')
    txt.html(item.m);
    if( item.i != 0 ) 
        txt.prepend('<img src="'+item.i+'" class="viewer"/><br/>');
    bubble.append(txt);
    bubble.append('<span class="d">'+item.d+'</span>')
    if( item.o == 1 ) {
        if( item.r == 1 )
            bubble.append('<span class="r"><i class="fas fa-check lu" rel="tooltip" title="'+l("js-messagerie-statut-lu")+'"></i></span>')
        else
            bubble.append('<span class="r"><i class="fas fa-check" rel="tooltip" title="'+l("js-messagerie-statut-nonlu")+'"></i></span>')

    }
    msg.append(bubble);
    return msg;
}

let scrollBot = () => {
    var d = readerBody.find('.body');
    d.scrollTop(d.prop("scrollHeight"));
}

let filterConversations = () => {
    let v = $("#filter").val().trim().toLowerCase(),
        c = mailList.find('.bloc-msg');
    if( v == "" ) 
        return c.each(function() { $(this).show(); })
    c.each(function() {
        let n = $(this).find('.n').text().trim().toLowerCase();
        n.includes(v) ? $(this).show() : $(this).hide()
    })
}
let sendMessage = () => {
    if( id_partenaire == null ) return;
    let f = $(".bot input"),
        v = f.val().trim();
    if( v == ""  ) return;
    ajax({
        methode:"mailbox::sendMsg",
        id_partenaire : id_partenaire,
        msg : v
    },() => {
        f.val('');
        addMessage( ajaxDatas.msg );
        getMessages();
    })
}
let addMessage = msg => {
    let body  = readerBody.find('.body');
    body.append(createMsg(msg))
    updateTooltip();
    scrollBot();
}
let updateNotifs = o  => {
    nb = o.length;
    let e = $("#nav-menu .nbmsg");
    if( e.length == 0 ) return;
    if( nb == 0 ) return e.remove();
    e.text(nb);
}
let getDialogUpdates = () => {
    ajax({
        methode : 'mailbox::getLastMsg',
        id_partenaire : id_partenaire,
        id_lastMsg : id_lastMsg
    },function() {

        if( Object.keys(ajaxDatas).length > 0 ) {

            let body = $('#right-panel .body');
            for( let i in ajaxDatas ) {
                body.append(createMsg(ajaxDatas[i]))
                id_lastMsg = ajaxDatas[i].id
            } 
            updateTooltip();           
            readerBody.find('.input input').focus()
            scrollBot();

        }
        
    })    
}

let newMsg = () => {
    let tpl = $("#newMsgTemplate").html(),
        r = $("#right-panel");
    if( r.find('.windowNewMsg').length ) return;
    r.append(tpl);
}
const closeWindowNewMsg = () => {
    $("#right-panel .windowNewMsg").remove();
}
const selectUserOnList = (item) => {
    let isFrom = ( item.parents('.list-group:first').hasClass('listFrom') );
    if( !isFrom ) {
        item.remove();
        $("#right-panel .windowNewMsg .listFrom a[data-id="+item.attr('data-id')+"]").show()
        return;
    }
    $("#right-panel .windowNewMsg .listTo").append(item.clone())
    item.hide();
}
const sendNewMessage = () => {
    let ids = [];
    $("#right-panel .windowNewMsg .listTo a").each(function() {
        ids.push($(this).attr('data-id'))
    });
    if( ids.length == 0 ) {
        return Swal.fire({
            icon: 'error',
            title: l("js-messagerie-send-dest"),
            text: l("js-messagerie-send-dest-select"),
        })
    }
    let msg = $("#right-panel .windowNewMsg .input input").val().trim();
    if( msg == "" ) {
        return Swal.fire({
            icon: 'error',
            title: l("js-messagerie-send-msg"),
            text: l("js-messagerie-send-msg-vide"),
        })
    }
    ajax({
        methode : 'mailbox::newConversation',
        ids : ids,
        msg : msg
    },function() {   
        readerBody.html( $("#disclaimerTemplate").html())
        closeWindowNewMsg();
        getMessages();
        toast(l("js-messagerie-sent"))
    });
}