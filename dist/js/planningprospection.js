let pl;
$(document).ready( () => {
    pl = $("#pl-wrapper")
    $(document).on('change.pcsFields','#planning_sel_id_repr,[name=from],[name=to]',()=> planningCS() )
})
const planningCS = () => {
    let datas = {
        methode : 'task::getPlanning',
        id_user : parseInt( $("#planning_sel_id_repr").val() ),
        from : $('[name=from]').val(),
        to : $('[name=to]').val(),
        isClient : showTacheClient
    }
    if( !datas.id_user || datas.from == "" ) return;
    ajax( datas, () => printPlanning( ajaxDatas ) )
}
const printPlanning = datas => {
    let tasks = datas.tasks;
    if( tasks.length == 0 ) 
        return pl.html("<p class='mt-5 text-center text-secondary'>"+l('js-task-vide')+"</p>");
    
    let content = [],
        prevDate = null;  

    content.push(`
        <table class="table table-condensed table-hover">
            <thead>
                <tr>
                    <th>${l('js-task-table-date')}</th>
                    <th>${l('js-task-table-prospect')}</th>
                    <th>${l('js-task-table-type')}</th>
                    <th>${l('js-task-table-action')}</th>
                    <th>${l('js-task-table-desc')}</th>
                    <th>${l('js-task-table-priorite')}</th>
                    <th>${l('js-task-table-statut')}</th>
                </tr>
            </thead>
            <tbody>
    `)



    for( let i in tasks ) {
        let o = tasks[i],
            d = tasks[i]['statut'] == 1
        content.push(`
            <tr class="${ d ? 'bg-success text-white':'' }">
                <td>${o.date_task}</td>
                <td>${ o.target == "prospect" ? o.prospect.ig.nom : o.client.enseigne }</td>
                <td>${o.type}</td>
                <td>${o.action}</td>
                <td>${o.libelle}</td>
                <td>${o.priorite}</td>
                <td>${ d ? l('js-task-fait'): l('js-task-a-faire')}</td>
            </tr>
        `);
    }
    content.push('</tbody></table>')
    pl.html(content.join(''))
}