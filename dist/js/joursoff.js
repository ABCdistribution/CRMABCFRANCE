$(document).ready(function() {
    $(document).on('change.yearOffSelected','#yearOffSelected', () => changeYear() )
    setTimeout( () =>changeYear(), 100 )


    $(document).on('change.newDayOff','#newDayOff',function() {
        let v = $(this).val();
        if( v == "" ) return;
        let d = new Date( v );

        let weekday = new Array(7);
        weekday[0]="Dimanche";
        weekday[1]="Lundi";
        weekday[2]="Mardi";
        weekday[3]="Mercredi";
        weekday[4]="Jeudi";
        weekday[5]="Vendredi";
        weekday[6]="Samedi";

        $("#newDayOffremarque").val( weekday[d.getDay() ] + " " + d.getDate() + " " + gNM[d.getMonth()]+" "+d.getFullYear()+" : " ).focus()

    })


})

const changeYear = async () => {
    let d = $("#dayz"),
        year = parseInt( $("#yearOffSelected").val() ),
        datas = {
            methode : 'admin::getDaysOff',
            year : year
        }
    d.empty();
    let response = await api(datas).then( r => r.results )
    let days = []
    for( let i in response ) 
        days.push(response[i])
    

    let html = []
    html.push(`<hr/><h3>Liste des jours OFF de ${year}</h3>`)

    html.push(`<div class="jours bg-light p-5 my-3" >`)
    if( days.length == 0 ) 
        html.push(`<p class="p-0 m-0">Aucun jour férié/off n'a été renseigné sur ${year}</p>`)
    else {
        
    html.push(`<div class="list-group">`)
    days.forEach( el => {
        let d = new Date(el.date_off);
        html.push(`
            <a href="#" class="list-group-item" onclick="deleteDayOff(${el.id})">
                ${d.toLocaleDateString('fr-FR')}
                <small class="text-secondary ml-5">${el.remarque ?? ""}</small>
            </a>
        `)
    })
    html.push(`</div>`)



    }
    html.push(`</div>`)
    html.push(`<button class="btn btn-primary" onclick="addDayOff()">Ajouter un jour off</button>`)
    html.push(`<button class="btn btn-warning ml-5" onclick="addAutoDayOff()">Ajouter les jours fériés ${year}</button>`)
    // Print
    d.html(html.join(''))
}

const addDayOff = async () => {
    let year =  parseInt( $("#yearOffSelected").val() )
    if( year < 1 ) return;
    const { value: formValues } = await Swal.fire({
        title: `Ajouter un jour off en ${year}`,
        html: `
        <div class="form-group">
            <span class="input-group-addon">Choix du jour :</span>
            <input type="date" class="form-control" id="newDayOff"  min="${year}-01-01" min="${year}-12-31" onfocus="this.showPicker()">
        </div>
        <div class="form-group">
            <span class="input-group-addon">Remarque (optionnelle) : </span>
            <input type="text" class="form-control" id="newDayOffremarque"  value="">
        </div>
        `,
        didOpen : () => setTimeout( () => $("#newDayOff").focus(), 1000 ),
        focusConfirm: false,
        preConfirm: () => {
          return {
            date : $("#newDayOff").val(),
            remarque : $("#newDayOffremarque").val()
          }
        }
      });
      if( !formValues || formValues?.date == "" ) return;
      
      api({
        methode : 'admin::addJourOff',
        date : formValues.date,
        remarque : formValues.remarque
      }).then( () => {
        changeYear()
        setTimeout( () => notif("Jour off ajouté"), 1000 )
      })
}
const deleteDayOff = id => {
    Swal.fire({
        title: "Supprimer ce jour OFF ?",
        showCancelButton: true,
        confirmButtonText: "Supprimer",
    }).then((result) => {
        if (!result.isConfirmed) return;
        api({
            methode : 'admin::deleteJourOff',
            id : id
        }).then( () => {
            changeYear()
            setTimeout( () => notif("Jour off supprimé"), 1000 )
        })
    });
}
const addAutoDayOff = () => {
    let year =  parseInt( $("#yearOffSelected").val() )
    api({
        methode : 'admin::addAutoJourOff',
        year : year
    }).then( () => {
        changeYear()
        setTimeout( () => notif("Jour fériés ajoutés"), 1000 )
    })
}