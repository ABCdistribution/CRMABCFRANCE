function uploadPlanning() {
  $("input[name=filePlanning]").click();
}
$(document).ready(function() {
  $(document).on('change.filePlanning','input[name=filePlanning]',function() {

    loader(true)
    let datas = new FormData($("#formUploadPlanning").get(0));
    datas.append( 'methode', 'planning::importFileFromUpload' );
    $("#formUploadPlanning").get(0).reset();
    $.ajax({
      url : _global.app_url+'async/',
      type : 'POST',
      dataType : 'json',
      data: datas,
      contentType: false,
      processData: false,
      success: function(response){
        ajaxDatas = response;
        if( ajaxDatas.err == true ) {
          loader(false)
          Swal.fire({
            icon: 'error',
            title: l('js-erreur')+'...',
            text: decodeURIComponent(ajaxDatas.errMsg),
          })
          return false;
        }

        loader(false)
        let t = ajaxDatas.total - ajaxDatas.errTotal;
        info(
          t+" "+l("js-lignes-importees")+" "+ajaxDatas.total+'<br/>' +
          ajaxDatas.errTotal + ' '+l("js-lignes-en-erreur")+'.<br/><br/>' +
          (ajaxDatas.errTotal>0? '<p style="font-weight:light;font-size:12px;">'+decodeURIComponent(ajaxDatas.errors)+'</p>':''));
      },
    });

  })
})

function truncatePlanning() {
  Swal.fire({
    title: l("js-modal-confirmez-vous"),
    text: l("js-planning-warning-database"),
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: l('js-bouton-supprimer'),
    cancelButtonText: l('js-bouton-annuler')
  }).then((result) => {
    if (result.isConfirmed) {
      ajax({methode:'planning::truncatePlanning'},function() {
        Swal.fire(
          '',
          l("js-planning-deleted-database"),
          'success'
        )
      })
    }
  })
}
$(function() {
  let d = $("#infoCSV");
  d.hide();
  let b = $("#btnUploadCSV");
  b.mouseenter(()=>{
    d.stop(true,true).slideDown()
  }).mouseleave(()=>{
    d.stop(true,true).slideUp()
  })
})



$(()=>{
  $(document).on('change.getPlanning','.getPlanning',function() {
    getPlanning();
  })
})


let currentIdRepr
function getPlanning() {
  let id = parseInt($("#planning_sel_id_repr").val()),
      from = $("[name=from]").val(),
      to = $("[name=to]").val();
  if( id < 1 ) return;
  currentIdRepr = id
  $("#pl-wrapper").empty();
  loader(true,$("#pl-wrapper"))
  ajax({methode:'planning::getPlanning', id_repr : id, from : from, to : to },function() {
    $("#pl-wrapper").empty();
    
    let row = $('<div class="row">');
    let col = $('<div class="col">');

    for( let i in ajaxDatas.planning ) {
      let el = ajaxDatas.planning[i];
      let wn = ajaxDatas.weeks[i];
      let tmp = i.split("-");
      let date = tmp[2]+"/"+tmp[1]+"/"+tmp[0];
      let dt = new Date(i),
          dn = dt.getDay(),
          dayName = [
              l("js-date-dimanche"),
              l("js-date-lundi"),
              l("js-date-mardi"),
              l("js-date-mercredi"),
              l("js-date-jeudi"),
              l("js-date-vendredi"),
              l("js-date-samedi")
          ];

      col.append('<p class="mt-3 text-primary">'+(Object.keys(el).length)+' '+l('js-planning-visites-prevues')+' <strong>'+dayName[dn]+' '+date+' ('+l('js-semaine')+' # '+wn+')</strong></p>');
      let ul = $('<ul class="list-group list-group-flush">');
      for( let j in el ) {
          let id_client = el[j][0],
              green = el[j][1],
              name = el[j][3],
              reason = ( el[j][2] != "" && green == 2 ? '<strong>'+l('js-visite-annulee')+'</strong> : '+el[j][2] : '' );
          let classes = "";
          if( green == 1 ) classes = "bg-success text-white"
          if( green == 2 ) classes = "bg-danger text-white"
          ul.append('<li class="list-group-item '+classes+'">'+name+' '+reason+'</li>')
      }
      col.append(ul)
    }

    row.append(col);
    col = $('<div class="col">');

    div = '<div class="card card-primary card-outline"><div class="card-body">';
    div += '<h5><span>'+l('js-planning-plannification')+'</span></h5>'+l('js-planning-actions-possibles');
    
    div += '<div class="list-group mt-5" id="list-plannification">';

    for( let i in ajaxDatas.plannification ) {
      let o = ajaxDatas.plannification[i]
      div += '<a href="#" class="list-group-item list-group-item-action delPlannification" data-id="'+o.id+'">';
      div += '<strong>'+o.enseigne+'</strong> <small> - <em>'+l('js-planning-plannification-creee-le')+' '+o.date_creation+'</em></small><br/>';
      div += l('js-planning-toutes-les')+' <strong>'+o.rec+'</strong> '+l('js-planning-toutes-semaines-les')+' <strong>'+o.days+'</strong> ('+l('js-planning-a-partir-de')+' '+o.start+')';
      div += '</a>';
    }

    div+= '</div></div></div>';
    col.html(div)

    row.append(col)
    $("#pl-wrapper").html(row);

    getMagasinNonPlannifie()
    
  })
}



const getMagasinNonPlannifie = async () => {
  let mags = await api({methode:"planning::getMagasinNonPlannifie",id_repr : currentIdRepr})

  let d = $("#list-plannification")
  if( ajaxDatas.count == 0 ) {
    d.after(`
      <h5><span>${l('js-planning-non-plannifies')}</span></h5>
      <p class="tc">Tous les magasins ont été planifiés</p>
    `)
  }
  else {
    let c = []
    c.push(`
    <h5><span>${l('js-planning-non-plannifies')}</span></h5>
    <table class="table" id="tNonPlanifie">
      <thead>
        <tr>  
          <th>Magasin</th>
          <th>Code</th>
          <th>Periodicité</th>
        </tr>
      </thead>
      <tbody>
    `)
    for( let i in ajaxDatas.clients ) {
      c.push(`
        <tr>
          <td>${ajaxDatas.clients[i].n}</td>
          <td>${ajaxDatas.clients[i].id_as400}</td>
          <td>${ajaxDatas.clients[i].p}</td>
        </tr>
      `)
    }
    c.push(`</tbody></table>`)
    d.after(c.join(''))

    let t = $("#tNonPlanifie")
    t.dataTable()
  }


}

$(()=>{
  $(document).on('click.delPlannification','.delPlannification', function() {
    let id = $(this).attr('data-id');
    deletePlannification(id)
  })
})

function deletePlannification(id) {
  Swal.fire({
    title: l('js-planning-action-planification'),
    showDenyButton: true,
    showCancelButton: true,
    confirmButtonText: l('js-bouton-rennouveler'),
    denyButtonText: l('js-bouton-supprimer'),
    cancelButtonText : l('js-bouton-annuler')
  }).then((result) => {
    let action = null;
    if (result.isConfirmed) {
        action = 'renouv'
    } else if (result.isDenied) {
        action = 'delete'
    }
    else action = false;
    if( !action ) return;

    ajax({methode:'planning::actionPlannification', id : id, action : action },function() {
      info(l('js-action-effectuee'))
      getPlanning();
    });

  })  
}