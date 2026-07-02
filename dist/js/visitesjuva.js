$(document).ready(function() {
  $(document).on('keyup.searchField','#searchField', function(e) {
    if( e.which == 13 || e.keyCode == 13 ) {
      currentOffset = 0;
      loadTableVisite();
    }
  })
  $(document).on('change.nbResults','#nbResults',function(){
    currentOffset = 0;
    loadTableVisite();
  })
  $(document).on('click.pagination','#vTable-wrapper .btn-group button',function() {
    currentOffset = $(this).attr('data-offset');
    loadTableVisite();
  })
  $(document).on('click.vTable','#vTable tbody tr',function(e) {
    //lien vers la page de la visite
    if ($(e.target).closest('.cmd-link').length > 0) {
        return;
      }
    window.open(_global.app_url+'VisitesJuva/'+$(this).attr('data-id') )
  })
  $(document).on('change.datepicker','input[type=date]',function(){
    loadTableVisite();
  })






  let info = Cookies.get('visitSearch');
  if( typeof info != undefined && info != undefined ) {
    info = JSON.parse(info);
    if( info.str ) $("#searchField").val(info.str)
    if( info.limit ) $("#nbResults").val(info.limit)
    if( info.from ) $("input[name=from]").val(info.from)
    if( info.to ) $("input[name=to]").val(info.to)
  }
  loadTableVisite();
})

let currentOffset = 0;
function loadTableVisite() {
  let table = $("#vTable"),
      tableWrapper = $("#vTable-wrapper"),
      searchField = $("#searchField"),
      v = ( searchField.length ? searchField.val().trim() : "" ),
      dis = $("#disclaimer span.d02"),
      from = $("input[name=from]").val(),
      to = $("input[name=to]").val(),
      limit = parseInt( $("#nbResults").val() ),
      limitSpan = $("#disclaimer span.d01");
  loader(true,table);
  ajax({
    methode:'visite::searchBoardJuva',
    from : from ?? "",
    to : to ?? "",
    limit : limit,
    offset : currentOffset,
    str : v,
  },function() {

    Cookies.set('visitSearch', JSON.stringify({
      str : v,
      limit : limit,
      offset : currentOffset,
      from : from,
      to : to
    }))



    //table.find('tbody').html(decodeURIComponent(ajaxDatas.html))
    let tbody = table.find('tbody')
    tbody.empty()
    for( let i in ajaxDatas.datas ) {
      let l = ajaxDatas.datas[i]
      let tr = $('<tr>');
      tr.attr('data-id',l[0])
      tr.append(`<td>${l[1]}</td>`)
      tr.append(`<td>${l[2]}</td>`)
      tr.append(`<td>${l[3]}</td>`)
      tr.append(`<td><small>${l[4]}</small></td>`)
      tr.append(`<td>${l[5]}</td>`)
      tr.append(`<td>${l[6]}</td>`)
      tr.append(`<td>${l[7]}</td>`)
      tr.append(`<td class="tc">${l[8] > 0 ? l[8]+ '<i class="fas fa-camera"></i>' : '<i class="fas fa-times text-danger"></i>'}</td>`)
      // tr.append(`<td class="tc">${l[9] ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times text-danger"></i>'}</td>`)
      tr.append(`<td class="tc">${l[10] ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times text-danger"></i>'}</td>`)
      // tr.append(`<td class="tc">${l[11] ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times text-danger"></i>'}</td>`)
      tr.append(`<td class="tc">${l[12] ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times text-danger"></i>'}</td>`)


      tbody.append(tr)
    }



    dis.text(ajaxDatas.count)
    let max = (currentOffset*limit)+limit+1;
    ajaxDatas.count = parseInt(ajaxDatas.count?.replace(' ',''));
    if( max > ajaxDatas.count ) max = ajaxDatas.count;
    limitSpan.text( (currentOffset*limit+1)+' à '+ max )

    /* Pagination */
    $("#vTable-wrapper .btn-group").remove();
    let div = $("<div id='pagination'>");
    div.html('<div class="btn-group" role="group">');

    let nb_pages = Math.floor(ajaxDatas.count/limit);
    let reste = ajaxDatas.count % limit;
    let maxPage = parseInt( reste > 0 ? nb_pages : nb_pages-1 );
    for( let i = 0; i <= maxPage ; i++ ) {
      if( i < parseInt(currentOffset) -3 || i > parseInt(currentOffset) + 3 ) {
        if( i != 0 && i != maxPage )
          continue;
      }
      let btn = $('<button type="button" class="btn btn-secondary">'+(i+1)+'</button>');
      if( i == currentOffset ) btn.addClass('selected')
      btn.attr('data-offset', i)
      div.find('.btn-group').append(btn);
    }
    table.after(div.html())
    $('[rel="tooltip"]').tooltip()

  })

}
