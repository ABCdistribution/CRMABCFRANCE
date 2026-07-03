$(document).ready(function() {

  $('#editor').trumbowyg({
    lang: 'fr'
  });

  $(document).on('click.btnNavCreate','#btnNavCreate',function(){
    let i = $(this),
        d1 = $("#wrapperListNews"),
        d2 = $("#wrapperCreateNews");
    if( d1.hasClass('show') ) {
      d1.addClass('hidden').removeClass('show');
      d2.removeClass('hidden').addClass('show');
      i.text('Retour');
    }
    else {
      d2.addClass('hidden').removeClass('show');
      d1.removeClass('hidden').addClass('show');
      i.text('Créer une news');
    }
  });

  let i = $("#photo");
      obj = i[0],
      img = $("#img")[0];
  obj.onchange = evt => {
    file = obj.files
    if (file) {
      if(file[0].size > 5242880 ) {
        Swal.fire( l("js-news-upload-error") )
        return false;
      }
      $("#img").removeClass('hidden').attr('src',URL.createObjectURL(file[0]));
      $(".photoPreview p").hide();
    }
  }

})

let file = null;
function saveNews() {
  let t = $("input[name=titre_news]").val();
  if( t.length < 5 || t.length > 300 ) {
    return Swal.fire({
      icon: 'error',
      title: l("js-news-title-error"),
      text:  l("js-news-title-error-text"),
    })
  }
  if( !file && $("input[name=id]").length == 0 ) {
    return Swal.fire({
      icon: 'error',
      title: l("js-news-photo-error"),
      text:  l("js-news-photo-error-text"),
    })
  }

  loader();

  // Envoi de la news
  let datas = new FormData($("#formCreateNews").get(0));
  datas.append( 'methode', 'news::create' );
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
          title: l('js-error')+'...',
          text: decodeURIComponent(ajaxDatas.errMsg),
        })
        return false;
      }
      loader(false)
      reload();
    },
  });



}


function news_publish( el, id ) {
  let tr = $(el).parents('tr:first'),
      state = tr.attr('data-state'),
      newState = ( state == 1 ? 0 : 1 );
  Swal.fire({
    title: state == 1 ? 'Annuler la publication ?' : 'Publier cette news ?',
    showDenyButton: false,
    showCancelButton: true,
    confirmButtonText: 'Confirmer',
    cancelButtonText: 'Annuler',
  }).then((result) => {
    if (result.isConfirmed) {
      loader();
      ajax( {methode:"news::togglePublish","id":id}, function() {
          let td = tr.find('td.pub');
          td.html( newState == 1 ? '<i class="fas fa-check green"></i>' : '<i class="fas fa-times"></i>');
          $(el).html( newState == 1 ? 'Annuler la publication' : 'Publier' );
          tr.attr('data-state', newState );
          Swal.fire({
            position: 'top-end',
            icon: 'success',
            title: newState ? 'La news a été publiée' : 'Publication retirée',
            showConfirmButton: false,
            timer: 1500
          })
      })
    }
  })
}

function news_delete( el, id ) {
  let tr = $(el).parents('tr:first');
  Swal.fire({
    icon : 'question',
    title: 'Supprimer définitivement cette news ?',
    showDenyButton: false,
    showCancelButton: true,
    confirmButtonText: 'Supprimer !',
    cancelButtonText: 'Annuler',
  }).then((result) => {
    if (result.isConfirmed) {
      loader();
      ajax( {methode:"news::deleteNews","id":id}, function() {
        tr.remove();
        Swal.fire({
          position: 'top-end',
          icon: 'info',
          title: 'La news a été supprimée',
          showConfirmButton: false,
          timer: 1500
        })
      });
    }
  });
}
