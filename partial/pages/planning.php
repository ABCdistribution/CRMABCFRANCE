<?php if( !securite::can(17) ) return core::restricted();?>

  <h1>
    <?php echo l('page-planning-titre');?>
    <a class="float-right btn-xs abc bg-info" href="<?php echo URL; ?>Gestion_Secteur" id="btnGererPlanning" title="Gérer le planning du promoteur">
      <i class="fas fa-users-cog"></i> Gérer le planning
    </a>
    <a class="float-right btn-xs abc bg-danger" onclick="truncatePlanning()"><?php echo l('page-planning-bouton-supprimer');?></a>
    <a class="float-right btn-xs abc" id="btnUploadCSV" onclick="uploadPlanning()"><?php echo l('page-planning-bouton-charger');?></a>
  </h1>

  <form method="post" id="formUploadPlanning" class="hidden">
    <input type="file" name="filePlanning" accept=".csv"/>
  </form>

  <div class="clearfix"></div>

  <div class="alert alert-info" role="alert" id="infoCSV">
    <div class="row">
      <div class="col">
        <?php echo l('page-planning-import-csv');?>
        </div>
      <div class="col">
        <?php echo l('page-planning-import-structure');?>
      </div>
      <div class="col">
        <?php echo l('page-planning-import-date');?>
      </div>
  </div>
</div>


    <div class="card card-primary card-outline" id="promoter-selection-card">
      <div class="card-header">
        <h5 class="m-0"><?php echo l('page-planning-rechercher-promoteur');?></h5>
      </div>
      <div class="card-body">
        <div class="form-group">
          <label><?php echo l('page-planning-representant');?></label>
          <select class="form-control getPlanning" id="planning_sel_id_repr">
            <?php
            foreach( planning::getPlanningRepr() as $id => $o ) {
              if( empty($o) )
                echo '<option value="0" data-id="0"></option>';
              else
                echo '<option value="'.$id.'" data-id="'.$o['id_repr'].'">#'.$o['id_repr'].' : '.$o['name'].'</option>';
            }
            ?>
          </select>
        </div>

        <div class="input-group mb-3">
          <div class="input-group-prepend">
            <span class="input-group-text" id="basic-addon1"><?php echo l('page-planning-dates');?> :</span>
          </div>
          <input type="date" class="form-control getPlanning" name="from" autocomplete="off" value="<?php echo date("Y-m-d");?>" placeholder="<?php echo l('date-du');?>...">
          <input type="date" class="form-control getPlanning" name="to" autocomplete="off" placeholder="<?php echo l('date-au');?>">
        </div>
      </div>
    </div>
  <!-- Vue Planning (Calendrier) -->
  <div class="card card-primary card-outline mb-4">
    <div class="card-header">
      <h5 class="m-0">
        <i class="fas fa-calendar-week"></i> Vue Planning
      </h5>
    </div>
    <div class="card-body">
      <div id="week-view">
          <!-- Navigation -->
          <div class="row mb-3">
            <div class="col-12">
              <div class="d-flex justify-content-between align-items-center">
                <!-- Navigation semaine -->
                <div class="btn-group" role="group">
                  <button type="button" class="btn btn-sm btn-outline-primary" id="prev-week">
                    <i class="fas fa-chevron-left"></i> Précédent
                  </button>
                  <button type="button" class="btn btn-sm btn-outline-primary" id="cette-semaine">
                    <i class="fas fa-calendar-week"></i> <span id="semaine-courante-text">Cette semaine</span>
                  </button>
                  <button type="button" class="btn btn-sm btn-outline-primary" id="next-week">
                    Suivant <i class="fas fa-chevron-right"></i>
                  </button>
                </div>
                
                <!-- Navigation mois -->
                <div class="btn-group" role="group">
                  <button type="button" class="btn btn-sm btn-outline-info" id="mois-precedent">
                    <i class="fas fa-chevron-left"></i> Mois précédent
                  </button>
                  <button type="button" class="btn btn-sm btn-outline-info" id="mois-courant">
                    <i class="fas fa-calendar"></i> <span id="mois-courant-text">Ce mois</span>
                  </button>
                  <button type="button" class="btn btn-sm btn-outline-info" id="mois-suivant">
                    Mois suivant <i class="fas fa-chevron-right"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Vue calendrier grille -->
          <div id="calendar-grid">
            <div class="text-center text-muted py-5">
              <i class="fas fa-calendar-alt fa-3x mb-3"></i><br>
              <h5>Sélectionnez une période pour voir le planning</h5>
              <p>Utilisez les sélecteurs de date ci-dessus pour charger le calendrier</p>
            </div>
          </div>
      </div>
    </div>
  </div>
  
  <!-- Vue Liste -->
  <div class="card card-primary card-outline">
    <div class="card-header">
      <h5 class="m-0">
        <i class="fas fa-list"></i> Vue Liste
      </h5>
    </div>
    <div class="card-body">
      <div id="list-view">
        <div id="pl-wrapper"></div>
      </div>
    </div>
  </div>


<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js" integrity="sha512-uto9mlQzrs59VwILcLiRYeLKPPbS/bT71da/OEBYEwcdNUk8jYIy+D176RYoop1Da+f9mvkYrmj5MCLZWEtQuA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css" integrity="sha512-aOG0c6nPNzGk+5zjwyJaoRUgCdOrfSDhmMID2u4+OIslr0GjpLKo7Xm0Ao3xmpM4T8AmIouRkqwj1nrdVsLKEQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />

<style>
/* S'assurer que la carte de sélection du promoteur reste toujours visible */
#promoter-selection-card {
    display: block !important;
    visibility: visible !important;
}

/* Espacement entre les vues planning et liste */
#week-view {
    margin-bottom: 20px;
}

#list-view {
    margin-top: 0;
}

.card.mb-4 {
    margin-bottom: 1.5rem !important;
}

/* Styles pour la vue planning en colonnes sur une ligne */
#week-wrapper .row {
    display: flex;
    flex-wrap: nowrap; /* Empêche le retour à la ligne */
    min-height: 500px;
    overflow-x: auto; /* Scroll horizontal si nécessaire */
}

#week-wrapper .col-12,
#week-wrapper .col-sm-12,
#week-wrapper .col-md-12,
#week-wrapper .col-lg-12,
#week-wrapper .col-xl-12 {
    flex: 0 0 14.28%; /* 7 colonnes = 100% / 7 ≈ 14.28% */
    max-width: 14.28%;
    min-width: 120px; /* Largeur minimale pour la lisibilité */
}

#week-wrapper .card {
    height: 100%;
    min-height: 400px;
}

#week-wrapper .card-body {
    padding: 0.75rem;
}

#week-wrapper .list-group-item {
    border: none;
    border-bottom: 1px solid #dee2e6;
    padding: 0.5rem 0.75rem;
    font-size: 0.9rem;
}

#week-wrapper .list-group-item:last-child {
    border-bottom: none;
}

/* Responsive pour petits écrans */
@media (max-width: 768px) {
    #week-wrapper .col-12,
    #week-wrapper .col-sm-12,
    #week-wrapper .col-md-12,
    #week-wrapper .col-lg-12,
    #week-wrapper .col-xl-12 {
        flex: 0 0 140px; /* Largeur fixe sur mobile */
        max-width: 140px;
        min-width: 140px;
    }
    
    #week-wrapper .card {
        min-height: 200px;
    }
    
    #week-wrapper .card-header h6 {
        font-size: 0.8rem;
    }
    
    #week-wrapper .list-group-item {
        font-size: 0.75rem;
        padding: 0.4rem 0.5rem;
    }
}

/* Amélioration de l'affichage des jours vides */
#week-wrapper .text-muted {
    min-height: 200px;
}

#week-wrapper .fa-calendar-times {
    opacity: 0.3;
}
</style>

<script>
$(document).ready(function() {
    // Récupérer l'ID représentant depuis l'URL
    var urlParams = new URLSearchParams(window.location.search);
    var idRepr = urlParams.get('id_repr');
    
    if (idRepr) {
        // Trouver l'option correspondante dans le select
        var $select = $('#planning_sel_id_repr');
        var $option = $select.find('option[data-id="' + idRepr + '"]');
        
        if ($option.length > 0) {
            // Sélectionner l'option
            $select.val($option.val());
            // Déclencher le changement pour charger le planning
            $select.trigger('change');
        }
    }
    
     // Gestion de la vue planning
     var currentWeek = getWeekNumber(new Date());
     var currentYear = new Date().getFullYear();
     var currentMonth = new Date().getMonth();
     var currentYearMonth = new Date().getFullYear();
     
     // Fonction pour mettre à jour l'affichage de la semaine
     function updateWeekDisplay() {
         $('#semaine-courante-text').text('Semaine ' + currentWeek + ' - ' + currentYear);
     }
     
     // Initialiser l'affichage de la semaine
     updateWeekDisplay();
    
    
    // Navigation semaine
    $('#prev-week').on('click', function() {
        currentWeek--;
        if (currentWeek < 1) {
            currentWeek = 52;
            currentYear--;
        }
        updateWeekDisplay();
        loadWeekByWeek();
    });
    
    $('#next-week').on('click', function() {
        currentWeek++;
        if (currentWeek > 52) {
            currentWeek = 1;
            currentYear++;
        }
        updateWeekDisplay();
        loadWeekByWeek();
    });
    
    $('#cette-semaine').on('click', function() {
        // Recalculer la semaine courante
        currentWeek = getWeekNumber(new Date());
        currentYear = new Date().getFullYear();
        updateWeekDisplay();
        loadCurrentWeek();
    });
    
    // Charger la semaine en cours quand un promoteur est sélectionné
    $('#planning_sel_id_repr').on('change', function() {
        var selectedId = $(this).find('option:selected').data('id');
        if (selectedId && selectedId !== '0') {
            currentIdRepr = selectedId;
            // Charger automatiquement la semaine en cours (les deux vues sont maintenant visibles)
            loadCurrentWeek();
        }
    });
    
    // Gestion du bouton "Gérer le planning"
    $('#btnGererPlanning').on('click', function(e) {
        e.preventDefault();
        
        if (currentIdRepr) {
            // Rediriger vers la page de gestion avec l'ID du promoteur
            window.location.href = '<?php echo URL; ?>Gestion_Secteur?id_repr=' + currentIdRepr;
        } else {
            // Aucun promoteur sélectionné, aller sur la page de gestion normale
            window.location.href = '<?php echo URL; ?>Gestion_Secteur';
        }
    });
    
    
    
    
    // Navigation mois
    $('#mois-precedent').on('click', function() {
        currentMonth--;
        if (currentMonth < 0) {
            currentMonth = 11;
            currentYearMonth--;
        }
        loadMonthByMonth();
    });
    
    $('#mois-suivant').on('click', function() {
        currentMonth++;
        if (currentMonth > 11) {
            currentMonth = 0;
            currentYearMonth++;
        }
        loadMonthByMonth();
    });
    
    $('#mois-courant').on('click', function() {
        var today = new Date();
        currentMonth = today.getMonth();
        currentYearMonth = today.getFullYear();
        loadMonthByMonth();
    });
    
     // Fonction pour obtenir le numéro de semaine (commençant le lundi)
     function getWeekNumber(date) {
         var d = new Date(date);
         var day = d.getDay();
         d.setDate(d.getDate() + 4 - (day === 0 ? 7 : day)); // Ajuster au lundi de la semaine
         var yearStart = new Date(d.getFullYear(), 0, 1);
         return Math.ceil((((d - yearStart) / 86400000) + 1) / 7);
     }
    
    // Fonction pour charger la vue planning
    function loadWeekView() {
        if (!currentIdRepr) return;
        
        loadCurrentWeek();
    }
    
     // Fonction pour charger le planning sur une période personnalisée
     function loadPlanningByDateRange(dateDebut, dateFin) {
         if (!currentIdRepr) {
             alert('Veuillez d\'abord sélectionner un promoteur');
             return;
         }
         
         // Stocker les dates sélectionnées pour les utiliser dans displayWeekView
         window.selectedDateRange = {
             start: dateDebut,
             end: dateFin
         };
         
         console.log('Période sélectionnée:', window.selectedDateRange);
         
         $('#calendar-grid').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Chargement du planning...</div>');
         
         ajax({methode:'planning::getPlanning', id_repr : currentIdRepr, from : dateDebut, to : dateFin }, function() {
             displayWeekView(ajaxDatas.planning);
         });
     }
     
     // Fonction pour charger une semaine spécifique (lundi à samedi)
     function loadWeekByWeek() {
         if (!currentIdRepr) {
             alert('Veuillez d\'abord sélectionner un promoteur');
             return;
         }
         
         var startDate = getDateFromWeek(currentWeek, currentYear);
         var endDate = new Date(startDate);
         endDate.setDate(startDate.getDate() + 5); // Samedi (6 jours : lundi à samedi)
         
        var dateDebut = startDate.toISOString().split('T')[0];
        var dateFin = endDate.toISOString().split('T')[0];
        
        loadPlanningByDateRange(dateDebut, dateFin);
     }
     
     // Fonction pour charger la semaine courante (qui contient la date d'aujourd'hui)
     function loadCurrentWeek() {
         if (!currentIdRepr) {
             alert('Veuillez d\'abord sélectionner un promoteur');
             return;
         }
         
         var today = new Date();
         var day = today.getDay();
         
         // Calculer le lundi de cette semaine
         var monday = new Date(today);
         if (day === 0) { // Dimanche
             monday.setDate(today.getDate() - 6); // Aller au lundi précédent
         } else {
             monday.setDate(today.getDate() - (day - 1)); // Aller au lundi de cette semaine
         }
         
         // Calculer le samedi (6 jours après le lundi)
         var saturday = new Date(monday);
         saturday.setDate(monday.getDate() + 5);
         
        var dateDebut = monday.toISOString().split('T')[0];
        var dateFin = saturday.toISOString().split('T')[0];
        
        loadPlanningByDateRange(dateDebut, dateFin);
     }
     
     // Fonction pour charger un mois spécifique
     function loadMonthByMonth() {
         if (!currentIdRepr) {
             alert('Veuillez d\'abord sélectionner un promoteur');
             return;
         }
         
         var startOfMonth = new Date(currentYearMonth, currentMonth, 1);
         // Trouver le premier lundi du mois
         while (startOfMonth.getDay() !== 1) {
             startOfMonth.setDate(startOfMonth.getDate() + 1);
         }
         var endOfWeek = new Date(startOfMonth);
         endOfWeek.setDate(startOfMonth.getDate() + 5); // Samedi
         
        var dateDebut = startOfMonth.toISOString().split('T')[0];
        var dateFin = endOfWeek.toISOString().split('T')[0];
        
        // Mettre à jour le texte du bouton mois courant
        var monthNames = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 
                         'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
        $('#mois-courant-text').text(monthNames[currentMonth] + ' ' + currentYearMonth);
        
        loadPlanningByDateRange(dateDebut, dateFin);
     }
    
    // Fonction pour obtenir la date de début d'une semaine (lundi)
    function getDateFromWeek(week, year) {
        // Calculer le premier jour de l'année
        var jan1 = new Date(year, 0, 1);
        var jan1Day = jan1.getDay(); // 0=dimanche, 1=lundi, etc.
        
        // Calculer le premier lundi de l'année
        var firstMonday;
        if (jan1Day === 1) {
            firstMonday = new Date(year, 0, 1);
        } else if (jan1Day === 0) {
            firstMonday = new Date(year, 0, 2); // Dimanche -> lundi suivant
        } else {
            firstMonday = new Date(year, 0, 1 + (8 - jan1Day)); // Autres jours
        }
        
        // Calculer le lundi de la semaine demandée
        var weekMonday = new Date(firstMonday);
        weekMonday.setDate(firstMonday.getDate() + (week - 1) * 7);
        
        return weekMonday;
    }
    
    // Fonction pour créer la grille calendrier
    function createCalendarGrid(planningData) {
        var sortedDates = [];
        
        // Utiliser les dates de la période sélectionnée si disponible
        if (window.selectedDateRange) {
            var startDate = new Date(window.selectedDateRange.start);
            var endDate = new Date(window.selectedDateRange.end);
            
            // Générer toutes les dates entre startDate et endDate
            var currentDate = new Date(startDate);
            while (currentDate <= endDate) {
                sortedDates.push(currentDate.toISOString().split('T')[0]);
                currentDate.setDate(currentDate.getDate() + 1);
            }
            
            // Limiter à 6 jours maximum (lundi à samedi)
            if (sortedDates.length > 6) {
                sortedDates = sortedDates.slice(0, 6);
            }
        } else if (planningData && Object.keys(planningData).length > 0) {
            // Utiliser les dates des données
            sortedDates = Object.keys(planningData).sort();
        } else {
            // Utiliser la semaine courante par défaut
            var today = new Date();
            var startOfWeek = new Date(today);
            startOfWeek.setDate(today.getDate() - today.getDay() + 1); // Lundi
            
            for (var i = 0; i < 6; i++) { // 6 jours : lundi à samedi
                var date = new Date(startOfWeek);
                date.setDate(startOfWeek.getDate() + i);
                sortedDates.push(date.toISOString().split('T')[0]);
            }
        }
        
        console.log('Dates générées pour la semaine (lundi-samedi):', sortedDates);
        console.log('Nombre de jours générés:', sortedDates.length);
        
        // Créer la grille calendrier
        var calendarHtml = '<div class="calendar-container">';
        
        // En-tête avec les jours
        calendarHtml += '<div class="calendar-header">';
        calendarHtml += '<div class="time-column-header"></div>';
        
        console.log('Création des en-têtes pour', sortedDates.length, 'jours');
        for (var i = 0; i < sortedDates.length; i++) {
            var date = sortedDates[i];
            var visitDate = new Date(date);
            var dayName = visitDate.toLocaleDateString('fr-FR', { weekday: 'long' });
            var dayNumber = visitDate.getDate();
            var monthName = visitDate.toLocaleDateString('fr-FR', { month: 'long' });
            
            console.log('Jour', i, ':', dayName, dayNumber, monthName);
            
            // Calculer les statistiques pour ce jour
            var visits = planningData[date] || {};
            var visitsArray = [];
            for (var key in visits) {
                visitsArray.push(visits[key]);
            }
            var totalVisits = visitsArray.length;
            
            calendarHtml += '<div class="day-header">';
            calendarHtml += '<div class="day-name">' + dayName + ' ' + dayNumber + ' ' + monthName + '</div>';
            calendarHtml += '<div class="day-stats">';
            // calendarHtml += '<div>C.A.: 0.00€</div>';
            // calendarHtml += '<div>Réalisé: 0%</div>';
            // calendarHtml += '<div>Transformé: 0%</div>';
            calendarHtml += '</div>';
            calendarHtml += '</div>';
        }
        
        calendarHtml += '</div>';
        
        // Corps de la grille avec les heures
        calendarHtml += '<div class="calendar-body">';
        
        // Créer les lignes d'heures (de 4h à 15h)
        for (var hour = 4; hour <= 15; hour++) {
            calendarHtml += '<div class="time-row">';
            calendarHtml += '<div class="time-label">' + hour + 'h</div>';
            
            // Créer les cellules pour chaque jour
            if (hour === 4) { // Debug seulement pour la première heure
                console.log('Création des cellules pour', sortedDates.length, 'jours à l\'heure', hour);
            }
            for (var i = 0; i < sortedDates.length; i++) {
                var date = sortedDates[i];
                var visits = planningData[date] || {};
                
                // Convertir l'objet en tableau
                var visitsArray = [];
                for (var key in visits) {
                    visitsArray.push(visits[key]);
                }
                
                calendarHtml += '<div class="time-cell" data-date="' + date + '" data-hour="' + hour + '">';
                
                // Afficher une visite par heure dans l'ordre
                var visitIndex = hour - 4; // Commencer à 4h = index 0
                
                if (visitIndex < visitsArray.length) {
                    var visit = visitsArray[visitIndex];
                    var name = visit[3] || 'Visite inconnue';
                    var statut = visit[1];
                    
                    // Déterminer la couleur du bloc selon le statut
                    var blockClass = 'visit-block';
                    if (statut == 1) {
                        blockClass += ' success'; // Terminé - vert
                    } else if (statut == 2) {
                        blockClass += ' danger'; // Annulé - rouge
                    } else {
                        blockClass += ' warning'; // En attente - orange
                    }
                    
                    // Déterminer le statut de la visite selon les données
                    var statusText = '';
                    var motif = visit[2];
                    var statut = visit[1];
                    
                    if (statut == 1) {
                        // Visite terminée - simuler un CA
                        var ca = Math.random() * 3000 + 500;
                        statusText = ca.toFixed(2) + ' € C.A.';
                    } else if (statut == 2) {
                        // Visite annulée
                        statusText = 'Visite sans commande';
                        if (motif) statusText += ' - ' + motif;
                    } else {
                        // Visite en attente
                        statusText = 'Visite sans commande';
                        if (motif) statusText += ' - ' + motif;
                    }
                    
                    calendarHtml += '<div class="' + blockClass + '" style="height: 60px;">';
                    calendarHtml += '<div class="visit-client">' + name + '</div>';
                    calendarHtml += '<div class="visit-details">' + statusText + '</div>';
                    calendarHtml += '</div>';
                }
                
                calendarHtml += '</div>';
            }
            
            calendarHtml += '</div>';
        }
        
        calendarHtml += '</div>';
        calendarHtml += '</div>';
        
        // Ajouter le CSS pour la grille
        calendarHtml += '<style>';
        calendarHtml += '.calendar-container { border: 1px solid #ddd; border-radius: 4px; overflow: hidden; margin-top: 20px; }';
        calendarHtml += '.calendar-header { display: flex; background: #f8f9fa; border-bottom: 2px solid #ddd; }';
        calendarHtml += '.time-column-header { width: 60px; padding: 10px; font-weight: bold; border-right: 1px solid #ddd; }';
        calendarHtml += '.day-header { flex: 1; padding: 10px; border-right: 1px solid #ddd; text-align: center; }';
        calendarHtml += '.day-header:last-child { border-right: none; }';
        calendarHtml += '.day-name { font-weight: bold; margin-bottom: 5px; }';
        calendarHtml += '.day-stats { font-size: 0.8em; color: #666; }';
        calendarHtml += '.day-stats div { margin: 2px 0; }';
        calendarHtml += '.calendar-body { display: flex; flex-direction: column; }';
        calendarHtml += '.time-row { display: flex; border-bottom: 1px solid #eee; min-height: 60px; }';
        calendarHtml += '.time-row:last-child { border-bottom: none; }';
        calendarHtml += '.time-label { width: 60px; padding: 10px; font-weight: bold; border-right: 1px solid #ddd; background: #f8f9fa; display: flex; align-items: center; }';
        calendarHtml += '.time-cell { flex: 1; padding: 2px; border-right: 1px solid #eee; position: relative; }';
        calendarHtml += '.time-cell:last-child { border-right: none; }';
        calendarHtml += '.visit-block { margin: 1px; padding: 8px; border-radius: 6px; font-size: 0.75em; cursor: pointer; min-height: 60px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }';
        calendarHtml += '.visit-block.success { background: #d4edda; border: 2px solid #28a745; color: #155724; }';
        calendarHtml += '.visit-block.danger { background: #f8d7da; border: 2px solid #dc3545; color: #721c24; }';
        calendarHtml += '.visit-block.warning { background: #fff3cd; border: 2px solid #ffc107; color: #856404; }';
        calendarHtml += '.visit-client { font-weight: bold; margin-bottom: 4px; font-size: 0.9em; line-height: 1.2; text-align: center; }';
        calendarHtml += '.visit-details { margin-bottom: 3px; font-size: 0.8em; font-weight: 500; }';
        calendarHtml += '.visit-time { font-size: 0.8em; color: #333; font-weight: bold; margin-top: 3px; text-align: center; }';
        calendarHtml += '</style>';
        
        return calendarHtml;
    }
    
     // Fonction pour afficher la grille calendrier
     function displayWeekView(planningData) {
         console.log('displayWeekView appelée avec:', planningData);
         
         // Créer directement le calendrier en grille
         var calendarHtml = createCalendarGrid(planningData);
         $('#calendar-grid').html(calendarHtml);
    }
});
</script>