<div id="page-dashboard">
    <div class="card card-primary card-outline">
      <div class="card-header">
        <h5 class="m-0">Dashboard</h5>
      </div>
      <div class="card-body">
          <div id="vTable-wrapper" class="rel" style="padding:5px;">

            <!-- Filtres -->
            <div class='row' id="filtersDiv">
              <div class="col-3">
                <div class="input-group mb-3">
                  <div class="input-group-prepend">
                    <span class="input-group-text" id="basic-addon1"><i class="far fa-calendar-alt"></i></span>
                  </div>
                  <input type="text" class="form-control datepicker" autocomplete="off" placeholder="Du..." name="from" value="01/<?php echo date('m/Y');?>">
                  <input type="text" class="form-control datepicker" autocomplete="off" placeholder="au..." name="to" value="<?php echo date('d/m/Y');?>">                  
                </div>
              </div>  
            </div>

            <div id="rez"></div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $(document).on('change.dpck','input[name=from], input[name=to]', () => {
            getDatas();
        })
        getDatas();
    })
    const getDatas = () => {
        let f = $("input[name=from]").val(),
            t = $("input[name=to]").val(),
            d = $("#rez");
        if( f == "" || t == "" ) return;
        loader(true,$("#page-dashboard"));
        ajax({methode:"stats::getDatas",f:f,t:t},function() {
            d.html(decodeURIComponent(ajaxDatas.html));
        });
    }
</script>









