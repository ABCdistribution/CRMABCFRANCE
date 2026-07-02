let statsWrapper = null,
    statsMethode = null,
    statsTemplate = null;

$(document).ready(function() {
  statsWrapper = $("#statsRezWrapper");
  if( !statsWrapper.length ) return;
  statsMethode = statsWrapper.attr('data-get');
  statstemplate = $("#statstemplate")[0].content.cloneNode(true);

  stats_loader()
  setTimeout(function() {
    stats_get();
  },2000);

  $(document).on('change.changeStats','#changeStats',function() {
    let v = parseInt( $(this).val() );
    if( v > 0 )
      $(location).attr('href', _global.app_url+'Stats/'+v)
  })
})


let getForm = () => {
    let f = $("#formFilters"), obj = {};
    if( f.length == 0 )
        error( l("js-form-missing")+" #formFilters");
    else {
        let Arr = f.serializeArray();
        for( let i in Arr ) obj[Arr[i].name] = Arr[i].value
    }
    return obj;
}

let stats_loader = () => {
  statsWrapper.html('<div class="statsloader"><p>'+l("js-stats-loading")+'...</p></div>')
}
async function stats_get() {
  stats_loader()
  let form = getForm();
  form.methode = "stats::getStats";
  form.call = statsMethode;
  var formData = new FormData();
  for( let i in form )
    formData.append(i,form[i])
  let rep = await fetch( _global.app_url+'async/',{
    method : 'POST',
    body : formData
  }).then( rep => rep.json() )
  if( rep.errMsg )
    return error(decodeURIComponent(rep.errMsg));
  let datas = rep.datas;
  if( Object.keys(datas).length == 0 ) {
    statsWrapper.html('<p class="text-center text-secondary mt-5">'+l('js-stats-no-results')+'</p>');
    return;
  }
  eval(statsMethode+'( $(statstemplate), datas, rep )');
  hyperLinkCols();
}

function getMedalPosition( position ) {
  $pos = '<span class="pos">'+position+'<sup>ème</sup></span>';
  switch( parseInt(position) ) {
    case 1 : return $pos+' <i class="fas fa-medal gold"></i>';
    case 2 : return $pos+' <i class="fas fa-medal silver"></i>';
    case 3 : return $pos+' <i class="fas fa-medal bronze"></i>';
    default : return $pos;
  }
}

function getVisiteParPromoteurs( template, datas ) {
  console.dir(datas)
  let keys = [];
  let first = true;
  let count = 1;
  let top20 = { labels : [], datas : [] };
  let totalVisites = 0;
  for( let i in datas ) {
    let tr = $('<tr>');
    for( let j in datas[i] ) {
      if( first ) {
        let th = $('<th>');
        th.text(j)
        template.find('#rezTable thead tr:first').append(th)
      }
      let td = $('<td>');
      let content = datas[i][j];
      if( j == l("js-stats-classement")  ) content = getMedalPosition(content)
      if( j == l("js-stats-promoteur") && count < 20 ) top20.labels.push(content)
      if( j == l("js-stats-total-visite") && count < 20 ) {
        top20.datas.push(content)
      }
      if( j == l("js-stats-total-visite") ) totalVisites+=parseInt(content);
      td.html(content);
      tr.append(td);
    }
    if( first ) first = false;
    template.find('#rezTable tbody').append(tr);
    count++;
  }

  template.find('.statsTitle').html(l("js-stats-visites-promoteurs")+" (<strong>"+Object.keys(datas).length+"</strong> "+l("js-stats-visites-promoteurs-pour")+" <strong>"+totalVisites+"</strong> "+l("js-stats-visites-promoteurs-visites")+")");
  let a = '<a download="top_visite_promoteurs.xls" class="btn btn-link" href="#" onclick="return ExcellentExport.excel(this, \'rezTable\', \'Statistiques\');">';
  a+= '<i class="fas fa-cloud-download-alt"></i> Excel</a>';
  template.find('.statsTitle').append(a)

  statsWrapper.html(template);

}

function getAlertesCom( template, datas ) {  
  let keys = [];
  let first = true;
  for( let i in datas ) {
    let tr = $('<tr>');
    for( let j in datas[i] ) {
      if( first ) {
        let th = $('<th>');
        th.text(j)
        template.find('#rezTable thead tr:first').append(th)
      }
      let td = $('<td>');
      let content = datas[i][j];
      td.html(content);
      tr.append(td);
    }
    if( first ) first = false;
    template.find('#rezTable tbody').append(tr);
  }

  template.find('.statsTitle').html("Alertes Commerciales - (<strong>"+Object.keys(datas).length+"</strong> alertes)");
  let a = '<a download="commande_sans_visites.xls" class="btn btn-link" href="#" onclick="return ExcellentExport.excel(this, \'rezTable\', \'Statistiques\');">';
  a+= '<i class="fas fa-cloud-download-alt"></i> Excel</a>';
  template.find('.statsTitle').append(a)

  statsWrapper.html(template);

}










function getCommandesParPromoteurs( template, datas, rep ) {

  let keys = [];
  let first = true;
  let count = 1;
  let top20 = { labels : [], datas : [] };
  let totalCommandes = 0;
  for( let i in datas ) {
    let tr = $('<tr>');
    for( let j in datas[i] ) {
      if( first ) {
        let th = $('<th>');
        th.text(j)
        template.find('#rezTable thead tr:first').append(th)
      }
      let td = $('<td>');
      let content = datas[i][j];
      td.html(content);
      if( td.find('.title').length == 1 ) {
        
        if( td.find('.t2').length == 1 ) {
          tr.addClass('bg-success text-white').css('font-size','18px')
        }
        else {
          tr.addClass('bg-dark text-white').css('font-size','18px')
        }
      }
      else 
        tr.find('td:first').css('padding-left','25px')
      tr.append(td);
    }
    if( first ) first = false;
    template.find('#rezTable tbody').append(tr);
    count++;
  }

  template.find('.statsTitle').html("<strong>"+rep.users+"</strong> "+l('js-stats-visites-promoteurs-pour')+" <strong>"+rep.commandes+"</strong> "+l('js-stats-visites-cmd')+"");
  let a = '<a download="top_commandes_promoteurs.xls" class="btn btn-link" href="#" onclick="return ExcellentExport.excel(this, \'rezTable\', \'Statistiques\');">';
  a+= '<i class="fas fa-cloud-download-alt"></i> Excel</a>';
  template.find('.statsTitle').append(a)

  statsWrapper.html(template);

}




function getCommandesVisitesSecteur( template, datas ) {

  let keys = [];
  let first = true,
      graphDatas = {
        labels : [],
        datas : []
      }

  for( let i in datas ) {
    let tr = $('<tr>');
    for( let j in datas[i] ) {
      if( first ) {
        let th = $('<th>');
        th.text(j)
        template.find('#rezTable thead tr:first').append(th)
      }
      let td = $('<td>');
      let content = datas[i][j];
      if( j == "Secteur" && datas[i].secteur != "Total" ) graphDatas.labels.push(content)
      if( j == "total" && datas[i].secteur != "Total" ) graphDatas.datas.push(content)
      if( j == "total" ) content = Intl.NumberFormat('fr-FR').format(content)+' €';
      td.html(content);
      tr.append(td);
    }
    if( first ) first = false;
    template.find('#rezTable tbody').append(tr);
  }

  template
    .find('.statsTitle')
    .html(l('js-stats-secteur')+"");
  let a = '<a download="commandes_visites_secteur.xls" class="btn btn-link" href="#" ';
  a+= ' onclick="return ExcellentExport.excel(this, \'rezTable\', \'Statistiques\');">';
  a+= '<i class="fas fa-cloud-download-alt"></i> Excel</a>';
  //template.find('#rezTable').removeClass('table-condensed')
  template.find('.statsTitle').append(a)

  statsWrapper.html(template);

  // Chart
  statsWrapper.find('h2.chartsTitle').after('<div class="row"><div class="col"><canvas id="graph1"></canvas></div><div class="col"><canvas id="graph2"></canvas></div></div>');
  const myChart1 = new Chart(
   $("#statsRezWrapper #graph1").get(0),
   {
     type: 'polarArea',
     data: {
       labels: graphDatas.labels,
       datasets: [{
         label: l('js-stats-secteur'),
         backgroundColor: [
          "rgba(144, 202, 249, 0.5)",
          "rgba(159, 168, 218, 0.5)",
          "rgba(251, 140, 0, 0.5)"
        ],
         borderColor: [
          '#2196F3',
          '#3F51B5',
          '#FFCC80'
        ],
         data: graphDatas.datas,
         scale : 1,
         scaleSteps: 1,
         borderWidth: 0,
         borderRadius: 0,
       }]
     },
     options : {
       responsive: false,
       maintainAspectRatio: false,
     }
  });

  // Chart
  const myChart2 = new Chart(
   $("#statsRezWrapper #graph2").get(0),
   {
     type: 'bar',
     data: {
       labels: graphDatas.labels,
       datasets: [{
         label: l('js-stats-secteur'),
         backgroundColor: ["rgba(144, 202, 249, 0.5)","rgba(159, 168, 218, 0.5)","rgba(251, 140, 0, 0.5)"],
         borderColor: ['#2196F3','#3F51B5','#FFCC80'],
         data: graphDatas.datas,
         scale : 1,
         scaleSteps: 1,
         borderWidth: 3,
         borderRadius: 0,
       }]
     },
     options : {
       responsive: false,
       maintainAspectRatio: false,
     }
  });
}


function getFrancoNonAtteints( template, datas ) {
  let keys = [];
  let first = true;
  for( let i in datas ) {
    let tr = $('<tr>');
    for( let j in datas[i] ) {
      if( first ) {
        let th = $('<th>');
        th.text(j)
        template.find('#rezTable thead tr:first').append(th)
      }
      let td = $('<td>');
      let content = datas[i][j];
      if( j == l("js-stats-valeurs") ) content = Intl.NumberFormat('fr-FR').format(parseInt(content))+' €'
      td.html(content);
      tr.append(td);
    }
    if( first ) first = false;
    template.find('#rezTable tbody').append(tr);
  }

  template.find('.statsTitle').html(l("stats-franco")+" - (<strong>"+Object.keys(datas).length+"</strong> "+l('js-stats-visites-cmd')+")");
  let a = '<a download="franco_non_attenteins.xls" class="btn btn-link" href="#" onclick="return ExcellentExport.excel(this, \'rezTable\', \'Statistiques\');">';
  a+= '<i class="fas fa-cloud-download-alt"></i> Excel</a>';
  template.find('.statsTitle').append(a)

  statsWrapper.html(template);
}


function getCommandesSansVisites( template, datas ) {
  let keys = [];
  let first = true;
  for( let i in datas ) {
    let tr = $('<tr>');
    for( let j in datas[i] ) {
      if( first ) {
        let th = $('<th>');
        th.text(j)
        template.find('#rezTable thead tr:first').append(th)
      }
      let td = $('<td>');
      let content = datas[i][j];
      if( j == l("js-stats-valeurs") ) content = Intl.NumberFormat('fr-FR').format(parseInt(content))+' €'
      td.html(content);
      tr.append(td);
    }
    if( first ) first = false;
    template.find('#rezTable tbody').append(tr);
  }

  template.find('.statsTitle').html(l("js-stats-cmd-ss-visites")+" - (<strong>"+Object.keys(datas).length+"</strong> "+l('js-stats-visites-cmd')+")");
  let a = '<a download="commande_sans_visites.xls" class="btn btn-link" href="#" onclick="return ExcellentExport.excel(this, \'rezTable\', \'Statistiques\');">';
  a+= '<i class="fas fa-cloud-download-alt"></i> Excel</a>';
  template.find('.statsTitle').append(a)

  statsWrapper.html(template);
}


function getVisistesSansCommandes( template, datas ) {
  let keys = [];
  let first = true;
  for( let i in datas ) {
    let tr = $('<tr>');
    for( let j in datas[i] ) {
      if( first ) {
        let th = $('<th>');
        th.text(j)
        template.find('#rezTable thead tr:first').append(th)
      }
      let td = $('<td>');
      let content = datas[i][j];
      if( j == "valeurs" ) content = Intl.NumberFormat('fr-FR').format(parseInt(content))+' €'
      td.html(content);
      tr.append(td);
    }
    if( first ) first = false;
    template.find('#rezTable tbody').append(tr);
  }

  template.find('.statsTitle').html(l("js-stats-visites-ss-cmd")+" - (<strong>"+Object.keys(datas).length+"</strong> "+l("js-stats-visites-ss-cmd-visites")+")");
  let a = '<a download="visites_sans_commandes.xls" class="btn btn-link" href="#" onclick="return ExcellentExport.excel(this, \'rezTable\', \'Statistiques\');">';
  a+= '<i class="fas fa-cloud-download-alt"></i> Excel</a>';
  template.find('.statsTitle').append(a)

  statsWrapper.html(template);

  $("#rezTable tbody tr").off().on('click',function() {
    window.open('/Visites/'+ $(this).find('td:first').text())
  })
}


function getPromosVisites( template, datas ) {
  let keys = [];
  let first = true;
  for( let i in datas ) {
    let tr = $('<tr>');
    for( let j in datas[i] ) {
      if( first ) {
        let th = $('<th>');
        th.text(j)
        template.find('#rezTable thead tr:first').append(th)
      }
      let td = $('<td>');
      let content = datas[i][j];
      td.html(content);
      tr.append(td);
    }
    if( first ) first = false;
    template.find('#rezTable tbody').append(tr);
  }
  let libelle = l("js-stats-op-toutes");
  if( $("[name=promo] option:selected").val() != "" ) libelle = $("[name=promo] option:selected").text()
  template.find('.statsTitle').html(libelle+" - "+Object.keys(datas).length);
  let a = '<a download="op_visites.xls" class="btn btn-link" href="#" onclick="return ExcellentExport.excel(this, \'rezTable\', \'Statistiques\');">';
  a+= '<i class="fas fa-cloud-download-alt"></i> Excel</a>';
  template.find('.statsTitle').append(a)

  statsWrapper.html(template);
}


/* DN */

$(document).ready(function() {
  $("[name=type]").change(function() {
    let v = $(this).val();
    $("#sel_marque").addClass('hidden').find('select option:selected').prop('selected',false);
    $("#sel_gamme").addClass('hidden').find('select option:selected').prop('selected',false);
    if( v == "" ) return;
    ajax({
      methode : 'stats::getDnMarques',
      type : v
    },function() {
      let s = $("#sel_marque select");
      s.html('<option value="">-- Choisir--</option>');
      for( let i in ajaxDatas.datas )
        s.append('<option value="'+encodeURIComponent(ajaxDatas.datas[i])+'">'+ajaxDatas.datas[i]+'</option>');
      $("#sel_marque").removeClass('hidden')
    });
  }).trigger('change');

  let post_marque = $("[name=post_marque]");
  if( post_marque.val() != "" ) {
    setTimeout(function() {
      $("[name=marque]").find('[value="'+post_marque.val()+'"]').prop('selected',true)
      $("[name=marque]").trigger('change')
      let post_gamme = $("[name=post_gamme]");
      if( post_gamme.val() != "" ) {
        setTimeout(function() {
          $("[name=gamme]").find('[value="'+post_gamme.val()+'"]').prop('selected',true)
          $("[name=gamme]").trigger('change')
        },500)
      }
    },500)
  }

  $("[name=marque]").change(function() {
    let v = $(this).val();
    if( v == "" ) {
      $("#sel_gamme").addClass('hidden').find('select option:selected').prop('selected',false);
      return;
    }
    ajax({
      methode : 'stats::getDnGamme',
      marque : v
    },function() {
      let s = $("#sel_gamme select");
      s.html('<option value="">-- Choisir--</option>');
      for( let i in ajaxDatas.datas )
        s.append('<option value="'+encodeURIComponent(ajaxDatas.datas[i])+'">'+ajaxDatas.datas[i]+'</option>');
      $("#sel_gamme").removeClass('hidden')
    });
  })
})


function getDN( template, datas ) {
  let keys = [];
  let first = true;
  for( let i in datas ) {
    let tr = $('<tr>');
    for( let j in datas[i] ) {
      if( first ) {
        let th = $('<th>');
        th.text(j)
        template.find('#rezTable thead tr:first').append(th)
      }
      let td = $('<td>');
      let content = datas[i][j];
      if( j == l("js-dn-metrage") ) content = Intl.NumberFormat('fr-FR').format(content)+' m'
      td.html(content);
      tr.append(td);
    }
    if( first ) first = false;
    template.find('#rezTable tbody').append(tr);
  }
  template.find('.statsTitle').html(l("js-dn")+" - "+Object.keys(datas).length);
  let a = '<a download="dn.xls" class="btn btn-link" href="#" onclick="return ExcellentExport.excel(this, \'rezTable\', \'Statistiques\');">';
  a+= '<i class="fas fa-cloud-download-alt"></i> Excel</a>';
  template.find('.statsTitle').append(a)

  statsWrapper.html(template);
}



function getDNPresente( template, datas ) {
  let keys = [];
  let first = true;
  for( let i in datas ) {
    let tr = $('<tr>');
    for( let j in datas[i] ) {
      if( first ) {
        let th = $('<th>');
        th.text(j)
        template.find('#rezTable thead tr:first').append(th)
      }
      let td = $('<td>');
      let content = datas[i][j];
      if( j == "metrage" ) content = Intl.NumberFormat('fr-FR').format(content)+' m'
      td.html(content);
      tr.append(td);
    }
    if( first ) first = false;
    template.find('#rezTable tbody').append(tr);
  }
  template.find('.statsTitle').html("DN présente - "+Object.keys(datas).length+" résultats");
  let a = '<a download="dn.xls" class="btn btn-link" href="#" onclick="return ExcellentExport.excel(this, \'rezTable\', \'Statistiques\');">';
  a+= '<i class="fas fa-cloud-download-alt"></i> Excel</a>';
  template.find('.statsTitle').append(a)

  statsWrapper.html(template);
}


function hyperLinkCols() {
  let t = $("#statsRezWrapper table");
  if( t.length != 1 ) return;

  let indexMagasin = -1,
      indexPromoteur = -1,
      indexVisite = -1,
      indexTotalVisites = -1,
      indexTotalCmd = -1,
      indexCa = -1
  t.find('thead tr th').each(function() {
    let v = $(this).text().toLowerCase().trim();
    if( v == "magasins" ) indexMagasin = $(this).index();
    if( v == "promoteur" ) indexPromoteur = $(this).index();
    if( v == "ca" ) indexCa = $(this).index();
    if( v == "total visites" ) indexTotalVisites = $(this).index();
    if( v == "visite" ) indexVisite = $(this).index();
    if( v == "visites" ) indexTotalVisites = $(this).index();
    if( v == "cde crm"  ) indexTotalCmd = $(this).index();
  })
  if( indexMagasin > -1 ) {
    t.find('tbody tr').each(function() {
      if( $(this).find('.title').length == 0 )
        $(this).find('td').eq(indexMagasin).addClass('linkable linkableMagasin')
    })
  }
  if( indexPromoteur > -1 ) {
    t.find('tbody tr').each(function() {
      if( !$(this).find('td').eq(indexPromoteur).html().includes('strong') )
        if( $(this).find('.title').length == 0 )
          $(this).find('td').eq(indexPromoteur).addClass('linkable linkablePromoteur')
    })
  }  
  if( indexVisite > -1 ) {
    t.find('tbody tr').each(function() {
      if( $(this).find('.title').length == 0 )
        $(this).find('td').eq(indexVisite).addClass('linkable linkableVisiteId')
    })
  }  
  if( indexTotalVisites > -1 ) {
    t.find('tbody tr').each(function() {
      if( $(this).find('.title').length == 0 )
        $(this).find('td').eq(indexTotalVisites).addClass('linkable linkableVisite')
    })
  }  
  if( indexTotalCmd > -1 ) {
    t.find('tbody tr').each(function() {
      if( $(this).find('.title').length == 0 )
        $(this).find('td').eq(indexTotalCmd).addClass('linkable linkableCmd')
    })
  }   
  if( indexCa > -1 ) {
    t.find('tbody tr').each(function() {
      if( !$(this).find('td').eq(indexCa).html().includes('strong') )
        if( $(this).find('.title').length == 0 )
          $(this).find('td').eq(indexCa).addClass('linkable linkableCmd')
    })
  }  
}


$(document).ready(function() {
  $(document).on('click.linkableMagasin','.linkableMagasin',function() {
    window.open(_global.app_url+'Magasins/'+encodeURIComponent( $(this).text().trim().toLowerCase() ) )
  })
  $(document).on('click.linkablePromoteur','.linkablePromoteur',function() {
    let type = $("#changeStats").val() == 1 ? 'Visites' : 'Commandes';
    p = {
      user : $(this).text().trim().toLowerCase(),
      from : $("input[name=from]").val(),
      to : $("input[name=to]").val(),
    }
    window.open(_global.app_url+type+'/'+btoa(JSON.stringify(p)) )
  })  

  $(document).on('click.linkableVisiteId','.linkableVisiteId',function() {
    window.open(_global.app_url+'Visites/'+$(this).text().trim() )
  })  

  $(document).on('click.linkableVisite','.linkableVisite',function() {
    let user = $(this).parents('tr:first').find('td:first').text();
    p = {
      user : user,
      from : $("input[name=from]").val(),
      to : $("input[name=to]").val(),
    }
    window.open(_global.app_url+'Visites/'+btoa(JSON.stringify(p)) )
  })  

  $(document).on('click.linkableCmd','.linkableCmd',function() {
    let user = $(this).parents('tr:first').find('td:first').text();
    p = {
      user : user,
      from : $("input[name=from]").val(),
      to : $("input[name=to]").val(),
    }
    window.open(_global.app_url+'Commandes/'+btoa(JSON.stringify(p)) )
  })  


})





function getVisiteFaites( template, datas, rep ) {

  let keys = [];
  let first = true;
  let count = 1;
  let top20 = { labels : [], datas : [] };
  let totalCommandes = 0;
  for( let i in datas ) {
    let tr = $('<tr>');
    for( let j in datas[i] ) {
      if( first ) {
        let th = $('<th>');
        th.text(j)
        template.find('#rezTable thead tr:first').append(th)
      }
      let td = $('<td>');
      let content = datas[i][j];
      td.html(content);
      tr.append(td);
    }
    if( first ) first = false;
    template.find('#rezTable tbody').append(tr);
    count++;
  }

  template.find('.statsTitle').html(l("js-stats-visites-faites")+" (<strong>"+rep.u+"</strong> "+l("js-stats-visites-faites-promoteurs")+"<strong>");
  let a = '<a download="top_commandes_promoteurs.xls" class="btn btn-link" href="#" onclick="return ExcellentExport.excel(this, \'rezTable\', \'Statistiques\');">';
  a+= '<i class="fas fa-cloud-download-alt"></i> Excel</a>';
  template.find('.statsTitle').append(a)

  statsWrapper.html(template);

}




function getTempsPromoteurs( template, datas ) {
  let keys = [];
  let first = true;
  for( let i in datas ) {
    let tr = $('<tr>');
    for( let j in datas[i] ) {
      if( first ) {
        let th = $('<th>');
        th.text(j)
        template.find('#rezTable thead tr:first').append(th)
      }
      let td = $('<td>');
      td.attr('data-order', datas[i][j].replace(' ','').replace('h', '') )
      let content = datas[i][j];
      if( j == "valeurs" ) content = Intl.NumberFormat('fr-FR').format(parseInt(content))+' €'
      td.html(content);
      tr.append(td);
    }
    if( first ) first = false;
    template.find('#rezTable tbody').append(tr);
  }

  template.find('.statsTitle').html(l("js-stats-avancee-name")+" - (<strong>"+Object.keys(datas).length+"</strong> "+l("js-stats-avancee-promoteur")+")");
  let a = '<a download="temps_par_promoteur.xls" class="btn btn-link" href="#" onclick="return ExcellentExport.excel(this, \'rezTable\', \'Statistiques\');">';
  a+= '<i class="fas fa-cloud-download-alt"></i> Excel</a>';
  template.find('.statsTitle').append(a)

  template.find('.statsTitle').after(`
    <div class="legende">${l("js-stats-avancee-legende")}</div>
  `)

  statsWrapper.html(template);


  $("#rezTable").dataTable({
    bPaginate : false
  });
}




function getProduitsRemplacements( template, datas ) {
  let keys = [];
  let first = true;
  for( let i in datas ) {
    let tr = $('<tr>');
    for( let j in datas[i] ) {
      if( first ) {
        let th = $('<th>');
        th.text(j)
        template.find('#rezTable thead tr:first').append(th)
      }
      let td = $('<td>');
      let content = datas[i][j];
      if( j == l("js-stats-valeurs") ) content = Intl.NumberFormat('fr-FR').format(parseInt(content))+' €'
      td.html(content);
      tr.append(td);
    }
    if( first ) first = false;
    template.find('#rezTable tbody').append(tr);
  }

  template.find('.statsTitle').html(l("stat-type-10"));
  let a = '<a download="remplacement.xls" class="btn btn-link" href="#" onclick="return ExcellentExport.excel(this, \'rezTable\', \'Statistiques\');">';
  a+= '<i class="fas fa-cloud-download-alt"></i> Excel</a>';
  template.find('.statsTitle').append(a)

  statsWrapper.html(template);
}