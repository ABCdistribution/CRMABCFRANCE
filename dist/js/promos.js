let pTableDatas;
$(document).ready(function() {
  //loadOpTable();

  /*
  $(document).on('click.opTable', '#opTable tbody tr', function () {
      var id = $(this).find('td:first').text();
      editPromo( id );
  });
  */
  if( $("#listOp").length == 1 ) {
    $("#listOp").sortable({
      containment : '#listOpWrapper',
      cursor: 'move',
      stop : function( event, ui ) {
        reorderOp()
      }
    })


    ajax({methode:"promo::getJson"},function() {
      pTableDatas = ajaxDatas
    })
    $(document).on('click.editPromo','button.edit',function() {
      editPromo($(this).parents('li:first').attr('data-id'))
    })
    $(document).on('click.delPromo','.delPromo',function() {
      delPromo($(this).attr('data-id'))
    })

  }

})
function createOp() {
  let f = $("#newPromo");
  let full = true;
  f.find('input[type=text]').each(function() {
    if( $(this).val() == "" ) full = false;
  });
  if( !full ) return error( l("js-promos-warning-champ-vide"));
  ajax("methode=promo::new&"+f.serialize(),function() {
    info( l("js-promos-creation"));
    f.get(0).reset();
    loadOpTable();
  })
}

async function editPromo( id ) {

  let html = $("#newPromo").clone();
  html.attr('id','formEdit');
  html.append('<a href="#" class="btn btn-warning btn-xs float-right delPromo" data-id="'+id+'"><i class="far fa-trash-alt"></i> '+l("js-promos-supprimer")+'</a>')

  let obj = null;
  for( let i in pTableDatas ) {
    if( pTableDatas[i].id == id )
      obj = pTableDatas[i]
  }
  if( !obj || obj == null ) {
    return error(l('js-erreur'));
  }
  console.log(obj)
  html.find('input[name=id_as400]').val(obj.id_as400);
  html.find('input[name=libelle]').val(obj.libelle);
  html.find('select[name=actif]').find('option[value='+parseInt(obj.actif)+']').attr('selected','selected');





  const { value: formValues } = await Swal.fire({
    title: l('js-promos-edit'),
    html: html,
    allowOutsideClick : false,
    didOpen: () => {
      $("#formEdit").on('change','input,select',function() {
        ajax({
          methode:"promo::editPromo",
          id : id,
          field : $(this).attr('name'),
          value : $(this).val()
        },() => {

        })
      })
    },
    focusConfirm: false,
    preConfirm: (datas) => {
      return true
    },
    confirmButtonText: l('js-bouton-enregistrer')
  })

  if (formValues) {
    console.log(formValues);

    toast( l('js-modifications-sauvees'))
    reload()
  }
}

function reorderOp( silent = false) {
  let ul = $("#listOp");
  let order = [];
  ul.find('li').each(function() {
    order.push($(this).attr('data-id'))
  })
  ajax({methode:"promo::order",list : order.join('-')},function() {
    if( !silent ) toast(  l("js-promos-ordre-sauvegarde") )
  })
}
function delPromo( id ) {
  Swal.fire({
    title: l("js-promos-delete") ,
    icon : 'warning',
    showCancelButton: true,
    cancelButtonText : l("js-bouton-annuler"),
    confirmButtonText: l("js-bouton-supprimer"),
  }).then((result) => {
    if (result.isConfirmed) {
      ajax({methode:'promo::delPromo',id:id},function() {
          $("#listOp li[data-id="+id+"]").remove();
          reorderOp(true);
          Swal.fire( l("js-promos-deleted"), '', 'success')
      })
    }
  })
}
