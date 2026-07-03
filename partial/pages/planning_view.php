<?php if( !securite::can(17) ) return core::restricted();?>

<style>
/* Variables de couleurs */
:root {
    --primary: #3498db;
    --background: #f8f9fa;
    --success: #27ae60;
    --warning: #f39c12;
    --danger: #e74c3c;
    --border: #ddd;
    --text-dark: #2c3e50;
}

/* Container principal */
.planning-view-container {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    overflow: hidden;
    margin-bottom: 30px;
}

/* Header */
.planning-view-header {
    background: linear-gradient(135deg, var(--primary) 0%, #2980b9 100%);
    color: white;
    padding: 24px 28px;
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

.planning-view-title {
    margin: 0;
    font-size: 24px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 12px;
}

.planning-view-title i {
    font-size: 28px;
}

.planning-view-subtitle {
    margin: 8px 0 0 0;
    opacity: 0.9;
    font-size: 14px;
}

/* Section sélection */
.promoteur-selection {
    padding: 24px 28px;
    background: var(--background);
    border-bottom: 1px solid var(--border);
}

.promoteur-selection-inner {
    max-width: 600px;
    display: flex;
    align-items: center;
    gap: 16px;
}

.promoteur-selection label {
    font-weight: 500;
    color: var(--text-dark);
    margin: 0;
    white-space: nowrap;
}

.promoteur-selection select {
    flex: 1;
    padding: 10px 16px;
    border: 1px solid var(--border);
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.3s;
}

.promoteur-selection select:focus {
    border-color: var(--primary);
    outline: none;
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
}

/* Navigation */
.planning-navigation {
    padding: 20px 28px;
    background: white;
    border-bottom: 1px solid var(--border);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
}

.nav-buttons {
    display: flex;
    gap: 8px;
}

.nav-btn {
    padding: 8px 16px;
    background: white;
    border: 1px solid var(--border);
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 6px;
    color: var(--text-dark);
}

.nav-btn:hover {
    background: var(--background);
    border-color: var(--primary);
    color: var(--primary);
}

.nav-btn i {
    font-size: 12px;
}

.current-week {
    font-size: 16px;
    font-weight: 600;
    color: var(--text-dark);
}

.btn-return {
    background: #3498db;
    color: white;
    border: none;
    padding: 12px 20px;
    border-radius: 8px;
    font-size: 14px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
    font-weight: 600;
    cursor: pointer;
    box-shadow: 0 2px 6px rgba(52, 152, 219, 0.3);
}

.btn-return:hover {
    background: #2980b9;
    color: white;
    text-decoration: none;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(52, 152, 219, 0.4);
}

.btn-return:active {
    transform: translateY(0);
}

.btn-return i {
    font-size: 16px;
}

/* Légende */
.planning-legend {
    padding: 16px 28px;
    background: var(--background);
    border-bottom: 1px solid var(--border);
    display: flex;
    gap: 24px;
    flex-wrap: wrap;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
}

.legend-color {
    width: 20px;
    height: 20px;
    border-radius: 4px;
}

.legend-color.success { background: var(--success); }
.legend-color.warning { background: var(--warning); }
.legend-color.danger { background: var(--danger); }

/* Calendrier */
.planning-calendar {
    padding: 12px 16px;
    min-height: 400px;
}

.calendar-grid {
    display: grid;
    grid-template-columns: 50px repeat(7, 1fr);
    gap: 1px;
    background: var(--border);
    border: 1px solid var(--border);
    border-radius: 8px;
    overflow: hidden;
}

.calendar-header {
    background: var(--text-dark);
    color: white;
    padding: 12px 8px;
    text-align: center;
    font-weight: 600;
    font-size: 13px;
}

.calendar-header.time-col {
    background: var(--text-dark);
}

.calendar-time {
    background: var(--background);
    padding: 8px;
    text-align: center;
    font-weight: 600;
    font-size: 12px;
    color: var(--text-dark);
    display: flex;
    align-items: center;
    justify-content: center;
}

.calendar-cell {
    background: white;
    padding: 4px;
    min-height: 60px;
    position: relative;
}

/* Cellules de visite */
.planning-cell {
    border-radius: 6px;
    padding: 6px 8px;
    font-size: 11px;
    line-height: 1.3;
    transition: all 0.2s;
    margin-bottom: 4px;
    display: flex;
    flex-direction: column;
    gap: 2px;
    border: 1px solid rgba(0,0,0,0.1);
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.planning-cell.clickable {
    cursor: pointer;
}

.planning-cell.clickable:hover {
    transform: translateY(-1px);
    box-shadow: 0 3px 6px rgba(0,0,0,0.2);
}

.planning-cell.visite-terminee {
    background: #d5f4e6;
    border-left: 4px solid var(--success);
    color: #0d5c3a;
}

.planning-cell.visite-en-attente {
    background: #fef5e7;
    border-left: 4px solid var(--warning);
    color: #7d5e0e;
}

.planning-cell.visite-annulee {
    background: #fadbd8;
    border-left: 4px solid var(--danger);
    color: #7b1e1a;
}

.visite-magasin {
    font-weight: 600;
    font-size: 12px;
    line-height: 1.2;
}

.visite-code {
    font-size: 10px;
    opacity: 0.8;
    font-weight: 500;
}

.visite-type {
    font-size: 10px;
    font-style: italic;
    margin-top: 2px;
    opacity: 0.9;
}

.visite-auto {
    font-size: 9px;
    background: rgba(0,0,0,0.05);
    padding: 2px 4px;
    border-radius: 3px;
    margin-top: 2px;
    display: inline-block;
}

/* Loader */
.planning-loader {
    text-align: center;
    padding: 60px 20px;
    color: var(--primary);
}

.planning-loader i {
    font-size: 48px;
    margin-bottom: 16px;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.planning-empty {
    text-align: center;
    padding: 60px 20px;
    color: #999;
}

.planning-empty i {
    font-size: 64px;
    margin-bottom: 16px;
    opacity: 0.3;
}

/* Responsive */
@media (max-width: 1200px) {
    .calendar-grid {
        font-size: 11px;
    }
    
    .planning-cell {
        font-size: 10px;
    }
    
    .visite-magasin {
        font-size: 11px;
    }
}

@media (max-width: 768px) {
    .planning-navigation {
        flex-direction: column;
        align-items: stretch;
    }
    
    .nav-buttons {
        justify-content: center;
    }
    
    .current-week {
        text-align: center;
    }
}
</style>

<div class="planning-view-container">
    <!-- Header -->
    <div class="planning-view-header">
        <h1 class="planning-view-title">
            <i class="fas fa-calendar-week"></i>
            Vue Planning - Lecture seule
        </h1>
        <p class="planning-view-subtitle">Visualisez le planning hebdomadaire des promoteurs</p>
    </div>

    <!-- Sélection du promoteur -->
    <div class="promoteur-selection">
        <div class="promoteur-selection-inner">
            <label for="selPromoteur">
                <i class="fas fa-user"></i> Promoteur :
            </label>
            <select id="selPromoteur" class="form-control">
                <option value="">-- Sélectionner un promoteur --</option>
                <?php
                foreach( planning::getPlanningRepr() as $id => $o ) {
                    if( empty($o) ) continue;
                    echo '<option value="'.$id.'" data-id-repr="'.$o['id_repr'].'">#'.$o['id_repr'].' : '.$o['name'].'</option>';
                }
                ?>
            </select>
            <a href="<?php echo URL; ?>Gestion_Secteur" class="btn-return" id="btnGererPlanning">
                <i class="fas fa-users-cog"></i> <span id="btnGererText">Gérer le planning</span>
            </a>
        </div>
    </div>

    <!-- Légende -->
    <div class="planning-legend">
        <div class="legend-item">
            <div class="legend-color success"></div>
            <span>Visite terminée</span>
        </div>
        <div class="legend-item">
            <div class="legend-color warning"></div>
            <span>En attente</span>
        </div>
    </div>

    <!-- Navigation semaine -->
    <div class="planning-navigation" id="planningNav" style="display: none;">
        <div class="nav-buttons">
            <button class="nav-btn" id="btnPrevWeek">
                <i class="fas fa-chevron-left"></i>
                Semaine précédente
            </button>
            <button class="nav-btn" id="btnThisWeek">
                <i class="fas fa-calendar-day"></i>
                Cette semaine
            </button>
            <button class="nav-btn" id="btnNextWeek">
                Semaine suivante
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
        <div class="current-week" id="currentWeekDisplay">
            Semaine du ...
        </div>
    </div>

    <!-- Calendrier -->
    <div class="planning-calendar" id="planningContent">
        <div class="planning-empty">
            <i class="fas fa-calendar-alt"></i>
            <h4>Sélectionnez un promoteur pour afficher son planning</h4>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    let currentIdRepr = null;
    let currentWeekStart = null;
    let currentWeekEnd = null;
    
    // Initialisation
    const urlParams = new URLSearchParams(window.location.search);
    const idReprParam = urlParams.get('id_repr');
    
    if (idReprParam) {
        // Chercher l'option qui correspond à l'ID promoteur
        const option = $(`#selPromoteur option[data-id-repr="${idReprParam}"]`);
        if (option.length > 0) {
            $('#selPromoteur').val(option.val());
            currentIdRepr = option.val();
            
            // Mettre à jour le texte et l'URL du bouton "Gérer le planning"
            const selectedText = option.text();
            if (selectedText) {
                const promoterName = selectedText.split(':')[1]?.trim() || '';
                if (promoterName) {
                    const firstName = promoterName.split(' ')[0];
                    $('#btnGererText').text('Gérer le planning de ' + firstName);
                    // Mettre à jour l'URL avec l'ID du promoteur
                    $('#btnGererPlanning').attr('href', '<?php echo URL; ?>Gestion_Secteur?id_repr=' + idReprParam);
                }
            }
            
            goToThisWeek();
        }
    }
    
    // Changement de promoteur
    $('#selPromoteur').on('change', function() {
        currentIdRepr = $(this).val();
        console.log('Promoteur sélectionné:', currentIdRepr);
        
        // Mettre à jour le texte et l'URL du bouton "Gérer le planning"
        const selectedOption = $(this).find('option:selected');
        const selectedText = selectedOption.text();
        const idRepr = selectedOption.data('id-repr');
        
        if (currentIdRepr && selectedText) {
            // Extraire le prénom du promoteur (format: "#069 : Antoine LUQUE")
            const promoterName = selectedText.split(':')[1]?.trim() || '';
            if (promoterName) {
                // Extraire le prénom (premier mot)
                const firstName = promoterName.split(' ')[0];
                $('#btnGererText').text('Gérer le planning de ' + firstName);
                // Mettre à jour l'URL avec l'ID du promoteur
                $('#btnGererPlanning').attr('href', '<?php echo URL; ?>Gestion_Secteur?id_repr=' + idRepr);
            }
        } else {
            $('#btnGererText').text('Gérer le planning');
            $('#btnGererPlanning').attr('href', '<?php echo URL; ?>Gestion_Secteur');
        }
        
        if (currentIdRepr) {
            goToThisWeek();
        } else {
            showEmpty();
        }
    });
    
    // Navigation
    $('#btnPrevWeek').on('click', function() {
        navigateWeek(-7);
    });
    
    $('#btnNextWeek').on('click', function() {
        navigateWeek(7);
    });
    
    $('#btnThisWeek').on('click', function() {
        goToThisWeek();
    });
    
    // Aller à cette semaine
    function goToThisWeek() {
        const today = new Date();
        const dayOfWeek = today.getDay(); // 0 = Dimanche, 6 = Samedi
        
        // Calculer le dimanche de cette semaine
        const sunday = new Date(today);
        sunday.setDate(today.getDate() - dayOfWeek);
        
        // Calculer le samedi
        const saturday = new Date(sunday);
        saturday.setDate(sunday.getDate() + 6);
        
        currentWeekStart = sunday;
        currentWeekEnd = saturday;
        
        loadPlanning();
    }
    
    // Naviguer de N jours
    function navigateWeek(days) {
        if (!currentWeekStart) return;
        
        const newStart = new Date(currentWeekStart);
        newStart.setDate(newStart.getDate() + days);
        
        const newEnd = new Date(currentWeekEnd);
        newEnd.setDate(newEnd.getDate() + days);
        
        currentWeekStart = newStart;
        currentWeekEnd = newEnd;
        
        loadPlanning();
    }
    
    // Charger le planning
    function loadPlanning() {
        if (!currentIdRepr || !currentWeekStart || !currentWeekEnd) return;
        
        showLoader();
        $('#planningNav').show();
        
        // Mise à jour affichage semaine
        updateWeekDisplay();
        
        // Formater les dates
        const from = formatDate(currentWeekStart);
        const to = formatDate(currentWeekEnd);
        
        // Debug : Afficher les paramètres
        console.log('Paramètres envoyés:', {
            id_repr: currentIdRepr,
            from: from,
            to: to
        });
        
        // Debug : Vérifier l'ID promoteur dans le select
        const selectedOption = $('#selPromoteur option:selected');
        console.log('Option sélectionnée:', {
            value: selectedOption.val(), // ID utilisateur
            text: selectedOption.text(),
            dataIdRepr: selectedOption.data('id-repr') // ID promoteur
        });
        
        // Appel AJAX avec gestion spéciale pour éviter les alertes
        $.ajax({
            url: '<?php echo URL; ?>async',
            type: 'POST',
            data: {
                methode: 'planning::getPlanning',
                id_repr: currentIdRepr,
                from: from,
                to: to
            },
            success: function(response) {
                try {
                    // Debug : Afficher la réponse brute
                    console.log('Réponse brute:', response);
                    
                    // Parser la réponse
                    let data;
                    if (typeof response === 'string') {
                        data = JSON.parse(response);
                    } else {
                        data = response;
                    }
                    
                    console.log('Données parsées:', data);
                    
                    // Décoder le message d'erreur s'il existe
                    if (data.errMsg) {
                        data.errMsg = decodeURIComponent(data.errMsg);
                    }
                    
                    // Vérifier si c'est une erreur "Planning vide" spécifique
                    if (data.err && data.errMsg && data.errMsg.includes('Planning vide')) {
                        console.log('Planning vide détecté, affichage calendrier vide');
                        displayCalendar({});
                    } else if (data.err || data.error) {
                        console.log('Autre erreur détectée:', data.errMsg || data.error);
                        displayCalendar({});
                    } else if (data.planning && Object.keys(data.planning).length > 0) {
                        console.log('Planning trouvé:', data.planning);
                        displayCalendar(data.planning);
                    } else {
                        console.log('Pas de planning dans la réponse ou planning vide');
                        console.log('Clés disponibles:', Object.keys(data));
                        displayCalendar({});
                    }
                } catch (e) {
                    console.log('Erreur parsing:', e);
                    console.log('Réponse qui a causé l\'erreur:', response);
                    displayCalendar({});
                }
            },
            error: function(xhr, status, error) {
                // Erreur AJAX, afficher calendrier vide
                console.log('Erreur AJAX, affichage calendrier vide');
                displayCalendar({});
            }
        });
    }
    
    // Afficher le calendrier
    function displayCalendar(planningData) {
        console.log('displayCalendar appelée avec:', planningData);
        
        const days = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
        const hours = [4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15];
        
        // Générer les dates de la semaine
        const weekDates = [];
        for (let i = 0; i < 7; i++) {
            const date = new Date(currentWeekStart);
            date.setDate(date.getDate() + i);
            weekDates.push(date);
        }
        
        console.log('Dates de la semaine générées:', weekDates.map(d => formatDate(d)));
        
        // Vérifier s'il y a des données
        const hasData = planningData && Object.keys(planningData).length > 0;
        console.log('hasData:', hasData);
        
        // Construire le calendrier
        let html = '<div class="calendar-grid">';
        
        // Header avec les jours
        html += '<div class="calendar-header time-col">Heure</div>';
        weekDates.forEach((date, index) => {
            const dateStr = formatDateDisplay(date);
            html += `<div class="calendar-header">${days[index]}<br><small>${dateStr}</small></div>`;
        });
        
        // Créer un index de visites par date pour les répartir sur les lignes
        const visitsByDate = {};
        weekDates.forEach(date => {
            const dateKey = formatDate(date);
            const visits = planningData[dateKey];
            if (visits && Object.keys(visits).length > 0) {
                visitsByDate[dateKey] = Object.values(visits);
            }
        });
        
        // Lignes d'heures
        hours.forEach((hour, hourIndex) => {
            html += `<div class="calendar-time">${hour}h</div>`;
            
            weekDates.forEach(date => {
                const dateKey = formatDate(date);
                const visits = visitsByDate[dateKey] || [];
                
                html += '<div class="calendar-cell">';
                
                // Afficher la visite correspondant à cet index d'heure (si elle existe)
                if (visits[hourIndex]) {
                    const visit = visits[hourIndex];
                    
                    // visit est un objet avec des propriétés numériques
                    const id = visit[0];
                    const statut = visit[1];
                    const raison = visit[2];
                    const nom = visit[3];
                    
                    let className = 'planning-cell ';
                    if (statut == 1) className += 'visite-terminee clickable';
                    else if (statut == 2) className += 'visite-annulee';
                    else className += 'visite-en-attente';
                    
                    let typeVisite = '';
                    if (statut == 2 && raison) {
                        typeVisite = raison;
                    } else if (statut == 0) {
                        typeVisite = 'En attente';
                    } else if (statut == 1) {
                        typeVisite = 'Terminée';
                    }
                    
                    const dataAttr = statut == 1 ? `data-visite-id="${id}"` : '';
                    
                    html += `
                        <div class="${className}" ${dataAttr} title="${nom || 'Magasin'} - ${typeVisite}">
                            <div class="visite-magasin">${truncate(nom || 'Magasin', 20)}</div>
                            <div class="visite-code">#${id || ''}</div>
                            ${typeVisite ? `<div class="visite-type">${typeVisite}</div>` : ''}
                        </div>
                    `;
                }
                
                html += '</div>';
            });
        });
        
        html += '</div>';
        
        // Si pas de données, afficher un message discret sous le calendrier
        if (!hasData) {
            html += `
                <div style="text-align: center; padding: 20px; color: #95a5a6; font-style: italic;">
                    <i class="fas fa-info-circle"></i> Aucune visite planifiée pour cette période
                </div>
            `;
        }
        
        $('#planningContent').html(html);
    }
    
    // Mettre à jour l'affichage de la semaine
    function updateWeekDisplay() {
        if (!currentWeekStart || !currentWeekEnd) return;
        
        const startStr = formatDateDisplay(currentWeekStart);
        const endStr = formatDateDisplay(currentWeekEnd);
        
        $('#currentWeekDisplay').text(`Semaine du ${startStr} au ${endStr}`);
    }
    
    // Afficher le loader
    function showLoader() {
        $('#planningContent').html(`
            <div class="planning-loader">
                <i class="fas fa-spinner"></i>
                <h4>Chargement du planning...</h4>
            </div>
        `);
    }
    
    // Afficher message vide
    function showEmpty(message = 'Sélectionnez un promoteur pour afficher son planning') {
        $('#planningNav').hide();
        $('#planningContent').html(`
            <div class="planning-empty">
                <i class="fas fa-calendar-alt"></i>
                <h4>${message}</h4>
            </div>
        `);
    }
    
    // Formater date pour l'API (YYYY-MM-DD)
    function formatDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }
    
    // Formater date pour l'affichage (DD/MM/YYYY)
    function formatDateDisplay(date) {
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = date.getFullYear();
        return `${day}/${month}/${year}`;
    }
    
    // Tronquer texte
    function truncate(str, max) {
        if (!str) return '';
        return str.length > max ? str.substring(0, max) + '...' : str;
    }
    
    // Gestionnaire de clic sur les visites effectuées
    $(document).on('click', '.planning-cell.clickable', function() {
        const visiteId = $(this).data('visite-id');
        if (visiteId) {
            window.open('<?php echo URL; ?>VisitesJuva/' + visiteId, '_blank');
        }
    });
});
</script>

