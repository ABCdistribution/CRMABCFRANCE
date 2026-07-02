const acceptPlanning = id => {
    loader(true)
    api({methode:'planning::validateTournee',id:id}).then( r => {
        Swal.fire(l('js-validation-planning-tournee-validee'), '', 'success').then(()=>{
            $(location).attr('href',  _global.app_url+'ValidationPlanning')
        })  
    })
}
const refusePlanning = async id => {
    const { value: comment } = await Swal.fire({
        title: l('js-validation-planning-refus'),
        input: 'text',
        inputLabel: l('js-validation-planning-refus-commentaire'),
        inputValue: '',
        showCancelButton: true
    })
    api({
        methode:'planning::refuserTournee',
        id:id,
        com : comment
    }).then( r => {
        Swal.fire(l('js-validation-planning-tournee-refusee'), '', 'success').then(()=>{
            $(location).attr('href',  _global.app_url+'ValidationPlanning')
        })  
    })
}