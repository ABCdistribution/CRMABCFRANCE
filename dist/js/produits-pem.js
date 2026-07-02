
let memProduct;
const searchCode = async () => {
    let id = $("[name=id_as400]").val().trim()
    if( id == "" ) return;
    let p = await api({
        methode : 'produit::getProduitPem',
        id : id
    })
    if( !p ) return infoModal( l('js-pem-no-produit') );
    if( !p.id_as400 ) return;
    memProduct = p;
    $("#addProductPem").html(`
        <div class="addProduct">
            <strong>Code : </strong> ${p.id_as400}<br/>
            <strong>Libellé : </strong> ${p.libelle}<br/>
            <p class="text-center">
                <button class="btn btn-primary" onClick="addProductPem()">${l('js-pem-add-produit')}</button>
            </p>
        </div>    
    `)

}
const addProductPem = async () => {
    if( !memProduct.id_as400 ) return;
    let p = await api({
        methode : 'produit::addProduitPem',
        id : memProduct.id_as400
    })
    Swal.fire({
        icon : "success",
        title : "Produit PEM ajouté"
    }).then( () => reload() )
}

$(()=>{
    $(document).on('click.edit','.edit',function() {
        edit($(this));
    })
})
const edit = async el => {
    let id = el.parents('li:first').attr('data-id');
    let s = await api({
        methode : 'produit::changeStatePem',
        id : id
    }).then( r => r.state )

    let st = el.parents('li:first').find('.st')
    st.removeClass('badge-danger badge-success')
    st.text( s ? l('js-actif') : l('js-inactif') );
    st.addClass( s ? 'badge-success' : 'badge-danger' )
}