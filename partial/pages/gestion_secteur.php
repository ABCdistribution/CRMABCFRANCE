<?php if( !securite::can(25) ) return core::restricted();?>

<!-- CDN Select2 -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card modern-card">
                <!-- Header moderne -->
                <div class="page-header-modern">
                    <div class="header-content">
                        <div class="header-left">
                            <div class="header-icon">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <div class="header-text">
                                <h1 class="page-title">Gestion du Planning des Promoteurs</h1>
                                <p class="page-subtitle">Planifiez et gérez les activités de vos promoteurs affectés</p>
                            </div>
                        </div>
                        <div class="header-right">
                            <div id="promoteurInfo" class="promoteur-badge" style="display: none;">
                                <!-- Informations du promoteur sélectionné -->
                            </div>
                            <div id="promoteurActions" class="header-actions-group" style="display: none;">
                                <button type="button" id="btnSupprimerPlanning" class="btn-delete-planning">
                                    <i class="fas fa-trash-alt"></i>
                                    <span>Supprimer le planning</span>
                                </button>
                                <a href="#" id="btnPlanningComplet" class="btn-planning-complet">
                                    <i class="fas fa-calendar-alt"></i>
                                    <span>Voir le planning complet</span>
                                </a>
                                <button type="button" id="btnImportPlanning" class="btn-import-planning">
                                    <i class="fas fa-file-excel"></i>
                                    <span>Importer planning (CSV)</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Formulaire caché pour l'import CSV -->
                    <form method="post" id="formUploadPlanning" style="display: none;">
                        <input type="file" name="filePlanning" accept=".csv"/>
                    </form>
                    
                    <!-- Sélection du promoteur moderne -->
                    <div class="promoteur-selector-modern">
                        <div class="selector-icon">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="selector-content">
                            <label class="selector-label">Sélectionner un promoteur</label>
                            <select class="select-modern" id="promoteurSelect" name="id_repr" style="width: 100%;">
                                <option value="">-- Choisir un promoteur --</option>
                                <!-- Les promoteurs seront chargés ici -->
                            </select>
                        </div>
                    </div>
                    
                    <!-- Affichage des magasins du promoteur -->
                    <div id="magasinsSection" style="display: none;">
                        <div class="row mb-3">
                            <div class="col-12">
                                <h5 class="mb-0">
                                    <i class="fas fa-building"></i> 
                                    <span id="magasinsTitle">Magasins du promoteur</span>
                                </h5>
                            </div>
                        </div>
                        
                        <!-- Barre de recherche et filtres modernes -->
                        <div class="filters-modern-wrapper">
                            <div class="filters-grid">
                                <!-- Recherche -->
                                <div class="filter-box">
                                    <div class="filter-icon">
                                        <i class="fas fa-search"></i>
                                    </div>
                                    <div class="filter-content">
                                        <label class="filter-label">Rechercher un magasin</label>
                                        <input type="text" 
                                               class="filter-input" 
                                               id="magasinFilter" 
                                               placeholder="Nom, ville ou code...">
                                    </div>
                                </div>

                                <!-- Filtre par jour -->
                                <div class="filter-box">
                                    <div class="filter-icon">
                                        <i class="fas fa-calendar-day"></i>
                                    </div>
                                    <div class="filter-content">
                                        <label class="filter-label">Filtrer par jour</label>
                                        <select class="filter-select" id="jourFilter">
                                            <option value="">Tous les jours</option>
                                            <option value="1">Lundi</option>
                                            <option value="2">Mardi</option>
                                            <option value="3">Mercredi</option>
                                            <option value="4">Jeudi</option>
                                            <option value="5">Vendredi</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Actions et compteur -->
                                <div class="filter-box actions-box">
                                    <div class="counter-box">
                                        <div class="counter-icon">
                                            <i class="fas fa-store"></i>
                                        </div>
                                        <div class="counter-info">
                                            <span class="counter-label">Magasins</span>
                                            <span id="magasinCount" class="counter-number">0</span>
                                        </div>
                                    </div>
                                    <div class="actions-buttons">
                                        <button type="button" class="btn-modern btn-preview" id="btnApercu">
                                            <i class="fas fa-eye"></i>
                                            <span>Aperçu</span>
                                        </button>
                                        <button type="button" class="btn-modern btn-save" id="btnEnregistrer">
                                            <i class="fas fa-save"></i>
                                            <span>Enregistrer</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Liste des magasins sous forme de cartes -->
                        <div id="magasinsList" class="row magasins-grid">
                            <!-- Les cartes de magasins seront générées ici -->
                        </div>
                        
                    </div>
                    
                    <!-- Section d'affichage du planning -->
                    <div class="row mt-5">
                        <div class="col-12">
                            <div class="card" id="planningCard" style="display: none;">
                                <div class="card-header planning-header">
                                    <div class="planning-title">
                                        <i class="fas fa-calendar-check"></i>
                                        <span>Planning des Visites</span>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div id="planningInfo" class="mb-3">
                                        <!-- Informations du promoteur sélectionné -->
                                    </div>
                                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                        <table class="table table-striped table-hover" id="planningTable">
                                            <thead class="thead-dark" style="position: sticky; top: 0; z-index: 10;">
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Semaine</th>
                                                    <th>Magasin</th>
                                                    <th>Statut</th>
                                                </tr>
                                            </thead>
                                            <tbody id="planningTableBody">
                                                <!-- Planning du promoteur sera chargé ici -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* ==========================================
   STYLES MODERNES POUR LE HEADER
   ========================================== */

.modern-card {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    border: 1px solid #e8ecef;
    margin-bottom: 20px;
    border-radius: 12px;
    overflow: hidden;
}

.page-header-modern {
    background: #ffffff;
    padding: 24px 28px;
    border-bottom: 1px solid #e8ecef;
}

.header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 20px;
}

.header-left {
    display: flex;
    align-items: center;
    gap: 16px;
    flex: 1;
}

.header-icon {
    width: 56px;
    height: 56px;
    background: #3498db;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(52, 152, 219, 0.25);
}

.header-icon i {
    color: white;
    font-size: 24px;
}

.header-text {
    flex: 1;
}

.page-title {
    font-size: 24px;
    font-weight: 700;
    color: #2c3e50;
    margin: 0 0 6px 0;
    line-height: 1.2;
}

.page-subtitle {
    font-size: 14px;
    color: #6c757d;
    margin: 0;
    font-weight: 400;
}

.header-right {
    display: flex;
    align-items: center;
    gap: 12px;
}

.header-actions-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.promoteur-badge {
    background: #f8f9fa;
    padding: 10px 16px;
    border-radius: 8px;
    font-size: 14px;
    color: #2c3e50;
    font-weight: 500;
    border: 2px solid #e8ecef;
    display: flex;
    align-items: center;
    gap: 8px;
}

.promoteur-badge i {
    color: #3498db;
    font-size: 16px;
}

.btn-planning-complet,
.btn-import-planning {
    padding: 12px 20px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
}

.btn-planning-complet {
    background: #3498db;
    color: white;
    box-shadow: 0 2px 6px rgba(52, 152, 219, 0.3);
}

.btn-planning-complet:hover {
    background: #2980b9;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(52, 152, 219, 0.4);
    color: white;
    text-decoration: none;
}

.btn-planning-complet:active {
    transform: translateY(0);
}

.btn-planning-complet i {
    font-size: 16px;
}

.btn-import-planning {
    background: #27ae60;
    color: white;
    box-shadow: 0 2px 6px rgba(39, 174, 96, 0.3);
}

.btn-import-planning:hover {
    background: #229954;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(39, 174, 96, 0.4);
}

.btn-import-planning:active {
    transform: translateY(0);
}

.btn-import-planning i {
    font-size: 16px;
}

.btn-delete-planning {
    background: #e74c3c;
    color: white;
    padding: 12px 20px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
    box-shadow: 0 2px 6px rgba(231, 76, 60, 0.3);
}

.btn-delete-planning:hover {
    background: #c0392b;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(231, 76, 60, 0.4);
}

.btn-delete-planning:active {
    transform: translateY(0);
}

.btn-delete-planning i {
    font-size: 16px;
}

/* Sélecteur de promoteur moderne */
.promoteur-selector-modern {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    gap: 16px;
    border: 2px solid transparent;
    transition: all 0.3s ease;
}

.promoteur-selector-modern:hover {
    border-color: #3498db;
    box-shadow: 0 4px 12px rgba(52, 152, 219, 0.15);
}

.selector-icon {
    width: 48px;
    height: 48px;
    background: #3498db;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.selector-icon i {
    color: white;
    font-size: 20px;
}

.selector-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.selector-label {
    font-size: 12px;
    font-weight: 600;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin: 0;
}

.select-modern {
    /* Les styles de Select2 s'appliqueront */
}

/* Responsive Header */
@media (max-width: 992px) {
    .page-header-modern {
        padding: 20px;
    }
    
    .header-content {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .header-right {
        width: 100%;
        flex-direction: column;
        align-items: stretch;
    }
    
    .header-actions-group {
        width: 100%;
    }
    
    .btn-planning-complet,
    .btn-import-planning,
    .btn-delete-planning {
        justify-content: center;
        width: 100%;
    }
}

@media (max-width: 576px) {
    .header-icon {
        width: 48px;
        height: 48px;
    }
    
    .header-icon i {
        font-size: 20px;
    }
    
    .page-title {
        font-size: 20px;
    }
    
    .page-subtitle {
        font-size: 13px;
    }
}

/* Anciens styles - pour compatibilité */
.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid rgba(0, 0, 0, 0.125);
}

.card-title {
    margin-bottom: 0;
    color: #495057;
}

.form-group {
    margin-bottom: 1rem;
}

.form-label {
    font-weight: 600;
    color: #495057;
    margin-bottom: 0.5rem;
}

.form-control {
    border-radius: 0.375rem;
    border: 1px solid #ced4da;
    padding: 0.5rem 0.75rem;
}

.form-control:focus {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.btn {
    border-radius: 0.375rem;
    padding: 0.5rem 1rem;
    font-weight: 500;
}

.btn-success {
    background-color: #28a745;
    border-color: #28a745;
}

.btn-info {
    background-color: #17a2b8;
    border-color: #17a2b8;
}

.btn-secondary {
    background-color: #6c757d;
    border-color: #6c757d;
}

.table th {
    background-color: #343a40;
    color: white;
    border: none;
    font-weight: 600;
}

.table-striped tbody tr:nth-of-type(odd) {
    background-color: rgba(0, 0, 0, 0.05);
}


.text-muted {
    font-size: 0.9rem;
}

.mr-2 {
    margin-right: 0.5rem !important;
}

.mt-3 {
    margin-top: 1rem !important;
}

.mt-4 {
    margin-top: 1.5rem !important;
}

.mt-5 {
    margin-top: 3rem !important;
}

/* Styles pour le titre du planning */
.planning-header {
    background: linear-gradient(135deg, #667eea 0%, #4facfe 100%);
    border: none;
    padding: 1rem 1.5rem;
}

.planning-title {
    display: flex;
    align-items: center;
    color: white;
    font-weight: 600;
    font-size: 1.1rem;
}

.planning-title i {
    margin-right: 0.75rem;
    font-size: 1.3rem;
    color: #ffd700;
}

.planning-title span {
    text-shadow: 0 1px 2px rgba(0,0,0,0.1);
}

/* Styles pour la recherche de promoteurs et magasins */
#promoteurSearch, #magasinSearch {
    position: relative;
}

#promoteurResults, #magasinResults {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    z-index: 1000;
    background: white;
    border: 1px solid #ced4da;
    border-top: none;
    border-radius: 0 0 0.375rem 0.375rem;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.dropdown-item:hover {
    background-color: #f8f9fa;
}

.dropdown-item:active {
    background-color: #e9ecef;
}

/* Styles pour les cartes de magasins */
.magasin-card {
    border: 2px solid #28a745;
    border-radius: 8px;
    padding: 12px;
    margin-bottom: 10px;
    background: #f8f9fa;
    transition: all 0.3s ease;
    height: 220px;
    display: flex;
    flex-direction: column;
    position: relative;
}

.magasin-card:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.magasin-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 10px;
    padding-right: 35px;
    position: relative;
}

.magasin-header > div {
    flex: 1;
    min-width: 0;
}

.magasin-name {
    font-weight: 600;
    color: #495057;
    margin: 0;
    font-size: 0.95rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    width: 100%;
    cursor: help;
}

.magasin-id {
    font-size: 0.8rem;
    color: #6c757d;
    margin: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    width: 100%;
    cursor: help;
}

.jours-buttons {
    display: flex;
    gap: 4px;
    margin-bottom: 8px;
}

.jour-btn {
    padding: 4px 8px;
    border: 1px solid #ced4da;
    background: white;
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.75rem;
    font-weight: 500;
    transition: all 0.2s ease;
}

.jour-btn:hover {
    background: #e9ecef;
}

.jour-btn.selected {
    background: #28a745;
    color: white;
    border-color: #28a745;
}

.jour-btn.existing-planning {
    background: #ffc107;
    color: #212529;
    border-color: #ffc107;
    font-weight: bold;
}

.jour-btn.existing-planning:hover {
    background: #e0a800;
    border-color: #d39e00;
}

.frequency-section {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
}

.frequency-label {
    font-weight: 500;
    color: #495057;
    margin: 0;
    font-size: 0.85rem;
}

.frequency-controls {
    display: flex;
    align-items: center;
    gap: 5px;
}

.frequency-btn {
    width: 30px;
    height: 30px;
    border: 1px solid #ced4da;
    background: white;
    border-radius: 4px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
}

.frequency-btn:hover {
    background: #e9ecef;
}

.frequency-input {
    width: 50px;
    text-align: center;
    border: 1px solid #ced4da;
    border-radius: 4px;
    padding: 5px;
}

.week-section {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 5px;
}

.week-controls {
    display: flex;
    align-items: center;
    gap: 5px;
}

.magasin-actions {
    margin-top: auto;
    text-align: center;
}

.btn-supprimer-planning {
    position: absolute;
    top: 10px;
    right: 10px;
    width: 30px;
    height: 30px;
    border-radius: 4px;
    background-color: #ffebee;
    color: #dc3545;
    border: 2px solid #dc3545;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    padding: 0;
    z-index: 10;
}

.btn-supprimer-planning:hover {
    background-color: #ffcdd2;
    color: #dc3545;
    transform: scale(1.1);
}

.magasin-card.editing-planning {
    border: 2px solid #007bff;
    box-shadow: 0 0 10px rgba(0, 123, 255, 0.3);
    background: linear-gradient(135deg, #f8f9ff, #e3f2fd);
}

.magasin-card.editing-planning .magasin-header {
    background: transparent;
    color: #007bff;
    border-radius: 6px 6px 0 0;
    font-weight: bold;
}

.magasin-card.editing-planning .jour-btn {
    cursor: pointer;
    transition: all 0.2s ease;
}

.magasin-card.editing-planning .jour-btn:hover {
    transform: scale(1.05);
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.magasin-card.editing-planning .jour-btn.selected {
    background: #28a745 !important;
    color: white !important;
    border-color: #28a745 !important;
    font-weight: bold;
    box-shadow: 0 2px 4px rgba(40, 167, 69, 0.3);
}

.magasin-card.editing-planning .jour-btn:not(.selected) {
    background: #f8f9fa;
    color: #6c757d;
    border-color: #dee2e6;
}

.magasin-card.editing-planning .jour-btn:not(.selected):hover {
    background: #e9ecef;
    color: #495057;
    border-color: #adb5bd;
}

.magasin-card.modified {
    border: 2px solid #28a745;
    box-shadow: 0 0 10px rgba(40, 167, 69, 0.3);
}

.magasin-card.no-planning {
    border: 2px solid #dc3545 !important;
    box-shadow: 0 0 10px rgba(220, 53, 69, 0.3) !important;
}

.week-label {
    font-weight: 500;
    color: #495057;
    margin: 0;
}

.week-controls {
    display: flex;
    align-items: center;
    gap: 5px;
}

.week-input {
    width: 60px;
    text-align: center;
    border: 1px solid #ced4da;
    border-radius: 4px;
    padding: 5px;
}

.year-input {
    width: 80px;
    text-align: center;
    border: 1px solid #ced4da;
    border-radius: 4px;
    padding: 5px;
}

/* ==========================================
   STYLES MODERNES POUR LES FILTRES - SANS DÉGRADÉS
   ========================================== */

.filters-modern-wrapper {
    background: #ffffff;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 25px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    border: 1px solid #e8ecef;
}

.filters-grid {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 16px;
}

.filter-box {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 16px;
    display: flex;
    align-items: center;
    gap: 14px;
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.filter-box:hover {
    border-color: #3498db;
    box-shadow: 0 4px 12px rgba(52, 152, 219, 0.15);
}

.filter-icon {
    width: 44px;
    height: 44px;
    background: #3498db;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: all 0.3s ease;
}

.filter-box:hover .filter-icon {
    transform: scale(1.05);
}

.filter-icon i {
    color: white;
    font-size: 18px;
}

.filter-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.filter-label {
    font-size: 12px;
    font-weight: 600;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin: 0;
}

.filter-input,
.filter-select {
    border: none;
    background: white;
    padding: 10px 12px;
    border-radius: 8px;
    font-size: 14px;
    color: #2c3e50;
    font-weight: 500;
    outline: none;
    transition: all 0.3s ease;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
}

.filter-input:focus,
.filter-select:focus {
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
    background: #ffffff;
}

.filter-input::placeholder {
    color: #adb5bd;
    font-weight: 400;
}

.filter-select {
    cursor: pointer;
}

/* Box d'actions */
.actions-box {
    flex-direction: column;
    gap: 12px;
    padding: 12px;
}

.counter-box {
    background: white;
    border-radius: 8px;
    padding: 10px 14px;
    display: flex;
    align-items: center;
    gap: 12px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
}

.counter-icon {
    width: 36px;
    height: 36px;
    background: #e74c3c;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.counter-icon i {
    color: white;
    font-size: 16px;
}

.counter-info {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.counter-label {
    font-size: 10px;
    font-weight: 600;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.counter-number {
    font-size: 20px;
    font-weight: 700;
    color: #2c3e50;
    line-height: 1;
    transition: transform 0.2s ease;
}

.actions-buttons {
    display: flex;
    gap: 8px;
}

.btn-modern {
    flex: 1;
    padding: 10px 16px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    font-size: 13px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.3s ease;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
}

.btn-modern i {
    font-size: 14px;
}

.btn-modern:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.btn-modern:active {
    transform: translateY(0);
}

.btn-preview {
    background: #3498db;
    color: white;
}

.btn-preview:hover {
    background: #2980b9;
}

.btn-save {
    background: #27ae60;
    color: white;
}

.btn-save:hover {
    background: #229954;
}

/* Responsive */
@media (max-width: 1200px) {
    .filters-grid {
        grid-template-columns: 1fr 1fr;
    }
    
    .actions-box {
        grid-column: 1 / -1;
        flex-direction: row;
        padding: 16px;
    }
    
    .counter-box {
        flex: 1;
    }
    
    .actions-buttons {
        flex: 1;
    }
}

@media (max-width: 768px) {
    .filters-grid {
        grid-template-columns: 1fr;
    }
    
    .actions-box {
        flex-direction: column;
    }
    
    .actions-buttons {
        width: 100%;
    }
    
    .btn-modern {
        width: 100%;
    }
}

/* Animation du compteur */
@keyframes countPulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

.counter-number.pulse {
    animation: countPulse 0.3s ease;
}

/* ==========================================
   STYLES POUR LE MODAL D'APERÇU MODERNE
   ========================================== */

.modal-modern {
    border-radius: 12px;
    overflow: hidden;
    border: none;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
}

.modal-header-blue {
    background: #3498db;
    color: white;
    padding: 20px 24px;
    border-bottom: none;
}

.modal-header-red {
    background: #e74c3c;
    color: white;
    padding: 20px 24px;
    border-bottom: none;
}

.modal-header-success {
    background: #27ae60;
    color: white;
    padding: 20px 24px;
    border-bottom: none;
}

.modal-header-content {
    display: flex;
    align-items: center;
    gap: 12px;
    flex: 1;
}

.modal-icon {
    width: 40px;
    height: 40px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-icon i {
    font-size: 18px;
    color: white;
}

.modal-header-blue .modal-title {
    font-size: 18px;
    font-weight: 700;
    color: white;
    margin: 0;
}

.modal-close-white {
    color: white;
    opacity: 0.9;
    text-shadow: none;
    font-size: 28px;
    font-weight: 300;
}

.modal-close-white:hover {
    opacity: 1;
    color: white;
}

.modal-body-modern {
    padding: 24px;
    background: #ffffff;
}

.apercu-content {
    white-space: pre-wrap;
    font-family: 'Courier New', monospace;
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    margin: 0;
    border: 2px solid #e8ecef;
    color: #2c3e50;
    font-size: 13px;
    line-height: 1.6;
}

.modal-footer-modern {
    padding: 16px 24px;
    background: #f8f9fa;
    border-top: 1px solid #e8ecef;
}

.btn-modal-close {
    background: #6c757d;
    color: white;
    padding: 10px 20px;
    border-radius: 8px;
    border: none;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
}

.btn-modal-close:hover {
    background: #5a6268;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.btn-modal-close i {
    font-size: 14px;
}

.btn-modal-confirm-delete {
    background: #e74c3c;
    color: white;
    padding: 10px 20px;
    border-radius: 8px;
    border: none;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
}

.btn-modal-confirm-delete:hover {
    background: #c0392b;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(231, 76, 60, 0.3);
}

.btn-modal-confirm-delete i {
    font-size: 14px;
}

.btn-modal-success {
    background: #27ae60;
    color: white;
    padding: 10px 20px;
    border-radius: 8px;
    border: none;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
}

.btn-modal-success:hover {
    background: #229954;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(39, 174, 96, 0.3);
}

.btn-modal-success i {
    font-size: 14px;
}

/* Animation du modal */
.modal.fade .modal-dialog {
    transition: transform 0.3s ease-out;
}

.modal.show .modal-dialog {
    transform: none;
}

/* Animation pour les cartes de magasins */
.magasin-card {
    animation: slideInUp 0.3s ease-out;
}

@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Styles personnalisés pour Select2 */
.select2-container--default .select2-selection--single {
    height: 38px !important;
    border: 1px solid #ced4da !important;
    border-radius: 0.375rem !important;
    background-color: #fff !important;
    transition: all 0.3s ease !important;
}

.select2-container--default .select2-selection--single:hover {
    border-color: #80bdff !important;
}

.select2-container--default.select2-container--focus .select2-selection--single {
    border-color: #80bdff !important;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25) !important;
}

.select2-container--default .select2-selection--single .select2-selection__rendered {
    color: #495057 !important;
    line-height: 36px !important;
    padding-left: 12px !important;
    padding-right: 20px !important;
}

.select2-container--default .select2-selection--single .select2-selection__placeholder {
    color: #6c757d !important;
}

.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 36px !important;
    right: 8px !important;
}

.select2-container--default .select2-selection--single .select2-selection__arrow b {
    border-color: #6c757d transparent transparent transparent !important;
    border-width: 5px 4px 0 4px !important;
    margin-top: -2px !important;
}

.select2-dropdown {
    border: 1px solid #ced4da !important;
    border-radius: 0.375rem !important;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
    margin-top: 2px !important;
}

.select2-container--default .select2-search--dropdown .select2-search__field {
    border: 1px solid #ced4da !important;
    border-radius: 0.25rem !important;
    padding: 6px 8px !important;
    margin: 4px !important;
    width: calc(100% - 16px) !important;
}

.select2-container--default .select2-search--dropdown .select2-search__field:focus {
    border-color: #80bdff !important;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25) !important;
    outline: 0 !important;
}

.select2-container--default .select2-results__option {
    padding: 8px 12px !important;
    color: #495057 !important;
    transition: background-color 0.2s ease !important;
}

.select2-container--default .select2-results__option--highlighted[aria-selected] {
    background-color: #e9ecef !important;
    color: #495057 !important;
}

.select2-container--default .select2-results__option[aria-selected=true] {
    background-color: #007bff !important;
    color: white !important;
}

.select2-container--default .select2-results__option[aria-selected=true]:hover {
    background-color: #0056b3 !important;
}

.select2-container--default .select2-results__option--no-results {
    color: #6c757d !important;
    font-style: italic !important;
}

.select2-container--default .select2-results__option--searching {
    color: #6c757d !important;
    font-style: italic !important;
}

/* Animation pour l'ouverture du dropdown */
.select2-dropdown {
    animation: fadeInDown 0.2s ease-out !important;
}

@keyframes fadeInDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Style pour le label du select */
#promoteurSelect + .select2-container {
    margin-top: 0.5rem;
}

/* Style pour l'affichage du promoteur sélectionné */
.promoteur-info-display {
    height: 38px;
    line-height: 36px;
    padding: 0 12px;
    background-color: #d1ecf1;
    border: 1px solid #bee5eb;
    border-radius: 0.375rem;
    color: #0c5460;
    font-size: 0.875rem;
    display: flex;
    align-items: center;
    transition: all 0.3s ease;
}

/* Espace après l'icône utilisateur */
.promoteur-info-display i {
    margin-right: 8px;
}

/* Style pour le bouton de planning - supprimé car déjà géré par .btn-planning-complet */


.promoteur-info-display:hover {
    background-color: #c6e8f0;
    border-color: #b8daff;
}

/* Grille des magasins avec espacement réduit */
.magasins-grid {
    margin-left: -5px;
    margin-right: -5px;
}

.magasins-grid > [class*='col-'] {
    padding-left: 5px;
    padding-right: 5px;
}

/* Responsive */
@media (max-width: 768px) {
    .select2-container--default .select2-selection--single {
        height: 42px !important;
    }
    
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 40px !important;
    }
    
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 40px !important;
    }
    
    .promoteur-info-display {
        height: 42px;
        line-height: 40px;
    }
    
    /* Cartes de magasins sur mobile */
    .magasin-card {
        height: auto !important;
        min-height: 220px;
    }
    
    .col-md-6, .col-lg-3 {
        margin-bottom: 10px;
    }
}

/* Correction pour les écrans d'ordinateur */
@media (min-width: 769px) {
    .container-fluid {
        max-width: 100%;
        padding-left: 15px;
        padding-right: 15px;
    }
    
    .card {
        overflow: hidden;
    }
    
    .card-body {
        overflow-x: auto;
    }
    
    /* Ajustement des colonnes pour éviter le débordement */
    .col-md-6 {
        flex: 0 0 50%;
        max-width: 50%;
    }
    
    .col-lg-3 {
        flex: 0 0 25%;
        max-width: 25%;
    }
    
    /* Cartes de magasins sur desktop */
    .magasin-card {
        height: 220px;
        overflow: hidden;
    }
    
    /* Ajustement des contrôles dans les cartes */
    .jours-buttons {
        flex-wrap: wrap;
        gap: 3px;
    }
    
    .jour-btn {
        flex: 0 0 calc(20% - 3px);
        font-size: 0.75rem;
        padding: 3px 6px;
    }
    
    .frequency-section {
        flex-wrap: wrap;
        gap: 5px;
    }
    
    .week-section {
        gap: 3px;
    }
    
    .week-controls {
        flex-wrap: wrap;
        gap: 3px;
    }
    
    .week-input, .year-input {
        width: 45px;
        font-size: 0.8rem;
    }
    
    .frequency-input {
        width: 40px;
        font-size: 0.8rem;
    }
}
</style>

<script>
$(document).ready(function() {
    // Initialisation de la date de passage à aujourd'hui
    $('#datePassage').val(new Date().toISOString().split('T')[0]);
    
    // Empêcher la sélection de dates antérieures à aujourd'hui
    $('#datePassage').attr('min', new Date().toISOString().split('T')[0]);
    
    // Initialiser Select2 pour le select des promoteurs
    $('#promoteurSelect').select2({
        placeholder: 'Choisir un promoteur...',
        allowClear: false,
        width: '100%',
        minimumResultsForSearch: 0,
        language: {
            noResults: function() {
                return "Aucun promoteur trouvé";
            },
            searching: function() {
                return "Recherche en cours...";
            },
            inputTooShort: function() {
                return "Tapez au moins 1 caractère";
            },
            inputTooLong: function() {
                return "Tapez moins de caractères";
            },
            loadingMore: function() {
                return "Chargement...";
            },
            maximumSelected: function() {
                return "Vous ne pouvez sélectionner qu'un seul promoteur";
            }
        },
        templateResult: function(promoteur) {
            if (promoteur.loading) {
                return promoteur.text;
            }
            
            var $container = $(
                '<div class="select2-result-item">' +
                    '<div class="select2-result-item__title">' + promoteur.text + '</div>' +
                '</div>'
            );
            
            return $container;
        },
        templateSelection: function(promoteur) {
            return promoteur.text;
        }
    });
    
    // Charger la liste des promoteurs au démarrage
    chargerListePromoteurs();
    
    
    // Gestionnaire pour la sélection d'un promoteur
    $('#promoteurSelect').on('change', function() {
        console.log('Changement de promoteur détecté');
        var selectedOption = $(this).find('option:selected');
        var idRepr = selectedOption.val();
        var nomPromoteur = selectedOption.text();
        
        console.log('ID Repr sélectionné:', idRepr, 'Nom:', nomPromoteur);
        
        // Vérifier que le sélecteur est toujours visible
        var $select = $('#promoteurSelect');
        console.log('Sélecteur visible:', $select.is(':visible'));
        console.log('Sélecteur display:', $select.css('display'));
        console.log('Sélecteur parent visible:', $select.parent().is(':visible'));
        
        // Réinitialiser tous les filtres
        $('#magasinFilter').val('');
        $('#jourFilter').val('');
        
        if (idRepr && idRepr !== '') {
            chargerMagasinsPromoteur(idRepr, nomPromoteur);
            // Afficher le bouton de planning
            $('#promoteurActions').show();
            // Mettre à jour le lien du bouton avec l'ID représentant
            $('#btnPlanningComplet').attr('href', '<?php echo URL; ?>Planning_View?id_repr=' + idRepr);
        } else {
            // Masquer les sections si aucun promoteur sélectionné
            $('#magasinsSection').hide();
            $('#promoteurInfo').hide();
            $('#promoteurActions').hide();
        }
        
        // Vérifier à nouveau après le traitement
        setTimeout(function() {
            console.log('Après traitement - Sélecteur visible:', $select.is(':visible'));
            console.log('Après traitement - Sélecteur display:', $select.css('display'));
            
            // Si le sélecteur n'est pas visible, le forcer à être visible
            if (!$select.is(':visible') || $select.css('display') === 'none') {
                console.log('Sélecteur caché détecté - correction en cours...');
                $select.show().css('display', 'block');
                $select.parent().show();
                $select.parent().parent().show();
                
                // Réinitialiser Select2 si nécessaire
                if ($select.hasClass('select2-hidden-accessible')) {
                    $select.select2('destroy');
                    $select.select2({
                        placeholder: 'Choisir un promoteur...',
                        allowClear: false,
                        width: '100%',
                        minimumResultsForSearch: 0,
                        language: {
                            noResults: function() {
                                return "Aucun résultat trouvé";
                            }
                        }
                    });
                }
            }
        }, 100);
    });
    
    // Gestion du bouton "Supprimer le planning"
    $('#btnSupprimerPlanning').on('click', function() {
        var idRepr = $('#promoteurSelect').val();
        if (!idRepr) {
            alert('Veuillez d\'abord sélectionner un promoteur');
            return;
        }
        
        var promoteurText = $('#promoteurSelect option:selected').text();
        var promoteurName = promoteurText.split(':')[1]?.trim() || 'ce promoteur';
        
        // Créer le modal de confirmation moderne
        var confirmModalHtml = `
            <div class="modal fade" id="confirmDeleteModal" tabindex="-1" role="dialog">
                <div class="modal-dialog modal-md" role="document">
                    <div class="modal-content modal-modern">
                        <div class="modal-header modal-header-red">
                            <div class="modal-header-content">
                                <div class="modal-icon">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </div>
                                <h5 class="modal-title">Confirmer la suppression</h5>
                            </div>
                            <button type="button" class="close-modal" data-dismiss="modal">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="modal-body modal-body-modern">
                            <div class="alert alert-danger" style="border-left: 4px solid #c0392b; margin-bottom: 20px;">
                                <strong><i class="fas fa-exclamation-circle"></i> ATTENTION</strong>
                                <p style="margin: 10px 0 0 0;">Cette action est <strong>irréversible</strong> !</p>
                            </div>
                            <p style="font-size: 15px; line-height: 1.6;">
                                Voulez-vous vraiment supprimer tout le planning futur de <strong>${promoteurName}</strong> ?
                            </p>
                            <p style="color: #666; margin-top: 15px;">
                                Cette action supprimera :
                            </p>
                            <ul style="color: #666;">
                                <li>Toutes les visites planifiées à partir d'aujourd'hui</li>
                                <li>Toutes les plannifications récurrentes</li>
                            </ul>
                        </div>
                        <div class="modal-footer modal-footer-modern">
                            <button type="button" class="btn-modal-close" data-dismiss="modal">
                                <i class="fas fa-times"></i> Annuler
                            </button>
                            <button type="button" class="btn-modal-confirm-delete" id="confirmDeleteBtn">
                                <i class="fas fa-trash-alt"></i> Supprimer
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Supprimer l'ancien modal s'il existe
        $('#confirmDeleteModal').remove();
        
        // Ajouter le nouveau modal
        $('body').append(confirmModalHtml);
        
        // Afficher le modal
        $('#confirmDeleteModal').modal('show');
        
        // Gestionnaire du bouton de confirmation
        $('#confirmDeleteBtn').off('click').on('click', function() {
            // Fermer le modal
            $('#confirmDeleteModal').modal('hide');
            
            // Exécuter la suppression
            executerSuppressionPlanning(idRepr, promoteurName);
        });
    });
    
    // Fonction pour exécuter la suppression
    function executerSuppressionPlanning(idRepr, promoteurName) {
        
        // Afficher un loader
        var $btn = $('#btnSupprimerPlanning');
        var originalHtml = $btn.html();
        $btn.html('<i class="fas fa-spinner fa-spin"></i> <span>Suppression...</span>');
        $btn.prop('disabled', true);
        
        // Appel AJAX
        $.ajax({
            url: '<?php echo URL; ?>async',
            method: 'POST',
            data: {
                methode: 'user::supprimerPlanningPromoteur',
                id_repr: idRepr
            },
            success: function(response) {
                $btn.html(originalHtml);
                $btn.prop('disabled', false);
                
                try {
                    var data = typeof response === 'string' ? JSON.parse(response) : response;
                    
                    if (data.err || data.error) {
                        alert('Erreur lors de la suppression : ' + (data.errMsg || 'Erreur inconnue'));
                    } else {
                        // Modal de succès
                        var successModalHtml = `
                            <div class="modal fade" id="successDeleteModal" tabindex="-1" role="dialog">
                                <div class="modal-dialog modal-md" role="document">
                                    <div class="modal-content modal-modern">
                                        <div class="modal-header modal-header-success">
                                            <div class="modal-header-content">
                                                <div class="modal-icon">
                                                    <i class="fas fa-check-circle"></i>
                                                </div>
                                                <h5 class="modal-title">Suppression réussie</h5>
                                            </div>
                                            <button type="button" class="close-modal" data-dismiss="modal">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                        <div class="modal-body modal-body-modern">
                                            <p style="font-size: 15px; line-height: 1.6;">
                                                Le planning futur de <strong>${promoteurName}</strong> a été supprimé avec succès.
                                            </p>
                                            <div style="background: #d4edda; border-left: 4px solid #28a745; padding: 12px; margin-top: 15px; border-radius: 4px;">
                                                <strong>✓ ${data.count || 0}</strong> entrée(s) de planning supprimée(s)<br>
                                                <strong>✓ ${data.plannifications || 0}</strong> plannification(s) récurrente(s) supprimée(s)
                                            </div>
                                        </div>
                                        <div class="modal-footer modal-footer-modern">
                                            <button type="button" class="btn-modal-success" data-dismiss="modal">
                                                <i class="fas fa-check"></i> OK
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                        
                        $('#successDeleteModal').remove();
                        $('body').append(successModalHtml);
                        $('#successDeleteModal').modal('show');
                        
                        // Recharger les magasins après fermeture du modal
                        $('#successDeleteModal').on('hidden.bs.modal', function() {
                            chargerMagasinsPromoteur(idRepr, promoteurName);
                        });
                    }
                } catch(e) {
                    alert('Erreur lors du traitement de la réponse');
                    console.error(e);
                }
            },
            error: function(xhr, status, error) {
                $btn.html(originalHtml);
                $btn.prop('disabled', false);
                alert('Erreur lors de la suppression du planning');
                console.error(error);
            }
        });
    }
    
    // Gestion du bouton "Importer planning (CSV)"
    $('#btnImportPlanning').on('click', function() {
        $('input[name=filePlanning]').click();
    });
    
    // Gestion de l'upload du fichier CSV
    $(document).on('change', 'input[name=filePlanning]', function() {
        if (this.files.length === 0) return;
        
        // Afficher un loader
        var $btn = $('#btnImportPlanning');
        var originalText = $btn.html();
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> <span>Import en cours...</span>');
        
        let formData = new FormData($("#formUploadPlanning").get(0));
        formData.append('methode', 'planning::importFileFromUpload');
        
        $("#formUploadPlanning").get(0).reset();
        
        $.ajax({
            url: '<?php echo URL; ?>async',
            type: 'POST',
            dataType: 'json',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                if (response.err == true) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erreur',
                        text: decodeURIComponent(response.errMsg),
                    });
                    $btn.prop('disabled', false).html(originalText);
                    return false;
                }
                
                let successCount = response.total - response.errTotal;
                let message = successCount + ' ligne(s) importée(s) avec succès';
                
                if (response.errTotal > 0) {
                    message += '\n' + response.errTotal + ' erreur(s)';
                }
                
                Swal.fire({
                    icon: 'success',
                    title: 'Import réussi',
                    html: message.replace(/\n/g, '<br>'),
                }).then(function() {
                    // Recharger les magasins si un promoteur est sélectionné
                    var idRepr = $('#promoteurSelect').val();
                    if (idRepr) {
                        location.reload();
                    }
                });
                
                $btn.prop('disabled', false).html(originalText);
            },
            error: function(xhr, status, error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: 'Erreur lors de l\'import du fichier CSV',
                });
                $btn.prop('disabled', false).html(originalText);
            }
        });
    });
    
    // Gestion de la périodicité
    $('input[name="periodicite"]').on('change', function() {
        if ($(this).val() === 'recurrent') {
            $('#joursSemaine').show();
        } else {
            $('#joursSemaine').hide();
            // Décocher tous les jours
            $('input[name="jours[]"]').prop('checked', false);
        }
    });
    
    // Gestion du formulaire
    $('#planningForm').on('submit', function(e) {
        e.preventDefault();
        
        // Validation des champs requis
        var idRepr = $('#promoteurId').val();
        var datePassage = $('#datePassage').val();
        var idMagasin = $('#magasinId').val();
        var periodicite = $('input[name="periodicite"]:checked').val();
        
        if (!idRepr || !datePassage || !idMagasin) {
            alert('Veuillez remplir tous les champs obligatoires');
            return;
        }
        
        // Validation pour la périodicité récurrente
        if (periodicite === 'recurrent') {
            var joursSelectionnes = $('input[name="jours[]"]:checked').length;
            if (joursSelectionnes === 0) {
                alert('Veuillez sélectionner au moins un jour de la semaine pour la périodicité récurrente');
                return;
            }
        }
        
        // Enregistrement du planning
        var joursSelectionnes = [];
        if (periodicite === 'recurrent') {
            $('input[name="jours[]"]:checked').each(function() {
                joursSelectionnes.push(parseInt($(this).val()));
            });
        }
        
        // Préparer les données à envoyer
        var formData = {
            methode: 'user::savePlanning',
            id_repr: idRepr,
            id_magasin: idMagasin,
            date_passage: datePassage,
            periodicite: periodicite,
            jours: joursSelectionnes
        };
        
        // Envoyer les données via AJAX
        $.ajax({
            url: '<?php echo URL; ?>async',
            method: 'POST',
            data: formData,
            success: function(response) {
                try {
                    var data = JSON.parse(response);
                    if (data.success) {
                        alert(data.message + ' (' + data.count + ' planning(s) créé(s))');
                        // Recharger le planning du promoteur
                        chargerPlanningPromoteur(idRepr, $('#promoteurSearch').val().split(' (')[0]);
                    } else {
                        alert('Erreur: ' + data.message);
                    }
                } catch (e) {
                    console.error('Erreur parsing JSON:', e);
                    alert('Erreur lors de l\'enregistrement');
                }
            },
            error: function() {
                console.error('Erreur AJAX');
                alert('Erreur lors de l\'enregistrement');
            }
        });
        
        // Réinitialiser le formulaire
        this.reset();
        $('#datePassage').val(new Date().toISOString().split('T')[0]);
    });
    
    // Bouton annuler
    $('#btnAnnuler').on('click', function() {
        $('#planningForm')[0].reset();
        $('#datePassage').val(new Date().toISOString().split('T')[0]);
    });
    
    
    
    // Recherche de magasins avec autocomplétion
    var searchTimeout;
    $('#magasinSearch').on('input', function() {
        var searchTerm = $(this).val();
        
        // Effacer le timeout précédent
        clearTimeout(searchTimeout);
        
        if (searchTerm.length < 2) {
            $('#magasinResults').hide().empty();
            $('#magasinId').val('');
            return;
        }
        
        // Attendre 300ms avant de faire la recherche
        searchTimeout = setTimeout(function() {
            $.ajax({
                url: '<?php echo URL; ?>async',
                method: 'POST',
                data: {
                    methode: 'ref::searchClient',
                    search: searchTerm
                },
                success: function(response) {
                    try {
                        var data = JSON.parse(response);
                        displayMagasinResults(data);
                    } catch (e) {
                        console.error('Erreur parsing JSON recherche magasins:', e);
                        $('#magasinResults').hide().empty();
                    }
                },
                error: function() {
                    console.error('Erreur recherche magasins');
                    $('#magasinResults').hide().empty();
                }
            });
        }, 300);
    });
    
    function displayMagasinResults(results) {
        var $results = $('#magasinResults');
        $results.empty();
        
        if (results && results.length > 0) {
            results.forEach(function(magasin) {
                var $item = $('<div class="dropdown-item" style="cursor: pointer; padding: 8px 12px;">' + 
                             magasin.libelle + ' (ID: ' + magasin.id + ')</div>');
                $item.on('click', function() {
                    $('#magasinSearch').val(magasin.libelle);
                    $('#magasinId').val(magasin.id);
                    $results.hide();
                });
                $results.append($item);
            });
            $results.show();
        } else {
            $results.hide();
        }
    }
    
    // Masquer les résultats quand on clique ailleurs
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#magasinSearch, #magasinResults').length) {
            $('#magasinResults').hide();
        }
    });
    
    // Gestionnaire pour la recherche de magasins
    var magasinFilterTimeout;
    $('#magasinFilter').on('input', function() {
        var searchTerm = $(this).val();
        
        // Effacer le timeout précédent
        clearTimeout(magasinFilterTimeout);
        
        // Attendre 300ms avant de faire la recherche (debounce)
        magasinFilterTimeout = setTimeout(function() {
            filtrerMagasins(searchTerm, $('#jourFilter').val());
        }, 300);
    });
    
    // Gestionnaire pour le filtre par jour
    $('#jourFilter').on('change', function() {
        var jourSelectionne = $(this).val();
        var searchTerm = $('#magasinFilter').val();
        
        // Réinitialiser l'affichage avant de filtrer
        afficherTousLesMagasins();
        
        // Appliquer le filtre
        filtrerMagasins(searchTerm, jourSelectionne);
    });
    
    // Effacer la recherche avec Escape
    $('#magasinFilter').on('keydown', function(e) {
        if (e.keyCode === 27) { // Escape
            $(this).val('');
            filtrerMagasins('');
        }
    });
    
    // Gestionnaires d'événements pour les cartes de magasins
    $(document).on('click', '.jour-btn', function() {
        var $btn = $(this);
        var $card = $btn.closest('.magasin-card');
        
        // Si c'est un planning existant et qu'on n'est pas en mode modification
        if ($btn.hasClass('existing-planning') && !$card.hasClass('editing-planning')) {
            // Décocher le jour cliqué et activer le mode modification
            $btn.removeClass('selected');
            activerModeModification($card);
        } else {
            // Comportement normal : toggle selection
            $btn.toggleClass('selected');
            // Marquer la carte comme modifiée
            $card.data('modified', true);
            $card.addClass('modified');
        }
    });
    
    $(document).on('click', '.frequency-btn[data-action="decrease"]', function() {
        var input = $(this).siblings('.frequency-input');
        var value = parseInt(input.val());
        if (value > 1) {
            input.val(value - 1);
            // Marquer la carte comme modifiée
            var $card = $(this).closest('.magasin-card');
            $card.data('modified', true);
            $card.addClass('modified');
        }
    });
    
    $(document).on('click', '.frequency-btn[data-action="increase"]', function() {
        var input = $(this).siblings('.frequency-input');
        var value = parseInt(input.val());
        if (value < 8) {
            input.val(value + 1);
            // Marquer la carte comme modifiée
            var $card = $(this).closest('.magasin-card');
            $card.data('modified', true);
            $card.addClass('modified');
        }
    });
    
    $(document).on('click', '.frequency-btn[data-action="decrease-week"]', function() {
        var input = $(this).siblings('.week-input');
        var value = parseInt(input.val());
        if (value > 1) {
            input.val(value - 1);
            // Marquer la carte comme modifiée
            var $card = $(this).closest('.magasin-card');
            $card.data('modified', true);
            $card.addClass('modified');
        }
    });
    
    $(document).on('click', '.frequency-btn[data-action="increase-week"]', function() {
        var input = $(this).siblings('.week-input');
        var value = parseInt(input.val());
        if (value < 53) {
            input.val(value + 1);
            // Marquer la carte comme modifiée
            var $card = $(this).closest('.magasin-card');
            $card.data('modified', true);
            $card.addClass('modified');
        }
    });
    
    // Gestion des changements dans les champs de saisie
    $(document).on('change input', '.frequency-input, .week-input, .year-input', function() {
        var $card = $(this).closest('.magasin-card');
        $card.data('modified', true);
        $card.addClass('modified');
    });
    
    // Gestion du clic sur le bouton de suppression de planning
    $(document).on('click', '.btn-supprimer-planning', function() {
        var $card = $(this).closest('.magasin-card');
        var idRepr = $('#promoteurSelect').val();
        var idMagasin = $card.data('magasin-id');
        var plannings = $card.data('existing-plannings') || [];
        
        console.log('Suppression planning:', {idRepr, idMagasin, plannings});
        console.log('Carte trouvée:', $card.length);
        console.log('Bouton supprimer cliqué');
        console.log('Plannings dans la carte:', plannings);
        console.log('Type de plannings:', typeof plannings);
        console.log('Longueur plannings:', plannings ? plannings.length : 'undefined');
        
        if (plannings && plannings.length > 0) {
            // Récupérer les paramètres de la carte (semaine, année, fréquence)
            var week = parseInt($card.find('.week-input').val());
            var year = parseInt($card.find('.year-input').val());
            var frequency = parseInt($card.find('.frequency-input').val());
            
            console.log('Paramètres de suppression:', {week, year, frequency});
            console.log('Plannings disponibles:', plannings);
            
            // Chercher le planning correspondant aux paramètres sélectionnés
            var planningToDelete = null;
            for (var i = 0; i < plannings.length; i++) {
                var planning = plannings[i];
                console.log('Vérification planning:', {
                    planning_start: planning.start,
                    planning_annee: planning.annee,
                    planning_rec: planning.rec,
                    carte_week: week,
                    carte_year: year,
                    carte_frequency: frequency
                });
                
                // Comparaison flexible des types
                if (parseInt(planning.start) == week && parseInt(planning.annee) == year && parseInt(planning.rec) == frequency) {
                    planningToDelete = planning;
                    console.log('Planning trouvé avec paramètres exacts');
                    break;
                }
            }
            
            // Si aucun planning trouvé avec les paramètres exacts, prendre le premier disponible
            if (!planningToDelete && plannings.length > 0) {
                planningToDelete = plannings[0];
                console.log('Aucun planning trouvé avec les paramètres exacts, utilisation du premier planning disponible');
                // Utiliser les paramètres du planning existant au lieu de ceux de l'interface
                week = parseInt(planningToDelete.start);
                year = parseInt(planningToDelete.annee);
                frequency = parseInt(planningToDelete.rec);
                console.log('Paramètres ajustés:', {week, year, frequency});
            }
            
            if (planningToDelete) {
                console.log('Planning à supprimer:', planningToDelete);
                console.log('Paramètres de suppression:', {week, year, frequency});
                supprimerPlanningExistant(idRepr, idMagasin, week, year, frequency);
            } else {
                console.log('Aucun planning trouvé dans la carte');
                alert('Aucun planning trouvé pour ce magasin');
            }
        } else {
            console.log('Aucun planning trouvé dans la carte');
            // Recharger les plannings et attendre le résultat
            console.log('Tentative de rechargement des plannings...');
            chargerPlanningMagasin(idRepr, idMagasin, $card, function() {
                // Callback exécuté après le chargement
                var planningsReloaded = $card.data('existing-plannings') || [];
                console.log('Plannings rechargés:', planningsReloaded);
                
                if (planningsReloaded && planningsReloaded.length > 0) {
                    // Récupérer les paramètres de la carte (semaine, année, fréquence)
                    var week = parseInt($card.find('.week-input').val());
                    var year = parseInt($card.find('.year-input').val());
                    var frequency = parseInt($card.find('.frequency-input').val());
                    
                    console.log('Paramètres de suppression après rechargement:', {week, year, frequency});
                    console.log('Plannings rechargés disponibles:', planningsReloaded);
                    
                    // Chercher le planning correspondant aux paramètres sélectionnés
                    var planningToDelete = null;
                    for (var i = 0; i < planningsReloaded.length; i++) {
                        var planning = planningsReloaded[i];
                        console.log('Vérification planning après rechargement:', {
                            planning_start: planning.start,
                            planning_annee: planning.annee,
                            planning_rec: planning.rec,
                            carte_week: week,
                            carte_year: year,
                            carte_frequency: frequency
                        });
                        
                        // Comparaison flexible des types
                        if (parseInt(planning.start) == week && parseInt(planning.annee) == year && parseInt(planning.rec) == frequency) {
                            planningToDelete = planning;
                            console.log('Planning trouvé avec paramètres exacts après rechargement');
                            break;
                        }
                    }
                    
                    // Si aucun planning trouvé avec les paramètres exacts, prendre le premier disponible
                    if (!planningToDelete && planningsReloaded.length > 0) {
                        planningToDelete = planningsReloaded[0];
                        console.log('Aucun planning trouvé avec les paramètres exacts après rechargement, utilisation du premier planning disponible');
                        // Utiliser les paramètres du planning existant au lieu de ceux de l'interface
                        week = parseInt(planningToDelete.start);
                        year = parseInt(planningToDelete.annee);
                        frequency = parseInt(planningToDelete.rec);
                        console.log('Paramètres ajustés après rechargement:', {week, year, frequency});
                    }
                    
                    if (planningToDelete) {
                        console.log('Planning à supprimer après rechargement:', planningToDelete);
                        console.log('Paramètres de suppression après rechargement:', {week, year, frequency});
                        supprimerPlanningExistant(idRepr, idMagasin, week, year, frequency);
                    } else {
                        console.log('Aucun planning trouvé après rechargement');
                        alert('Aucun planning trouvé pour ce magasin');
                    }
                } else {
                    alert('Aucun planning à supprimer pour ce magasin');
                }
            });
        }
    });
    
    // Bouton Aperçu
    $('#btnApercu').on('click', function() {
        var idRepr = $('#promoteurSelect').val();
        if (!idRepr) {
            alert('Veuillez d\'abord sélectionner un promoteur');
            return;
        }
        
        var apercu = 'APERÇU DU PLANNING\n\n';
        var promoteurText = $('#promoteurSelect option:selected').text();
        apercu += 'Promoteur: ' + promoteurText + '\n\n';
        
        var modifiedCount = 0;
        $('.magasin-card').each(function() {
            var $card = $(this);
            var isModified = $card.hasClass('editing-planning') || $card.data('modified') === true;
            
            // Vérifier combien de jours sont sélectionnés
            var joursSelected = [];
            $card.find('.jour-btn.selected').each(function() {
                joursSelected.push($(this).text());
            });
            
            // Ne montrer que les cartes modifiées ET avec au moins un jour sélectionné
            if (isModified && joursSelected.length > 0) {
                modifiedCount++;
                var magasinName = $card.find('.magasin-name').text();
                var frequency = $card.find('.frequency-input').val();
                var week = $card.find('.week-input').val();
                var year = $card.find('.year-input').val();
                
                apercu += magasinName + ':\n';
                apercu += '  - Jours: ' + joursSelected.join(', ') + '\n';
                apercu += '  - Fréquence: Toutes les ' + frequency + ' semaine(s)\n';
                apercu += '  - Début: Semaine ' + week + ' de ' + year + '\n\n';
            }
        });
        
        if (modifiedCount === 0) {
            apercu += 'Aucune modification détectée.';
        } else {
            apercu += 'Total: ' + modifiedCount + ' magasin(s) modifié(s)';
        }
        
        // Créer un modal Bootstrap moderne avec couleur bleue
        var modalHtml = `
            <div class="modal fade" id="apercuModal" tabindex="-1" role="dialog">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content modal-modern">
                        <div class="modal-header modal-header-blue">
                            <div class="modal-header-content">
                                <div class="modal-icon">
                                    <i class="fas fa-eye"></i>
                                </div>
                                <h5 class="modal-title">Aperçu des modifications</h5>
                            </div>
                            <button type="button" class="close modal-close-white" data-dismiss="modal">
                                <span>&times;</span>
                            </button>
                        </div>
                        <div class="modal-body modal-body-modern">
                            <pre class="apercu-content">${apercu}</pre>
                        </div>
                        <div class="modal-footer modal-footer-modern">
                            <button type="button" class="btn-modal-close" data-dismiss="modal">
                                <i class="fas fa-times"></i>
                                <span>Fermer</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Supprimer l'ancien modal s'il existe
        $('#apercuModal').remove();
        
        // Ajouter le nouveau modal
        $('body').append(modalHtml);
        
        // Afficher le modal
        $('#apercuModal').modal('show');
    });
    
    // Bouton Enregistrer
    $('#btnEnregistrer').on('click', function() {
        var idRepr = $('#promoteurSelect').val();
        if (!idRepr) {
            alert('Veuillez d\'abord sélectionner un promoteur');
            return;
        }
        
        var plannings = [];
        var hasValidPlanning = false;
        
        $('.magasin-card').each(function() {
            var $card = $(this);
            var magasinId = $card.data('magasin-id');
            console.log('Traitement carte magasin:', magasinId);
            
            // Vérifier si la carte a été modifiée
            var isModified = $card.hasClass('editing-planning') || $card.data('modified') === true;
            console.log('Carte modifiée:', isModified);
            
            var joursSelected = [];
            $card.find('.jour-btn.selected').each(function() {
                var jour = parseInt($(this).data('jour'));
                console.log('Jour sélectionné:', jour);
                joursSelected.push(jour);
            });
            
            var frequency = parseInt($card.find('.frequency-input').val());
            var week = parseInt($card.find('.week-input').val());
            var year = parseInt($card.find('.year-input').val());
            
            console.log('Données magasin:', {
                magasinId: magasinId,
                joursSelected: joursSelected,
                frequency: frequency,
                week: week,
                year: year,
                isModified: isModified
            });
            
            // Ne sauvegarder que si la carte a été modifiée ET qu'au moins un jour est sélectionné
            if (isModified && joursSelected.length > 0) {
                hasValidPlanning = true;
                var planningData = {
                    id_magasin: magasinId,
                    jours: joursSelected,
                    frequency: frequency,
                    week: week,
                    year: year,
                    is_modification: $card.hasClass('editing-planning') // Indiquer si c'est une modification
                };
                
                // Si c'est une modification d'un planning existant, ajouter l'ID du planning
                if ($card.hasClass('editing-planning')) {
                    var existingPlannings = $card.data('existing-plannings') || [];
                    if (existingPlannings.length > 0) {
                        planningData.planning_id = existingPlannings[0].unique_id || existingPlannings[0].id;
                    }
                }
                
                plannings.push(planningData);
            }
        });
        
        if (!hasValidPlanning) {
            alert('Aucune modification détectée. Veuillez modifier au moins un magasin avant de sauvegarder.');
            return;
        }
        
        console.log('Plannings à sauvegarder:', plannings);
        console.log('ID Repr:', idRepr);
        
        // Afficher le loader
        var $btn = $('#btnEnregistrer');
        var originalText = $btn.html();
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Sauvegarde en cours...');
        
        // Envoyer les plannings via AJAX
        $.ajax({
            url: '<?php echo URL; ?>async',
            method: 'POST',
            data: {
                methode: 'user::savePlanningMagasins',
                id_repr: idRepr,
                plannings: JSON.stringify(plannings)
            },
            success: function(response) {
                console.log('Réponse serveur:', response);
                try {
                    var data = JSON.parse(response);
                    console.log('Données parsées:', data);
                    if (data.success) {
                        alert(data.message + ' (' + data.count + ' planning(s) créé(s))');
                        // Marquer toutes les cartes comme non modifiées
                        $('.magasin-card.modified').removeClass('modified').data('modified', false);
                    } else {
                        alert('Erreur: ' + data.message);
                    }
                } catch (e) {
                    console.error('Erreur parsing JSON:', e);
                    console.error('Réponse brute:', response);
                    alert('Erreur lors de l\'enregistrement: ' + e.message);
                }
            },
            error: function() {
                console.error('Erreur AJAX');
                alert('Erreur lors de l\'enregistrement');
            },
            complete: function() {
                // Restaurer le bouton
                $btn.prop('disabled', false).html(originalText);
            }
        });
    });
    
    // Fonction pour générer les plannings récurrents sur 8 semaines
    function genererPlanningsRecurrents(idRepr, idMagasin, dateDebut, joursSemaine) {
        var plannings = [];
        var dateStart = new Date(dateDebut);
        var dateEnd = new Date(dateStart);
        dateEnd.setDate(dateEnd.getDate() + (8 * 7)); // 8 semaines
        
        var currentDate = new Date(dateStart);
        
        while (currentDate <= dateEnd) {
            var jourSemaine = currentDate.getDay(); // 0 = dimanche, 1 = lundi, etc.
            
            // Convertir le jour JavaScript (0-6) vers notre format (1-6, lundi-samedi)
            var jourFormat = jourSemaine === 0 ? 7 : jourSemaine; // Dimanche = 7, mais on ne l'utilise pas
            
            if (joursSemaine.includes(jourFormat)) {
                plannings.push({
                    id_repr: idRepr,
                    id_magasin: idMagasin,
                    date_passage: currentDate.toISOString().split('T')[0],
                    jour_semaine: jourFormat
                });
            }
            
            currentDate.setDate(currentDate.getDate() + 1);
        }
        
        return plannings;
    }
    
    // Fonction pour charger la liste des promoteurs
    function chargerListePromoteurs() {
        $.ajax({
            url: '<?php echo URL; ?>async',
            method: 'POST',
            data: {
                methode: 'user::getPromoteursSecteur'
            },
            success: function(response) {
                try {
                    var data = JSON.parse(response);
                    var $select = $('#promoteurSelect');
                    
                    // Vider le select (garder l'option par défaut)
                    $select.find('option:not(:first)').remove();
                    
                    if (data && data.length > 0) {
                        data.forEach(function(promoteur) {
                            var option = $('<option></option>')
                                .attr('value', promoteur.id_repr)
                                .text(promoteur.displayname + ' (ID Rep: ' + promoteur.id_repr + ')');
                            $select.append(option);
                        });
                        
                        // Vérifier s'il y a un ID de promoteur dans l'URL après le chargement
                        var urlParams = new URLSearchParams(window.location.search);
                        var idReprFromUrl = urlParams.get('id_repr');
                        
                        if (idReprFromUrl) {
                            console.log('ID Repr depuis URL:', idReprFromUrl);
                            var $option = $select.find('option[value="' + idReprFromUrl + '"]');
                            
                            if ($option.length > 0) {
                                console.log('Option trouvée, sélection en cours...');
                                // Sélectionner le promoteur
                                $select.val(idReprFromUrl);
                                $select.trigger('change');
                            } else {
                                console.log('Option non trouvée pour ID:', idReprFromUrl);
                            }
                        }
                    }
                } catch (e) {
                    console.error('Erreur parsing JSON promoteurs:', e);
                }
            },
            error: function() {
                console.error('Erreur chargement promoteurs');
            }
        });
    }
    
    // Fonction pour charger les magasins d'un promoteur
    function chargerMagasinsPromoteur(idRepr, nomPromoteur) {
        // Afficher la section des magasins
        $('#magasinsSection').show();
        // Le titre est géré statiquement, pas besoin de le modifier
        
        // Charger les magasins via AJAX
        $.ajax({
            url: '<?php echo URL; ?>async',
            method: 'POST',
            data: {
                methode: 'user::getMagasinsPromoteur',
                id_repr: idRepr
            },
            success: function(response) {
                try {
                    var data = JSON.parse(response);
                    // Afficher les informations du promoteur avec le nombre de magasins
                    $('#promoteurInfo').html(
                        '<i class="fas fa-user"></i> <strong>' + nomPromoteur + '</strong> (ID Rep: ' + idRepr + ') - <span class="badge badge-success">' + data.length + ' magasin(s)</span>'
                    ).show();
                    afficherMagasins(data);
                } catch (e) {
                    console.error('Erreur parsing JSON magasins:', e);
                    $('#promoteurInfo').html(
                        '<i class="fas fa-user"></i> <strong>' + nomPromoteur + '</strong> (ID Rep: ' + idRepr + ') - <span class="badge badge-warning">0 magasin(s)</span>'
                    ).show();
                    afficherMagasins([]);
                }
            },
            error: function() {
                console.error('Erreur chargement magasins');
                $('#promoteurInfo').html(
                    '<i class="fas fa-user"></i> <strong>' + nomPromoteur + '</strong> (ID Rep: ' + idRepr + ') - <span class="badge badge-danger">Erreur</span>'
                ).show();
                afficherMagasins([]);
            }
        });
    }
    
    // Variable globale pour stocker tous les magasins
    var tousLesMagasins = [];
    
    // Fonction pour afficher les magasins sous forme de cartes
    function afficherMagasins(magasinsData) {
        var $magasinsList = $('#magasinsList');
        $magasinsList.empty();
        
        // Stocker tous les magasins pour la recherche
        tousLesMagasins = magasinsData || [];
        
        if (magasinsData && magasinsData.length > 0) {
            magasinsData.forEach(function(magasin) {
                var carteMagasin = creerCarteMagasin(magasin);
                var $carte = $(carteMagasin);
                $magasinsList.append($carte);
                
                // Charger les plannings existants pour ce magasin
                var idRepr = $('#promoteurSelect').val();
                if (idRepr) {
                    chargerPlanningMagasin(idRepr, magasin.id_as400, $carte);
                }
            });
            
            // Mettre à jour le compteur
            updateMagasinCount(magasinsData.length);
            
            // Appliquer les filtres actuels
            var searchTerm = $('#magasinFilter').val();
            var jourSelectionne = $('#jourFilter').val();
            filtrerMagasins(searchTerm, jourSelectionne);
        } else {
            $magasinsList.html('<div class="col-12"><div class="alert alert-warning text-center">Aucun magasin trouvé pour ce promoteur</div></div>');
            updateMagasinCount(0);
        }
    }
    
    // Fonction pour mettre à jour le compteur de magasins
    function updateMagasinCount(count) {
        var $counter = $('#magasinCount');
        $counter.text(count);
        
        // Animation pulse
        $counter.addClass('pulse');
        setTimeout(function() {
            $counter.removeClass('pulse');
        }, 300);
    }
    
    // Fonction pour afficher tous les magasins (réinitialiser l'affichage)
    function afficherTousLesMagasins() {
        $('.col-md-6, .col-lg-3').show();
        updateMagasinCount($('.col-md-6:visible, .col-lg-3:visible').length);
    }
    
    // Fonction pour filtrer les magasins
    function filtrerMagasins(searchTerm, jourSelectionne) {
        // Vérifier que tousLesMagasins existe et n'est pas vide
        if (!tousLesMagasins || tousLesMagasins.length === 0) {
            console.log('tousLesMagasins est vide, récupération des magasins visibles...');
            // Fallback : récupérer les magasins depuis les cartes visibles
            var magasinsVisibles = [];
            $('.magasin-card').each(function() {
                var $card = $(this);
                var magasinData = {
                    id_as400: $card.data('magasin-id'),
                    enseigne: $card.find('.magasin-nom').text(),
                    ville: $card.find('.magasin-ville').text(),
                    code_postal: $card.find('.magasin-cp').text()
                };
                magasinsVisibles.push(magasinData);
            });
            tousLesMagasins = magasinsVisibles;
        }
        
        var magasinsFiltres = tousLesMagasins;
        
        // Filtre par recherche textuelle
        if (searchTerm && searchTerm.length >= 2) {
            // Diviser le terme de recherche en mots individuels
            var searchWords = searchTerm.toLowerCase().trim().split(/\s+/).filter(function(word) {
                return word.length > 0;
            });
            
            magasinsFiltres = magasinsFiltres.filter(function(magasin) {
                // Créer une chaîne de recherche combinée
                var searchText = [
                    magasin.enseigne || '',
                    magasin.ville || '',
                    magasin.id_as400 || '',
                    magasin.code_postal || ''
                ].join(' ').toLowerCase();
                
                // Vérifier que tous les mots de recherche sont présents
                return searchWords.every(function(word) {
                    return searchText.includes(word);
                });
            });
        }
        
        // Filtre par jour
        if (jourSelectionne && jourSelectionne !== '') {
            var jourNum = parseInt(jourSelectionne);
            magasinsFiltres = magasinsFiltres.filter(function(magasin) {
                // Vérifier si ce magasin a une récurrence ce jour-là
                var $card = $('.magasin-card[data-magasin-id="' + magasin.id_as400 + '"]');
                var hasPlanningThisDay = $card.find('.jour-btn[data-jour="' + jourNum + '"]').hasClass('existing-planning');
                return hasPlanningThisDay;
            });
        }
        
        afficherMagasinsFiltres(magasinsFiltres);
    }
    
    // Fonction pour afficher les magasins filtrés avec effet
    function afficherMagasinsFiltres(magasinsData) {
        // Masquer tous les conteneurs de cartes d'abord
        $('.col-md-6, .col-lg-3').hide();
        
        if (magasinsData && magasinsData.length > 0) {
            // Afficher seulement les magasins filtrés
            magasinsData.forEach(function(magasin) {
                var $conteneur = $('.magasin-card[data-magasin-id="' + magasin.id_as400 + '"]').closest('.col-md-6, .col-lg-3');
                if ($conteneur.length > 0) {
                    $conteneur.show();
                }
            });
            
            // Mettre à jour le compteur
            updateMagasinCount(magasinsData.length);
        } else {
            // Aucun magasin trouvé
            updateMagasinCount(0);
        }
    }
    
    // Fonction pour créer une carte de magasin
    function creerCarteMagasin(magasin) {
        var currentYear = new Date().getFullYear();
        var currentWeek = getWeekNumber(new Date());
        
        var locationText = (magasin.ville || '') + ' ' + (magasin.code_postal || '') + ' (' + magasin.id_as400 + ')';
        
        var carte = '<div class="col-md-6 col-lg-3">' +
            '<div class="magasin-card" data-magasin-id="' + magasin.id_as400 + '">' +
                '<div class="magasin-header">' +
                    '<div>' +
                        '<h6 class="magasin-name" title="' + magasin.enseigne + '">' + magasin.enseigne + '</h6>' +
                        '<p class="magasin-id" title="' + locationText + '">' + locationText + '</p>' +
                    '</div>' +
                '</div>' +
                
                // Boutons des jours
                '<div class="jours-buttons">' +
                    '<button type="button" class="jour-btn" data-jour="1">LUN</button>' +
                    '<button type="button" class="jour-btn" data-jour="2">MAR</button>' +
                    '<button type="button" class="jour-btn" data-jour="3">MER</button>' +
                    '<button type="button" class="jour-btn" data-jour="4">JEU</button>' +
                    '<button type="button" class="jour-btn" data-jour="5">VEN</button>' +
                '</div>' +
                
                // Section fréquence
                '<div class="frequency-section">' +
                    '<p class="frequency-label">Toutes les</p>' +
                    '<div class="frequency-controls">' +
                        '<button type="button" class="frequency-btn" data-action="decrease">-</button>' +
                        '<input type="number" class="frequency-input" value="1" min="1" max="8">' +
                        '<button type="button" class="frequency-btn" data-action="increase">+</button>' +
                    '</div>' +
                    '<p class="frequency-label">semaine</p>' +
                '</div>' +
                
                // Section semaine de début
                '<div class="week-section">' +
                    '<p class="week-label">À partir de la semaine n°</p>' +
                    '<div class="week-controls">' +
                        '<button type="button" class="frequency-btn" data-action="decrease-week">-</button>' +
                        '<input type="number" class="week-input" value="' + currentWeek + '" min="1" max="53">' +
                        '<button type="button" class="frequency-btn" data-action="increase-week">+</button>' +
                        '<input type="number" class="year-input" value="' + currentYear + '" min="2020" max="2030">' +
                    '</div>' +
                '</div>' +
                
                // Bouton de suppression (positionné en haut à droite)
                '<button type="button" class="btn-supprimer-planning" style="display: none;">' +
                    '<i class="fas fa-trash"></i>' +
                '</button>' +
            '</div>' +
        '</div>';
        
        return carte;
    }
    
    // Fonction pour charger les plannings existants d'un magasin
    function chargerPlanningMagasin(idRepr, idMagasin, $card, callback) {
        $.ajax({
            url: '<?php echo URL; ?>async',
            method: 'POST',
            data: {
                methode: 'user::getPlanningMagasin',
                id_repr: idRepr,
                id_magasin: idMagasin
            },
            success: function(response) {
                try {
                    var plannings = JSON.parse(response);
                    console.log('Plannings chargés pour magasin', idMagasin, ':', plannings);
                    
                    // IMPORTANT: Réinitialiser tous les boutons de jours avant de charger les plannings
                    $card.find('.jour-btn').removeClass('selected existing-planning editing-planning');
                    $card.find('.jour-btn').removeAttr('title');
                    $card.find('.jour-btn').removeData('planning-id');
                    
                    // Retirer les classes de modification
                    $card.removeClass('editing-planning modified');
                    $card.data('modified', false);
                    
                    if (plannings && plannings.length > 0) {
                        // Stocker les plannings dans la carte pour modification
                        $card.data('existing-plannings', plannings);
                        console.log('Plannings stockés dans la carte:', $card.data('existing-plannings'));
                        
                        // Retirer la classe no-planning si elle existe
                        $card.removeClass('no-planning');
                        
                        // Afficher le bouton de suppression
                        $card.find('.btn-supprimer-planning').show();
                        
                        // Mettre en surbrillance les jours déjà planifiés
                        plannings.forEach(function(planning) {
                            var days = planning.days.split(',');
                            days.forEach(function(day) {
                                var dayNum = parseInt(day.trim());
                                if (dayNum >= 1 && dayNum <= 5) {
                                    var $btn = $card.find('.jour-btn[data-jour="' + dayNum + '"]');
                                    $btn.addClass('selected existing-planning');
                                    $btn.attr('title', 'Planning existant: ' + planning.enseigne + ' (Cliquez pour modifier)');
                                    $btn.data('planning-id', planning.unique_id || planning.id);
                                }
                            });
                        });
                    } else {
                        // Supprimer les données de plannings stockées
                        $card.removeData('existing-plannings');
                        
                        // Masquer le bouton de suppression s'il n'y a pas de plannings
                        $card.find('.btn-supprimer-planning').hide();
                        
                        // Ajouter la classe pour indiquer qu'il n'y a pas de planning
                        $card.addClass('no-planning');
                        
                        console.log('Aucun planning trouvé pour le magasin', idMagasin, '- Classe no-planning ajoutée');
                        console.log('Classes de la carte:', $card.attr('class'));
                    }
                } catch (e) {
                    console.error('Erreur parsing JSON planning magasin:', e);
                }
                
                // Appeler le callback si fourni
                if (callback && typeof callback === 'function') {
                    callback();
                }
            },
            error: function() {
                console.error('Erreur chargement planning magasin');
                // Appeler le callback même en cas d'erreur
                if (callback && typeof callback === 'function') {
                    callback();
                }
            }
        });
    }
    
    // Fonction pour activer le mode modification d'une carte
    function activerModeModification($card) {
        // Activer le mode modification
        $card.addClass('editing-planning');
        $card.data('modified', true);
        
        // Désactiver la récurrence (mettre à 0)
        $card.find('.frequency-input').val(0);
        
        // Afficher un message d'information
        showNotification('Mode modification activé. Vous pouvez maintenant sélectionner/désélectionner les jours et modifier la récurrence.', 'info');
    }
    
    // Fonction pour modifier un planning existant (ancienne fonction, gardée pour compatibilité)
    function modifierPlanningExistant($card, planningId) {
        var plannings = $card.data('existing-plannings') || [];
        var planning = plannings.find(function(p) {
            return (p.unique_id || p.id) == planningId;
        });
        
        if (planning) {
            // Stocker l'ID du planning en cours de modification
            $card.data('editing-planning-id', planningId);
            
            // Pré-remplir les champs avec les valeurs existantes
            $card.find('.frequency-input').val(planning.rec || 1);
            $card.find('.week-input').val(planning.start || 1);
            $card.find('.year-input').val(planning.annee || new Date().getFullYear());
            
            // Désélectionner tous les jours
            $card.find('.jour-btn').removeClass('selected existing-planning');
            
            // Resélectionner les jours du planning existant
            var days = planning.days.split(',');
            days.forEach(function(day) {
                var dayNum = parseInt(day.trim());
                if (dayNum >= 1 && dayNum <= 5) {
                    var $btn = $card.find('.jour-btn[data-jour="' + dayNum + '"]');
                    $btn.addClass('selected');
                }
            });
            
            // Changer le style de la carte pour indiquer qu'elle est en édition
            $card.addClass('editing-planning');
            
            // Afficher un message d'information avec les paramètres réels
            showNotification('Planning existant chargé (Semaine: ' + (planning.start || 1) + ', Année: ' + (planning.annee || new Date().getFullYear()) + ', Fréquence: ' + (planning.rec || 1) + '). Vous pouvez le modifier et cliquer sur "Enregistrer" pour sauvegarder les changements.', 'info');
        }
    }
    
    // Fonction pour supprimer un planning existant
    function supprimerPlanningExistant(idRepr, idMagasin, week, year, frequency) {
        // Validation des paramètres
        week = parseInt(week) || 1;
        year = parseInt(year) || new Date().getFullYear();
        frequency = parseInt(frequency) || 1;
        
        console.log('Suppression avec paramètres validés:', {
            idRepr: idRepr,
            idMagasin: idMagasin,
            week: week,
            year: year,
            frequency: frequency
        });
        
        if (confirm('Êtes-vous sûr de vouloir supprimer ce planning à partir de la semaine ' + week + ' de ' + year + ' ?')) {
            // Afficher un loader sur la carte
            var $card = $('.magasin-card[data-magasin-id="' + idMagasin + '"]');
            var $cardParent = $card.closest('.col-md-6, .col-lg-3');
            $cardParent.css('opacity', '0.5');
            
            $.ajax({
                url: '<?php echo URL; ?>async',
                method: 'POST',
                data: {
                    methode: 'user::supprimerPlanningMagasin',
                    id_repr: String(idRepr),
                    id_magasin: String(idMagasin),
                    week: String(week),
                    year: String(year),
                    frequency: String(frequency)
                },
                success: function(response) {
                    try {
                        var result = JSON.parse(response);
                        if (result.success) {
                            // Animation de succès
                            $cardParent.css('opacity', '1');
                            $card.css('background', '#d4edda');
                            
                            setTimeout(function() {
                                $card.css('background', '');
                                showNotification('✓ Planning supprimé avec succès', 'success');
                                
                                // Recharger les plannings du magasin
                                chargerPlanningMagasin(idRepr, idMagasin, $card);
                            }, 500);
                        } else {
                            $cardParent.css('opacity', '1');
                            showNotification('Erreur lors de la suppression: ' + result.message, 'error');
                        }
                    } catch (e) {
                        $cardParent.css('opacity', '1');
                        console.error('Erreur parsing:', e);
                        console.error('Response:', response);
                        showNotification('Erreur lors de la suppression du planning', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    $cardParent.css('opacity', '1');
                    console.error('Erreur AJAX:', status, error);
                    showNotification('Erreur lors de la suppression du planning', 'error');
                },
                complete: function() {
                    // Toujours remettre l'opacité normale
                    $cardParent.css('opacity', '1');
                }
            });
        }
    }
    
    // Fonction pour afficher les notifications
    function showNotification(message, type) {
        var alertClass = 'alert-info';
        if (type === 'success') alertClass = 'alert-success';
        if (type === 'error') alertClass = 'alert-danger';
        if (type === 'warning') alertClass = 'alert-warning';
        
        var notification = '<div class="alert ' + alertClass + ' alert-dismissible fade show" role="alert">' +
            message +
            '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
            '<span aria-hidden="true">&times;</span>' +
            '</button>' +
            '</div>';
        
        // Afficher la notification en haut de la page
        $('#promoteurInfo').after(notification);
        
        // Auto-supprimer après 5 secondes
        setTimeout(function() {
            $('.alert').fadeOut();
        }, 5000);
    }
    
    // Fonction pour calculer le numéro de semaine
    function getWeekNumber(date) {
        var start = new Date(date.getFullYear(), 0, 1);
        var diff = date - start;
        var oneWeek = 1000 * 60 * 60 * 24 * 7;
        return Math.ceil(diff / oneWeek);
    }
    
    // Fonction pour afficher le planning dans le tableau
    function afficherPlanning(planningData) {
        var tbody = $('#planningTableBody');
        tbody.empty();
        
        if (planningData && planningData.length > 0) {
            planningData.forEach(function(item) {
                var date = new Date(item.date_passage);
                var semaine = getSemaine(date);
                
                var row = '<tr>';
                row += '<td>' + formaterDate(date) + '</td>';
                row += '<td>Semaine ' + semaine + '</td>';
                row += '<td>' + (item.nom_magasin || 'Magasin inconnu') + '</td>';
                row += '<td><span class="badge badge-info">' + (item.statut || 'Planifié') + '</span></td>';
                row += '</tr>';
                tbody.append(row);
            });
        } else {
            tbody.append('<tr><td colspan="4" class="text-center text-muted">Aucun planning trouvé pour ce promoteur</td></tr>');
        }
    }
    
    // Fonction pour calculer le numéro de semaine
    function getSemaine(date) {
        var start = new Date(date.getFullYear(), 0, 1);
        var diff = date - start;
        var oneWeek = 1000 * 60 * 60 * 24 * 7;
        return Math.ceil(diff / oneWeek);
    }
    
    // Fonction pour formater la date
    function formaterDate(date) {
        return date.toLocaleDateString('fr-FR', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit'
        });
    }
    
    // Fonctions pour les actions (à implémenter)
    function modifierPlanning(id) {
        console.log('Modifier planning ID:', id);
        // TODO: Implémenter la modification
    }
    
    function supprimerPlanning(id) {
        if (confirm('Êtes-vous sûr de vouloir supprimer ce planning ?')) {
            console.log('Supprimer planning ID:', id);
            // TODO: Implémenter la suppression
        }
    }
});
</script>
