let inputFile = null,
    wrapperImport = null,
    keys = ['annee','mois','id_repr','ca'],
    selectCS;
$(()=>{
    inputFile = $("#fileInput")
    wrapperImport = $("#importDiv")
    selectCS = $("#selectCS")
    inputFile.on('change',() => readCsv() )
    selectCS.on('change', () => getCSCA() )  
})
const uploadCSV = () => inputFile.click()
const readCsv = () => {
    loader(true,wrapperImport)
    let r = new FileReader();
    r.readAsBinaryString( inputFile[0]?.files[0] )
    r.onload = async () => {
        let json = await stringToJson(r.result)
        if( !json ) return;
        ajax({
            methode:'prospection::injectCA',
            datas : json
          },function() {
            loader(false,wrapperImport)
            toast("Fichier injecté");
        })
    }
}
const stringToJson = (string) => {
    let csv = string.split("\r"),
        errors = [],
        result = [];
    csv.forEach( (e,i) => {
        if( e.trim() == "" ) return;
        let datas = e.split(';')
        if( datas.length != 4 ) {
            errors.push(`Ligne ${i} mal formée`);
            return false;
        }
        let tmp = {}
        keys.forEach( (el,index) => tmp[el] = datas[index].trim() )
        result.push(tmp)
    });
    return JSON.stringify(result)
}
const getCSCA = () => {
    let id = parseInt(selectCS.val()),
        d = $("#resultCA");
    d.empty();
    if( id < 1 ) return;
    api({
        methode : 'prospection::getCACS',
        id_repr : id
    }).then( r => {
        buildTableCSCA( r );
    })
}
const buildTableCSCA = datas => {
    if( datas.length == 0 ) return toast( l('js-prospectionca-noca'));

    let table = $("<table>"),
        d = $("#resultCA");
    table.addClass('table table-condensed table-striped')
    
    let thead = $("<thead>"),
        tr = $('<tr>')
    tr.append(`<th>${ l('js-prospectionca-table-id') }</th>`)
    tr.append(`<th>${ l('js-prospectionca-table-periode') }</th>`)
    tr.append(`<th>${ l('js-prospectionca-table-obj') }</th>`)
    thead.append(tr)
    table.append(thead)

    let tbody = $('<tbody>');
    datas.forEach( el => {
        let tr = $('<tr>')
        tr.append(`<td>${el.id_repr}</td>`)
        tr.append(`<td>${el.mois} ${el.annee}</td>`)
        tr.append(`<td>${el.total}€</td>`)
        tbody.append(tr)
    })
    table.append(tbody)
    d.append(table)
}