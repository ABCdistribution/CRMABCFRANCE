function createOp() {
  let f = $("#newPromo").serialize();
  ajax("methode=promo::new&"+f,()=>{
    info("L'OP a bien été créée");
    loadOpTable();
  })
}

function loadOpTable() {
  let vTable = null;
  ajax({methode:"promo::getJson"},()=>{
    vTable = $("#opTable").DataTable({
      pagination: "bootstrap",
      filter:true,
      order : [[2, 'asc']],
      data : ajaxDatas,
      destroy: true,
      lengthMenu:[5,10,25],
      pageLength: 10,
      columns :[
         {     "data"     :     "id"     },
         {     "data"     :     "id_as400"},
         {     "data"     :     "libelle"},
         {     "data"     :     "actif"}
      ]
    });
  })


}
