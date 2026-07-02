$(document).ready(function() {
  $("#fieldSearchProduit").keyup(function(e) {
    if( e.which == 13 || e.keyCode == 13 )
      searchProduit();
  })
})
function searchProduit() {
  let field = $("#fieldSearchProduit"),
      search = field.val().toLowerCase().trim(),
      div = $("#resultSearchProduit");
  if( search.length < 2 ) return;
  ajax({
    methode : 'ref::searchProduit',
    search : search
  },function() {
    div.empty();
    let icon = '<i class="fas fa-long-arrow-alt-right ml"></i>';
    for( var i in ajaxDatas ) {
      let tr = $('<tr>');
      tr.append('<td>'+ajaxDatas[i].libelle+'</td>');
      tr.append('<td align="right"><a href="'+_global.app_url+'Produits/Fiche-'+ajaxDatas[i].id+'" class="btn btn-default btn-sm">'+l('js-fiche-produit')+' '+icon+'</a></td>');
      div.append(tr);
    }
  })
}
function saveInfosSupp() {
  let f = $("#formInfoSup").serialize();
  ajax("methode=produit::saveInfosSupp&"+f,function(){
    info("Modifications sauvegardées");
  })
  return false;
}


$(document).ready(function() {
  $(document).on('blur.id_switch','#id_switch',() => {
    isValidCodeArticle().then(
      r => {
        if( r ) $("#stateController").html( '<i class="fas fa-check text-success"></i>' )
        else $("#stateController").html( '<i class="fas fa-times text-danger" rel="tooltip" title="Article inconnu"></i>')
      }
    )
  })
})
const isValidCodeArticle = () => {
  return new Promise( (resolve,reject) => {
    ajax({methode:'produit::isValid',id_as400:$("#id_switch").val()},function(){
      resolve(ajaxDatas.valid);   
    })
  })
}

const saveArticleSwitch = async() => {
  let datas = {
    methode : 'produit::saveSwitchArticle',
    seuil : parseInt( $("#id_qte_switch").val() ),
    id_switch : $("#id_switch").val().trim(),
    id_produit : $("form.form-save").attr('data-id')
  }
  let valid = await isValidCodeArticle(datas.id_switch)
  if( !valid ) return error(l("js-article-swtich-error"));
  ajax(datas,()=> toast(l("js-article-swtich-success")) )

}
const killArticleSwitch = () => {
  let datas = {
    methode : 'produit::killSwitchArticle',
    id_produit : $("form.form-save").attr('data-id')
  }
  if( !confirm(l("js-article-swtich-delete")) ) return;
  ajax(datas,()=> reload() )
}




$(() => { if( $("#list-comp").length == 1 ) init_ProduitComplementaire(); })
const init_ProduitComplementaire = () => {
  getListProduitComp()
}
const getListProduitComp = async () => {
  let div = $("#list-comp"),
      datas = {
        methode : 'produit::getListArticlesComp',
        id_as400 : $('[name=id_as400]').val()
      }
  let r = await api(datas)
  if( r.count == 0 ) return div.html('<p class="text-muted text-center">'+l('js-page-produit-comp-vide')+'</p>')

  div.empty()
  for(let i in r) {
    div.append(`
      <div class="list-group-item ${r[i].actif == 1 ? 'actif' : 'inactif'}">
        <a href="${_global.app_url+'Produits/Fiche-'+r[i].id}" target="_blank">
          #${r[i].id_as400} - ${r[i].libelle} (x ${r[i].qte})
        </a>
        <div class="row sub">
          <div class="col-md-6">
            <strong>Statut : </strong> <a href="#" onclick="compChangeStatut('${r[i].id_as400}')">${r[i].actif == 1 ? 'Actif' : 'Inactif'}</a>
          </div>
          <div class="col-md-6 text-right">
            <a href="#" class="text-danger" onclick="compDelete('${r[i].id_as400}')">Supprimer</a>
          </div>  
        </div>
      </div>
    `)
  }

}

const addProduitComp = async () => {

  await Swal.fire({
    title: l("js-page-produit-comp-add-titre"),
    cancelButtonText : 'Annuler',
    html : `
      <form id="formAddProduitComp" class="text-left">
        <div class="form-group">
          <label>${l("js-page-produit-comp-add-code")}</label>
          <input type="text" class="form-control" name="code" />
        </div>
        <div class="form-group">
          <label>${l("js-page-produit-comp-add-qtePcb")}</label>
          <input type="number" class="form-control text-right" name="pcb" value="1" />
        </div>
      </form>    
    `,
    didOpen : () => $("#formAddProduitComp [name=code]").focus(),
    preConfirm: async () => {
      let jsonForm = formToJson($("#formAddProduitComp"))
      jsonForm.methode = 'produit::addProduitComp'
      jsonForm.id_as400 = $('[name=id_as400]').val()
      if( jsonForm.code.trim() == ""  ) return false;
      let r = await api(jsonForm)
      if( r.ko ) {
        alert(r.ko)
        return false;
      }
      if( r.ok ) {
        Swal.close()
        getListProduitComp()
      }
      return false
    }
  });    
  

}

const compChangeStatut = async id_as400 => {
  await api({
    methode : 'produit::produitCompChangeStatut',
    id_as400 : $('[name=id_as400]').val(),
    id_as400_comp : id_as400
  })
  getListProduitComp()
}

const compDelete = id_as400 => {
  Swal.fire({
    icon : 'warn',
    title: l('js-page-produit-comp-delete'),
    showCancelButton: true,
    confirmButtonText: l('js-bouton-supprimer'),
    cancelButtonText: l('js-bouton-annuler')
  }).then( async (result) => {
    if (!result.isConfirmed) return
    await api({
      methode : 'produit::produitCompDelete',
      id_as400 : $('[name=id_as400]').val(),
      id_as400_comp : id_as400
    })
    getListProduitComp()
  });
}