
        <div class="row">
          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-info">
              <div class="inner">
                <h3>150</h3>

                <p>Visites du jour</p>
              </div>
              <div class="icon">
                <i class="fas fa-money-check"></i>
              </div>
              <a href="#" class="small-box-footer">Plus d'informations <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          <!-- ./col -->
          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-success">
              <div class="inner">
                <h3>78</h3>
                <p>Commandes du jour</p>
              </div>
              <div class="icon">
                <i class="fas fa-chart-area"></i>
              </div>
              <a href="#" class="small-box-footer">Plus d'informations <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          <!-- ./col -->
          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-warning">
              <div class="inner">
                <h3>73 866<sup style="font-size: 20px">€</sup></h3>

                <p>Montant des commandes</p>
              </div>
              <div class="icon">
                <i class="fas fa-store"></i>
              </div>
              <a href="#" class="small-box-footer">Plus d'informations <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          <!-- ./col -->
          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-danger">
              <div class="inner">
                <h3>65</h3>

                <p>Ruptures de stock</p>
              </div>
              <div class="icon">
                <i class="fas fa-cubes"></i>
              </div>
              <a href="#" class="small-box-footer">Plus d'informations <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          <!-- ./col -->
        </div>








        <div class="row">
          <div class="col-lg-6 nomobile">
           
            <div class="card card-primary card-outline">
              <div class="card-header">
                <h5 class="m-0">Rapport de commandes du jour</h5>
              </div>
              <div class="card-body">           
				  <div id="world-map" style="width: 600px; height: 400px; margin: 10px auto;"></div>
				  <script>
				    $(function(){
				    	var palette = ['#EF6C00', '#FFB74D', '#FF9800', '#F57C00', '#E65100'];

						let map = new jvm.Map({
							map: 'fr_regions_2016_merc',
							container: $('#world-map'),
							backgroundColor : '#fff',
							series: {
							  regions: [{
							    attribute: 'fill'
							  }]
							},
							onRegionTipShow: function(e, el, code){
							  let rand = parseInt(Math.random() * 100);
						      el.html(el.html()+' : '+rand+' commandes');
						    }
						});				      

				      

				      let generateColors = function(){
				        var colors = {},
				            key;

				        for (key in map.regions) {
				          colors[key] = palette[Math.floor(Math.random()*palette.length)];
				        }
				        return colors;
				      }		

				      map.series.regions[0].setValues(generateColors());


				    });
				  </script> 
			  </div>
		  </div>				




          </div>
          <!-- /.col-md-6 -->
          <div class="col-lg-6">


            <div class="row">
          		<div class="col">
              <div class="small-box bg-primary">
                  <div class="inner">
                    <h3>71</h3>

                    <p>Connexion à l'application</p>
                  </div>
                  <div class="icon">
                    <i class="fas fa-mobile"></i>
                  </div>
                </div>
              </div>
            	<div class="col">
                <div class="small-box bg-info">
                  <div class="inner">
                    <h3>6</h3>

                    <p>Nouveaux messages</p>
                  </div>
                  <div class="icon">
                    <i class="fas fa-envelope"></i>
                  </div>
                </div>
	          	</div>      			
  			</div>








            <div class="card card-primary card-outline">
              <div class="card-header">
                <h5 class="m-0">Magasins non visités <span class="badge badge-primary float-right">9</span></h5>
              </div>
              <div class="card-body">            
		              <table class="table table-hover">
		                <tbody><tr>
		                  <th>Magasin</th>
		                  <th>Ville</th>
		                  <th>Dernière visite</th>
		                </tr>
		                <tr>
		                  <td>Auchan</td>
		                  <td>Paris 15eme</td>
		                  <td>23/08/2019</td>
		                </tr>
		                <tr>
		                  <td>Casino</td>
		                  <td>Nîmes</td>
		                  <td>21/07/2019</td>
		                </tr>
		                <tr>
		                  <td>Carrefour</td>
		                  <td>Brest</td>
		                  <td>07/09/2019</td>
		                </tr>	
		                <tr>
		                  <td>Framprix</td>
		                  <td>Dijon</td>
		                  <td>13/09/2019</td>
		                </tr>		                	                
		              </tbody>
		          </table>

                <br/>
                <div class="text-right">
                	<a href="#" class="btn btn-primary">Tout voir</a>
            	</div>
              </div>
            </div>




          </div>
          <!-- /.col-md-6 -->
        </div>
        <!-- /.row -->
        <div class="row">
          <div class="col-md-6">
            <div class="card card-primary card-outline">
              <div class="card-header">
                <h5 class="m-0">Répartitions des visites</h5>
              </div>
              <div class="card-body">

              <div class="row">
                <div class="col-md-6">
                  <div class="chart-responsive">
                    <canvas id="pieChart" height="180" width="328" style="width: 328px; height: 180px;"></canvas>
                    <script>
                      let data = {
                          datasets: [{
                              data: [67, 185, 88],
                              backgroundColor: [
                                "#81c784",
                                "#64b5f6",
                                "#ba68c8",
                              ],                              
                          }],

                          // These labels appear in the legend and in the tooltips when hovering different arcs
                          labels: [
                              'EST : '+67,
                              'OUEST : '+185,
                              'SUD : '+88
                          ]
                      };
                      var myPieChart = new Chart($("#pieChart"), {
                          type: 'pie',
                          data: data,
                          options: {
                            title: {
                              display : true,
                              text : "Aujourd'hui"
                            },
                            legend: {
                                position: 'right',
                                labels: {
                                    boxWidth: 20,
                                    padding: 5
                                }
                            }                            
                          }
                      });
                    </script>
                  </div>
                  <!-- ./chart-responsive -->
                </div>
                <div class="col-md-6">
                  <div class="chart-responsive">
                    <canvas id="pieChart2" height="180" width="328" style="width: 328px; height: 180px;"></canvas>
                    <script>
                      let data2 = {
                          datasets: [{
                              data: [325, 410, 295],
                              backgroundColor: [
                                "#81c784",
                                "#64b5f6",
                                "#ba68c8",
                              ],                              
                          }],

                          // These labels appear in the legend and in the tooltips when hovering different arcs
                          labels: [
                              'EST : '+325,
                              'OUEST : '+410,
                              'SUD : '+295
                          ]
                      };
                      var myPieChart2 = new Chart($("#pieChart2"), {
                          type: 'pie',
                          data: data2,
                           options: {
                            title :{
                              display : true,
                              text : "Cette semaine"
                            },
                            legend: {
                                position: 'right',
                                labels: {
                                    boxWidth: 20,
                                    padding: 5
                                }
                            }    
                          }
                      });
                    </script>
                  </div>
                  <!-- ./chart-responsive -->
                </div>                
                <!-- /.col -->
              </div>
              <!-- /.row -->
            </div>
            <!-- /.box-body -->
            <!-- /.footer -->
          </div>
        </div>


          <div class="col-md-6">
            <div class="card card-primary card-outline">
              <div class="card-header">
                <h5 class="m-0">Répartitions des commandes</h5>
              </div>
              <div class="card-body">

              <div class="row">
                <div class="col-md-6">
                  <div class="chart-responsive">
                    <canvas id="pieChart3" height="180" width="328" style="width: 328px; height: 180px;"></canvas>
                    <script>
                      let data3 = {
                          datasets: [{
                              data: [67, 185, 88],
                              backgroundColor: [
                                "#81c784",
                                "#64b5f6",
                                "#ba68c8",
                              ],                              
                          }],

                          // These labels appear in the legend and in the tooltips when hovering different arcs
                          labels: [
                              'EST : '+67,
                              'OUEST : '+185,
                              'SUD : '+88
                          ]
                      };
                      var myPieChart = new Chart($("#pieChart3"), {
                          type: 'bar',
                          data: data3,
                          options: {
                            title: {
                              display : true,
                              text : "Aujourd'hui"
                            },     
                            legend : {
                              display : false
                            }                     
                          }
                      });
                    </script>
                  </div>
                  <!-- ./chart-responsive -->
                </div>
                <div class="col-md-6">
                  <div class="chart-responsive">
                    <canvas id="pieChart4" height="180" width="328" style="width: 328px; height: 180px;"></canvas>
                    <script>
                      let data4 = {
                          datasets: [{
                              data: [325, 410, 295],
                              backgroundColor: [
                                "#81c784",
                                "#64b5f6",
                                "#ba68c8",
                              ],                              
                          }],

                          // These labels appear in the legend and in the tooltips when hovering different arcs
                          labels: [
                              'EST : '+325,
                              'OUEST : '+410,
                              'SUD : '+295
                          ]
                      };
                      var myPieChart2 = new Chart($("#pieChart4"), {
                          type: 'bar',
                          data: data4,
                           options: {
                            title :{
                              display : true,
                              text : "Cette semaine"
                            }, 
                            legend : {
                              display : false
                            } 
                          }
                      });
                    </script>
                  </div>
                  <!-- ./chart-responsive -->
                </div>                
                <!-- /.col -->
              </div>
              <!-- /.row -->
            </div>
            <!-- /.box-body -->
            <!-- /.footer -->
          </div>
        </div>        
      </div>



      <div class="row">
          <div class="col-md-6">
            <div class="card card-primary card-outline">
              <div class="card-header">
                <h5 class="m-0">Rutures de stocks</h5>
              </div>
              <div class="card-body">

              <div class="row">
                <div class="col">
                  <div class="chart-responsive">
                    <canvas id="pieChart5" height="180" width="328" style="width: 328px; height: 180px;"></canvas>
                    <script>
                      let data5 = {
                          datasets: [{
                              data: [16,11,15,22,38,12,15],         
                              backgroundColor: "#FF4141",                       
                          }],

                          // These labels appear in the legend and in the tooltips when hovering different arcs
                          labels: [
                              '29 Sep','30 Sep','1 Oct','2 Oct','3 Oct','4 Oct','5 Oct',
                          ]
                      };
                      var myPieChart2 = new Chart($("#pieChart5"), {
                          type: 'line',
                          data: data5,
                           options: {
                            title :{
                              display : true,
                              text : "Cette semaine"
                            }, 
                            legend : {
                              display : false
                            } 
                          }
                      });
                    </script>
                  </div>
                  <!-- ./chart-responsive -->
                </div>                
                <!-- /.col -->
              </div>
              <!-- /.row -->
            </div>
            <!-- /.box-body -->
            <!-- /.footer -->
          </div>
        </div> 
          <div class="col-md-6">
            <div class="card card-primary card-outline">
              <div class="card-header">
                <h5 class="m-0">CA des commandes</h5>
              </div>
              <div class="card-body">

              <div class="row">
                <div class="col">
                  <div class="chart-responsive">
                    <canvas id="pieChart6" height="180" width="328" style="width: 328px; height: 180px;"></canvas>
                    <script>
                      let data6 = {
                          datasets: [{
                              data: [95536,76654,112665,99987,64553,52223,87666],   
                              backgroundColor: "#68DB46",                           
                          }],

                          // These labels appear in the legend and in the tooltips when hovering different arcs
                          labels: [
                              '29 Sep','30 Sep','1 Oct','2 Oct','3 Oct','4 Oct','5 Oct',
                          ]
                      };
                      var myPieChart2 = new Chart($("#pieChart6"), {
                          type: 'bar',
                          data: data6,
                           options: {
                            title :{
                              display : true,
                              text : "Cette semaine"
                            }, 
                            legend : {
                              display : false
                            } 
                          }
                      });
                    </script>
                  </div>
                  <!-- ./chart-responsive -->
                </div>                
                <!-- /.col -->
              </div>
              <!-- /.row -->
            </div>
            <!-- /.box-body -->
            <!-- /.footer -->
          </div>
        </div>         
      </div>
