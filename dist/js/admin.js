function injectRef() {
  Swal.fire({
    title: l('js-admin-import-ref'),
    showCancelButton: true,
    confirmButtonText: l('js-admin-import-btn'),
    backdrop :true,
    allowOutsideClick : false,
  }).then((result) => {
    if (result.isConfirmed) {
      loader()
      ajax({methode:"importAS400::doAjaxImport"},function() {
        let str = decodeURIComponent(ajaxDatas.msg);
        Swal.fire({
          title: l('js-admin-import-termine'),
          icon: 'info',
          html: '<samp>'+str+'</samp>',
          backdrop : true,
          allowOutsideClick : false,
          showCancelButton: false,
          confirmButtonText: l('js-reload'),
        }).then((result) => {
          if (result.isConfirmed) {
            reload();
          }
        });
      })
    }
  })
}


$(document).ready(function() {
  $(document).on('click.adminPage','#adminPage .card-header', function() {
    if( $(this).hasClass('noFullSize') ) return;
    let c = $(this).parents('.card:first');
    if( c.hasClass('fullsize') ) c.removeClass('fullsize');
    else c.addClass('fullsize');
  });
  $(document).on('change.select_mail_alertes_com,select[name=select_mail_alertes_com]', function() {
    let select = $("[name=select_mail_alertes_com]");
    let user_id = select.val();
    ajax({methode:"admin::addMailAlerteCom",user_id:user_id},function() {
      select.html(decodeURIComponent(ajaxDatas.html_select));
      $(".tab-mail-alerte-com tbody").html(decodeURIComponent(ajaxDatas.html_tab));
    })
  });
})

function deleteMailAlerteCom(user_id){
  ajax({
    methode:"admin::deleteMailAlerteCom",
    id : user_id
  },function() {
    $(".userMail"+user_id).remove();
    $("[name=select_mail_alertes_com]").html(decodeURIComponent(ajaxDatas.html_select))
  });
}

function createProfil() {
  let v = $("input[name=libelle_new_profil]").val();
  if( v.length < 5 || v.length > 80 ) {
      info( l('js-admin-profile-error'));
      return;
  }
  ajax({methode:"securite::newProfil",v:v},function() {
    reload();
  })

}
$(document).ready(function() {
  $(document).on('click.selectProfile','#selectProfile a',function() {
    selectProfile($(this))
  })
})

let idSelectedProfile = null,
    isSelectedDefault = null;
function selectProfile(el) {
  let id = el.attr('data-id'),
      name = decodeURIComponent(el.attr('data-name')),
      defaut = el.attr('data-defaut'),
      homepage = el.attr('data-homepage'),
      d = $("#editProfile .wrapperSelectProfile");
  idSelectedProfile = id;
  isSelectedDefault = defaut == 1;
  $("#editProfile .disclaimerEditProfile").remove();
  d.removeClass('hidden').find('.profileName').text(name)
  d.find('input[name=libelle_edit_profil]').val(name)
  d.find('input[name=homepage_edit_profil]').val(homepage)
  d.find('select').find('option').each(function() {$(this).removeAttr('selected')})
  d.find('select').find('option[value='+defaut+']').attr('selected','selected')


  let d2 = $("#editDroitsProfile .wrapperSelectProfile");
  d2.removeClass('hidden')
  $("#titleEditDroits").text(l('js-admin-profile-droits')+" : "+name)
  $("#editDroitsProfile .disclaimerEditProfile").remove();

  // Récupération des droits
  ajax({
    methode:"securite::getDroitsProfile",
    id_profile : idSelectedProfile
  },function() {
    let d = $("#accordionDroits");
    d.find('input[type=checkbox]').each(function() {
      let el = $(this);
      el.prop( "checked", false );
      if( ajaxDatas.indexOf(el.val()) != -1 )
        el.prop( "checked", true );
    })
  });



}
function editProfil() {
  let libelle = $("input[name=libelle_edit_profil]").val(),
      defaut = $("select[name=defaut]").val(),
      homepage = $("input[name=homepage_edit_profil]").val()
  ajax({
    methode:"securite::editProfile",
    id : idSelectedProfile,
    libelle : libelle,
    defaut : defaut,
    homepage : homepage
  },function() {
    reload();
  });
}
function deleteProfil() {
  if( isSelectedDefault ) {
    info( l('js-admin-profile-delete-error') );
    return;
  }
  Swal.fire({
    title: l('js-admin-profile-delete-confirm'),
    text : l('js-admin-profile-delete-text'),
    icon : 'error',
    showCancelButton: true,
    confirmButtonText: l('js-bouton-supprimer'),
    cancelButtonText: l('js-bouton-annuler'),
  }).then((result) => {
    /* Read more about isConfirmed, isDenied below */
    if (result.isConfirmed) {
      ajax({
        methode:"securite::deleteProfile",
        id : idSelectedProfile
      },function() {
        reload();
      });
    }
  })
}


$(document).ready(function() {
  $(document).on('change.accordionDroits','#accordionDroits input',function() {
    let i = $(this),
        state = i.is(':checked');
    ajax({
      methode:"securite::changeDroit",
      id_profile : idSelectedProfile,
      id_droit : i.val(),
      state : state ? 1 : 0
    }, () => {});
  })
})


function calcCA() {
  let box = $("#calcCa"),
      id = $("#id_repr").val();
  if( id < 1 ) {
    return;
  }
  box.find('.infoCa').addClass('hidden');
  loader(true,box)
  ajax({methode:'user::calcCA',id:id},function() {
    let d = ajaxDatas;
    box.find('.infoCa b').text(d.user)
    box.find('.infoCa span').text(d.date)
    box.find('.infoCa h4').text(d.ca+'€')
    box.find('.infoCa em').text(d.count)
    box.find('.infoCa').removeClass('hidden');
  })
}

function regenerateCMD() {
  let id = parseInt($("#id_cmd").val().trim());
  if( id < 1 ) {
    return;
  }
  ajax({methode:'commande::regenerateCmdFic',id:id},function() {
    info( l("js-admin-regen-valid"));
  })
}

let liveLogsInterval = null, logTable = null;
function initLiveLogs() {
  logTable = $("#logsTable");
  liveLogsInterval = setInterval(()=>{
    getLastLogs();
  },2000);
}
function getLastLogs() {
  let id = logTable.find('tbody tr:first').attr('data-id');
  if( parseInt(id) < 1 ) return;
  ajax({
    methode : 'core::getLastLog',
    id : id
  },function() {
    if( !ajaxDatas.hasOwnProperty('logs') ) return;
    let datas = ajaxDatas.logs;
    for( let i in datas ) {
      let line = datas[i]
      let tr = $('<tr>');
      tr.attr('data-id', line.id)
      if( line.e == 1 ) {
        tr.addClass('bg-danger');
        traddClass('text-white')
      }
      tr.append('<td>'+line.d+'</td>');
      tr.append('<td>'+line.u+'</td>');
      tr.append('<td>'+line.l+'</td>');
      logTable.prepend(tr)
    }
  })
}


function readlog(e) {
  ajax({
    methode : 'core::readLog',
    name : $(e).attr('data-name')
  },function() {
    let datas = decodeURIComponent(ajaxDatas.log);
    //$(".logReader").JSONView(datas,{ collapsed: true });
    let div = $("<div>");
    div.html(decodeURIComponent(ajaxDatas.html));;
    div.find('.jsonElement').JSONView(datas,{ collapsed: true });
    $(".logReader").html(div);
  });
}



const uploadObj = () => {
  Swal.fire({
    title: l('js-admin-objectifs'),
    html : `

  ${l('js-admin-objectifs-texts')}
    <br/><br/>
  <div id="errorInjectobj" 
    style="display:none;max-height:150px; overflow: auto; margin: 15px 0; text-align:left;background: #eee; color:red;font-size:11px;"
  ></div>
  <br/><br/>

  <input type="file" id="fileObjectif"/>
  <button class="btn btn-primary" id="retryFileObjectif" onclick="redoImportObj()" style="display:none">
  <i class="fas fa-redo"></i>
  </button>
  `,
    showCancelButton: true,
    showConfirmButton : false,
    cancelButtonText: l('js-bouton-annuler'),
    allowOutsideClick: false,
    allowEscapeKey: false,
  })  
}

$(() => {
  $(document).on('change.fileObjectif','#fileObjectif',function() {
    updateObj( $(this) )
  })
})
const updateObj = el => {
  const file = $(el).get(0).files[0];
  const reader = new FileReader();
  let csv = [], errors = []
  reader.onload = function(e) {
    const text = e.target.result;
    const lines = text.split("\r\n");
    lines.forEach( (line,index) => {
      if( line == "" ) return;
      let li = line.split(';');
      if( li.length != 3 ) {
        errors.push( l('js-admin-objectifs-error-2') + (index + 1) )
        return;
      }
      
      if( !isValidDate(li[0]) ) {
        errors.push( l('js-admin-objectifs-error-1') + ' ' + (index + 1) +' ( : '+li[0]+')')
        return;
      }
      csv.push(li)
      
    });

    if( errors.length > 0 || csv.length == 0 ) {
      $("#errorInjectobj").show().html(errors.join('<br/>'))
      $("#fileObjectif").hide()
      $("#retryFileObjectif").show()
    }
    else {
      
      $(".swal2-html-container").html( l("js-admin-objectifs-loading") )
      $(".swal2-actions").hide();

      ajax({
        methode : 'admin::injectObjectif',
        csv : lines 
      },function() {
        info( l("js-admin-objectifs-success") )
      });

    }
  };
  reader.readAsText(file);
}

function isValidDate(dateString="") {
  if( dateString == "" || typeof dateString == "undefined" ) return false;
  var regEx = /^\d{4}-\d{2}-\d{2}$/;
  return dateString.match(regEx) != null;
}

const redoImportObj = () => {
  $("#retryFileObjectif").hide();
  $("#errorInjectobj").empty().hide();
  $("#fileObjectif").val('').show().click();
}
