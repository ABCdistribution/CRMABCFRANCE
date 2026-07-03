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
    window.open( _global.app_url+'Visites/'+$(this).attr('data-id') )
  })
})

let currentOffset = 0;
function loadTableVisite() {
  let table = $("#vTable"),
      tableWrapper = $("#vTable-wrapper"),
      searchField = $("#searchField"),
      dis = $("#disclaimer span.d02"),
      limit = parseInt( $("#nbResults").val() ),
      limitSpan = $("#disclaimer span.d01");
  loader(true,table);
  ajax({
    methode:'visite::searchBoard',
    str : searchField.val().trim(),
    limit : limit,
    offset : currentOffset
  },function() {
    table.find('tbody').html(decodeURIComponent(ajaxDatas.html))
    dis.text(ajaxDatas.count)
    let max = (currentOffset*limit)+limit+1;
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

  })

}
