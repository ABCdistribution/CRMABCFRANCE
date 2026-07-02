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
    $(document).on('click.vTable','#vTable tbody tr',function() {
      $(location).attr('href',_global.app_url+'VisitesCS/'+$(this).attr('data-id') )
    })
    $(document).on('change.datepicker','input[type=date]',function(){
      loadTableVisite();
    })
  
  
  
  
  
  
    let info = Cookies.get('visitSearchCS');
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
        dis = $("#disclaimer span.d02"),
        from = $("input[name=from]").val(),
        to = $("input[name=to]").val(),
        limit = parseInt( $("#nbResults").val() ),
        limitSpan = $("#disclaimer span.d01");
    loader(true,table);
    ajax({
      methode:'prospection::searchBoardVisitesCs',
      str : searchField.val().trim(),
      from : from,
      to : to,
      limit : limit,
      offset : currentOffset
    },function() {
  
      Cookies.set('visitSearchCS', JSON.stringify({
        str : searchField.val().trim(),
        limit : limit,
        offset : currentOffset,
        from : from,
        to : to
      }))
  
  
  
      table.find('tbody').html(decodeURIComponent(ajaxDatas.html))
      dis.text(ajaxDatas.count)
      let max = (currentOffset*limit)+limit+1;
      ajaxDatas.count = parseInt(ajaxDatas.count.replace(' ',''));
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
  