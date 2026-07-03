const addStrat = async () => {
    let p = await api({
        methode : 'produit::addStratPem',
        libelle : $("#addStratPem [name=libelle]").val()
    })
    Swal.fire({
        icon : "success",
        title : l('js-strat-pem-ajoutee')
    })
    printStrats()
}
const getStrats = () => 
    new Promise( resolve => api({methode : 'produit::apiGetStratsPem'}).then( r => resolve(r.strats) ))

$( () => printStrats() )
const printStrats = async ()  => {
    let tmp = await getStrats(),
        strats = [],
        d = $("#stratsWrapper")

    for( let i in tmp ) {
        let o = []
        for( let j in tmp[i]['lines'] ) o.push( tmp[i]['lines'][j] )
        tmp[i]['lines'] = o
        strats.push(tmp[i])
    }
    if( strats.length == 0 ) {
        d.html("<p class='text-center text-secondary'>"+l('js-strat-pem-vide')+"</p>")
        return;
    }

    let html = []
    for( let i in strats ) {
        html.push(`<details id="strat_${strats[i].id}"><summary>${strats[i].libelle}</summary>`)

        html.push(`
            <table class="table" data-id="${strats[i].id}">
                <thead>
                    <tr>
                        <th>${l('js-strat-pem-marque')}</th>
                        <th>${l('js-strat-pem-ref')}</th>
                        <th>${l('js-strat-pem-ean')}</th>
                        <th>${l('js-strat-pem-cug')}</th>
                        <th>${l('js-strat-pem-gamme')}</th>
                        <th>${l('js-strat-pem-action')}</th>
                    </tr>
                </thead>
                <tbody>
        `)
        for( let j in strats[i].lines ) {
            let l = strats[i].lines[j]
            html.push(getLine(l))
        }

        html.push('</tbody></table>')
    


        html.push(`
            <div class="text-center mt-3 mb-3">
                <button class="btn btn-success btn-sm addLine mr-5"><i class="fas fa-plus"></i></button>
                <button class="btn btn-warning btn-sm editStrat mr-5" onclick="editStrat(${strats[i].id})"><i class="fas fa-edit"></i></button>
                <button class="btn btn-danger btn-sm" onclick="delStrat(${strats[i].id})"><i class="fas fa-trash"></i></button>
            </div>
        `)
        html.push('</details>')
    }
    d.html(html.join(''))
}

const getLine = (l = {}) => {
    return `
    <tr data-id="${l.id??0}">
        <td><input type="text" name="marque" value="${l.marque??""}"/></td>
        <td><input type="text" name="reference" value="${l.reference??""}"/></td>
        <td><input type="text" name="ean" value="${l.ean??""}"/></td>
        <td><input type="text" name="cug" value="${l.cug??""}"/></td>
        <td><input type="text" name="gamme" value="${l.gamme??""}"/></td>
        <td><button class="btn btn-danger btn-sm delItem"><i class="fas fa-times"></i></button></td>
    </tr>`
}

$(()=>  $(document).on('click.addLine','.addLine', function(){ addLine( $(this) )}) )
const addLine = (el) => el.parents('details').find('table tbody').append(getLine())

const delStrat = (id) => {
    mConfirm(l('js-strat-pem-delete'),function() {
        api({methode : 'produit::delStrat',id : id})
        $("#strat_"+id).remove()
    })
}

$(()=>  $(document).on('click.delItem','.delItem', function(){ delItem( $(this) )}) )
const delItem = (el) => {
    let id = el.parents('tr:first').attr('data-id')
    if( id > 0 ) api({methode : 'produit::delLinePem',id : id})
    el.parents('tr:first').remove()
}

$(document).ready(function() { $(document).on('blur.save','#stratsWrapper input', function(){ saveField( $(this) )}); }) 
const saveField = (el) => {
    let datas = {
        id : el.parents('tr:first').attr('data-id'),
        id_strat : el.parents('table:first').attr('data-id'),
        name : el.attr('name'),
        value : el.val(),
        methode : 'produit::pemSaveLineField'
    }
    api(datas).then( r => {
        if(datas.id == 0 && r.id && r.id > 0)
            el.parents('tr:first').attr('data-id',r.id)
    })
}

const editStrat = async id => {
    let lib = $("#strat_"+id+" summary").text()
    const { value: newLibelle } = await Swal.fire({
        title: l('js-strat-pem-edit'),
        input: 'text',
        inputLabel: l('js-strat-pem-label-edit'),
        inputValue: lib,
        showCancelButton: true,
        inputValidator: (value) => {
          if (!value) {
            return l('js-strat-pem-label-error')
          }
        }
      })
      if (!newLibelle) return;

    let p = await api({
        methode : 'produit::editStratName',
        libelle : newLibelle,
        id : id
    })
    $("#strat_"+id+" summary").text(newLibelle)

}