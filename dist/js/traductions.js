$(() => {
    initTrads()
    $('input[name=code]').focus();
})
let indexes = [], dataTableItem = null;
const initTrads = async () => {
    let t = $("#tradTable"),
        theads = t.find('thead th')
    t.find('tbody').html(`<tr><td colspan="${theads.length}" class="text-center">Chargement des traductions...</td></tr>`)

    indexes = []
    theads.each(function() {
        let code = $(this).attr('data-col');
        if( typeof code == "undefined" ) code = "code";
        indexes.push(code);
    })

    let trads = await api({methode:'lang::getTrads'})
    let tbody = $('<tbody>')
    
    for( let i in trads ) {
        let tr = $('<tr>')
        tr.append(`<td>${i}</td>`)
        indexes.forEach( lang => { if( trads[i][lang] ) tr.append(`<td>${trads[i][lang]}</td>`) })
        tbody.append(tr)
    }
    if( dataTableItem != null ) $('#tradTable').DataTable().destroy();
    t.find('tbody').replaceWith(tbody)
    dataTableItem = t.dataTable({
        lengthMenu: [  [50, 200, -1], [50, 200, 'Tous'] ]
    })
}


$(() => $(document).on('click.tdClickable','#tradTable tbody td',function() {
    editTableTradElement( $(this) )
}))

let editingItem = null
const editTableTradElement = td => {
    editingItem = {
        lang : indexes[td.index()],
        val : td.html(),
        code : td.parents('tr:first').find('td:first').text()
    }
    if( editingItem.lang == "code" ) {
        editingItem = null;
        return false;
    }
    if( td.attr('contenteditable') == "true" ) return;
    td.attr('contenteditable',true)
    td.text(editingItem.val)
    td.select().focus()
    td.blur(async function() {
        td.off().attr('contenteditable',false)
        editingItem.val = $(this).text()
        editingItem.methode = "lang::saveTrad"
        let trad = await api(editingItem).then( r => r.trad )
        td.html(trad)
        editingItem = null;
    })
}
const createTrad = async () => {
    let f = $("#formAddTrad"),
        datas = formToJson(f)
    datas.methode = 'lang::createTrad';
    await api(datas)
    f.get(0).reset()
    notif("Traduction ajoutée")
    initTrads()
    f.find('[name=code]').focus()
}