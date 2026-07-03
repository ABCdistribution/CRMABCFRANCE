const gNM = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre']
function wip() {
	Swal.fire({
	  position: 'top-end',
	  icon: 'warning',
	  title: 'En cours de développement...',
	  showConfirmButton: false,
	  timer: 1500
	})
	return false;
}

$(()=>{
	$("#navigation #btnMenuMobile").click(()=>{
		let a = $("#nav-menu"), b = $("#page");
		if( b.is(':visible') ) {
			a.show(); b.hide();
		}
		else {
			b.show(); a.hide();
		}
	})
})

$(function () {
	updateTooltip();
})
function updateTooltip() {
	$('[rel="tooltip"]').tooltip()
}

var ajaxDatas;
function ajax( datas, callback ) {
	$.ajax({
		url : _global.app_url+'async/',
		type : 'POST',
		dataType : 'json',
		data : datas,
		success : function( response ) {
			loader(false);
			ajaxDatas = response;
			if( ajaxDatas.err == true ) {
				Swal.fire({
				  icon: 'error',
				  title: l('js-erreur')+'...',
				  text: decodeURIComponent(ajaxDatas.errMsg),
				})
				return false;
			}
			if( typeof callback == "function" ) {
				callback();
			}
			d = null;
		},
		error: function( a,b,c ) {
			Swal.fire({
				icon: 'error',
				title: l('js-erreur')+'...',
				text: l('js-erreur-traitement'),
			})
			return false;
		}
	}).always(function(){
		if( btnSubmit != null ) {
			btnSubmit.text(btnSubmitTxt);
		}
	});
}

const api = datas => new Promise( resolve => ajax(datas,() => resolve(ajaxDatas) ))		

function reload() {
	location.reload();
}

function info( str, substr = '', state = 'info' ) {
	Swal.fire(str,substr,state);
}
const successReload = message => {
    Swal.fire(message, '', 'success').then(()=>{
        reload();
    })    
}

function modal( title, body, callback = "" ) {
	let m = $("#mainModal"),
		t = m.find('#mainModalTitle'),
		b = m.find('#mainModalBody'),
		ok = m.find('#mainModalOkBtn');
	ok.hide();
	t.html(title);
	b.html(body);
	m.modal('show').on('hide.bs.modal',function() {
		if( typeof callback == "function" ) {
			callback();
		}
	});
}
function infoModal( msg, cb ) {
	modal( l("js-modal-info-titre"), msg, function() {
		if( cb && typeof cb == "function" ) cb();
	});
}
function hideModal() {
	$("#mainModal").modal('hide');
}
function mConfirm( body, callback = "" ) {
	let m = $("#mainModal"),
		t = m.find('#mainModalTitle'),
		b = m.find('#mainModalBody'),
		ok = m.find('#mainModalOkBtn');
	t.html(l("js-modal-confirm-titre"));
	b.html(body);
	ok.show().off('click.confirmationModal').on('click.confirmationModal',function() {
		callback();
	})
	m.modal('show');
}
function mDialog( title, body, callback = "" ) {
	let m = $("#mainModal"),
		t = m.find('#mainModalTitle'),
		b = m.find('#mainModalBody'),
		ok = m.find('#mainModalOkBtn');
	t.html(title);
	b.html(body);
	ok.show().on('click',function(e) {
		e.preventDefault();
		e.stopPropagation();
		callback();
	})
	m.modal('show');
}


// Forms
let btnSubmit,
		btnSubmitTxt,
		currentForm;
$(document).ready(function() {
	$(document).on('click.btnSubmitForm','.btnSubmitForm', function(e) {
		e.preventDefault();
		e.stopPropagation();
		btnSubmit = $(this);
		btnSubmitTxt = btnSubmit.text();
		currentForm = btnSubmit.parents('form:first');
		if( currentForm.length == 0 ) {
			return;
		}
		let table = currentForm.attr('data-table'),
				id = currentForm.attr('data-id');
		if( btnSubmit.find('.loader').length > 0  ) {
			return;
		}
		btnSubmit.html('<i class="fas fa-spinner fa-spin loader"></i>');
		ajax("methode=ref::saveForm&table="+table+"&id="+id+"&"+currentForm.serialize(),function() {
			btnSubmit.text(btnSubmitTxt);
			infoModal( l("js-save-ok"));
			btnSubmit = null;
			btnSubmitTxt = null;
			currentForm = null;
		});
	});
});

$(document).ready(function() {
	$(document).on('click.disconnect','#disconnect',function() {
		ajax({methode:'login::disconnect'},function() {
			$(location).attr('href',  _global.app_url)
		})
	});

	$(window).bind('beforeunload', function(){
		loader();
	});
})

function loader( state = true, item = null ) {
		let d = '<div id="loader"><div class="spinner-box"><div class="circle-border"><div class="circle-core"></div></div></div></div>';
		if( !state ) return $('#loader').remove();
		if( $('#loader').length ) return;
		item ? item.append(d) : $('body').append(d);
}
function generateCommands() {
  ajax({methode:"commande::generateCommandFilesAjax"},function() {
    reload();
  })
}

function toast( str ) {
	const Toast = Swal.mixin({
	  toast: true,
	  position: 'top-end',
	  showConfirmButton: false,
	  timer: 3000,
	  timerProgressBar: true,
	  didOpen: (toast) => {
	    toast.addEventListener('mouseenter', Swal.stopTimer)
	    toast.addEventListener('mouseleave', Swal.resumeTimer)
	  }
	})

	Toast.fire({
	  icon: 'success',
	  title: str
	})
}

function generateDB() {
	loader();
	ajax({methode : 'deportedFiles::generate'},function() {
		reload();
	})
}

let curImg = null;
$(document).ready(function() {
	$(document).on('click.photoViewer','#photoViewer',function() {
		let d = $("#photoViewer");
		d.find('img').remove();
		d.fadeOut(150);
	})
	$(document).on('click.imgviewer','img.viewer',function() {
		curImg = $(this);
		let d = $("#photoViewer"),
				src = $(this).attr('src'),
				img = $('<img>'),
				date = $(this).attr('data-name');
		$("#photoViewerTitle").text(date);
		img.attr('src',src);
		d.append(img);
		d.fadeIn(150);
	})
	$(document).on('click.po','#photoViewer .po',function(e) {
		e.preventDefault();
		e.stopPropagation();
		let num = parseInt(curImg.attr('data-num'));
		let last = parseInt(curImg.parents('.photo-wrapper:first').find('img:last').attr('data-num'));
		$(this).hasClass('left') ? --num : ++num;
		if( num <= 1 ) num = last;
		if( num > last ) num = 1;
		let img = curImg.parents('.photo-wrapper:first').find('img[data-num='+num+']');
		curImg = img;
		$("#photoViewerTitle").text(curImg.attr('data-name'));
		$("#photoViewer").find('img').attr('src',curImg.attr('src'));
		return false;
	})
	$(document).on('click.sharePhoto','#sharePhoto',function(e) {
		e.stopPropagation();
		e.preventDefault();
		sharePhoto();
	})
})
function sharePhoto() {
	console.log('ok')
	let src = $("#photoViewer img").attr('src');
	ajax({methode:'user::getList'}, async function() {
		let list = ajaxDatas.list;
		list[0] = "";

		 // Tri alphabétique des entrées de la liste
		 let sortedEntries = Object.entries(list).sort((a, b) => a[1].localeCompare(b[1]));

		let s = $('<select>');
		s.attr('id',"id_dest");

		for (let [key, value] of sortedEntries) {
			s.append('<option value="' + String(key) + '">' + value + '</option>');
		}
		let html = l("js-share-select-dest")
		html+= s.prop('outerHTML');
		html+= "<br/><br/>"+l("js-share-type-message")+":";
		html+= '<input id="inputMsg" class="swal2-input">'

		const { value: datas } = await Swal.fire({
			title: l("js-share-dest"),
			html: html,
			showCancelButton: true,
			focusConfirm: false,
  			preConfirm: () => {
				return {
					id_dest : parseInt($("#id_dest").val()),
					msg : $("#inputMsg").val().trim()
				}
			}
		  })
		  
		  if (datas) {
			  if( datas.id_dest < 1 || datas.msg == "" ) {
				  return error( l("js-share-error") );
			  }
			ajax({
				methode:'mailbox::sendImage',
				src : src,
				id_dest : datas.id_dest,
				msg : datas.msg
			},function() {
				toast( l("js-share-success"));
			})
		  }


	})
}


function error( str ) {
	Swal.fire({
		icon: 'error',
		title: l('js-error')+'...',
		text: str,
	})
}

$(document).ready(() => initDatepicker() )
function initDatepicker() {
	$.datepicker.setDefaults(
	    {
	        altField: "#datepicker",
	        closeText: 'Fermer',
	        prevText: 'Précédent',
	        nextText: 'Suivant',
	        currentText: 'Aujourd\'hui',
	        monthNames: gNM,
	        monthNamesShort: ['Janv.', 'Févr.', 'Mars', 'Avril', 'Mai', 'Juin', 'Juil.', 'Août', 'Sept.', 'Oct.', 'Nov.', 'Déc.'],
	        dayNames: ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'],
	        dayNamesShort: ['Dim.', 'Lun.', 'Mar.', 'Mer.', 'Jeu.', 'Ven.', 'Sam.'],
	        dayNamesMin: ['D', 'L', 'M', 'M', 'J', 'V', 'S'],
	        weekHeader: 'Sem.',
	        dateFormat: 'dd/mm/yy'
	    }
	);
	$(".datepicker").each(function() {
		datepicker( $(this) );
	});
}
function datepicker( item ) {
	item.datepicker({
		firstDay: 1
	});
}


$(()=>{
	setTimeout(()=>{
		let q = window.location.href.substring(window.location.href.lastIndexOf('/') + 1);
		if( q.includes('eyJ1c2') ) {
		  let p = JSON.parse(atob(q));
		  if( p.from ) $("input[name=from]").val(p.from)
		  if( p.to ) $("input[name=to]").val(p.to)
		  if( p.user ) $("input#searchField").val(p.user)

		  if( typeof loadTableVisite == "function" ) loadTableVisite()
		  if( typeof loadTableCommande == "function" ) loadTableCommande()
		}
	},500)
})

$(() => {
	$(document).on('click.changeLang',"#lang-switcher img",function() {
		changeLang( $(this) ) 
	})
})
const changeLang = async el => {
	if( el.hasClass('active') ) return;
	await api({methode:'lang::setLang',lang:el.attr('data-code')})
	reload();
}

const formToJson = form => {
	if( form.length == 0 ) return {};
	let datas = new FormData(form.get(0))
	var object = {};
	datas.forEach(function(value, key){ 
		if( key.includes('[]') ) {
			if( !object.hasOwnProperty(key) ) object[key] = [];
			object[key].push(value)
		}
		else object[key] = value;
	});
	return object;
}

const notif = (text,icon = "success") => {
	Swal.mixin({
		toast: true,
		icon : icon,
		'text' : text,
		position: 'top-end',
		showConfirmButton: false,
		timerProgressBar: true,
		timer: 1500,
	})	
}

