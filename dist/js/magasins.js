$(document).ready(function() {
  $("#fieldSearchClient").keyup(function(e) {
    if( e.which == 13 || e.keyCode == 13 )
      searchClient();
  })
})
function searchClient() {
  let field = $("#fieldSearchClient"),
      search = field.val().toLowerCase().trim(),
      div = $("#resultSearchClient");
  if( search.length < 2 ) return;
  ajax({
    methode : 'ref::searchClient',
    search : search
  },function() {
    div.empty();
    let icon = '<i class="fas fa-long-arrow-alt-right ml"></i>';
    for( var i in ajaxDatas ) {
      let tr = $('<tr>');
      tr.append('<td>'+ajaxDatas[i].libelle+'</td>');
      tr.append('<td align="right"><a href="'+_global.app_url+'Magasins/Fiche-'+ajaxDatas[i].id+'" class="btn btn-default btn-sm">'+l('js-fiche-client')+' '+icon+'</a></td>');
      div.append(tr);
    }
  })
}
const newClient = async function() {

  const { value: name } = await Swal.fire({
    title: l('js-create-client-libelle'),
    input: 'text',
    inputPlaceholder: l('js-create-client-libelle-rs'),
  })

  if (name) {
    loader();
    ajax({methode:"client::create",name:name},function() {
      $(location).attr('href', _global.app_url+"Magasins/Fiche-" + ajaxDatas.id );
    })
  }

}
$(()=>{
  $(document).on('change.periodicite','select[name=periodicite]',function() {
    let v = $(this).val(),
        id = $(this).attr('data-id');
    ajax({methode:"client::addPeriodicite",id:id,v:v},function() {
      info( l("js-save-perdiodicite"))
    })
  })
  $(document).on('change.directeurRegional','select[name=id_user_dr]',function() {
    let v = $(this).val(),
        id = $(this).attr('data-id');
    ajax({methode:"client::saveDirecteurRegional",id:id,v:v},function() {
      info( l("js-save-infos"))
    })
  })
  $(document).on('click.btnSaveInfosSupp','.btnSaveInfosSupp',()=>{
    saveInfosSuppClient()
  })
})
function saveInfosSuppClient() {
  ajax("methode=client::saveInfosSupp&"+$("#formInfosSup").serialize(),function() {
    info( l("js-save-infos"))
  })  
}

$(()=>{
  if( $("#lastVisits").length == 1 ) getLastVisits();
  if( $("#cmdMinos").length == 1 ) getLastCmds();
  $(document).on('click.getDetailsCmd','.getDetailsCmd', function() {  
    getDetailCmd($(this).attr('data-id')) 
  })
})

const getLastVisits = () => {
  let d = $("#lastVisits"),
      id_as400 = d.attr('data-id');
  ajax({
    methode:'visite::getLastClientVisitesPrintable',
    id_as400 : id_as400,
    limit : 200
  },function() {
    d.html(decodeURIComponent(ajaxDatas.html))
  })
}
const getLastCmds = () => {
  let d = $("#cmdMinos"),
      id_as400 = d.attr('data-id');
  ajax({
    methode:'commande::getLastCmdMinosPrintable',
    id_as400 : id_as400,
    limit : 200
  },function() {
    d.html(decodeURIComponent(ajaxDatas.html))
  })
}
const getDetailCmd = numero => {
  ajax({
    methode:'commande::getDetailsCmdMinos',
    numero : numero
  },function() {
    let content = decodeURIComponent(ajaxDatas.html);
    Swal.fire({
      title : "#"+numero,
      html : content,
      width : 600,
    })
  })  
}