<div class="card card-primary card-outline">
  <div class="card-header">
    <h5 class="m-0"><?php echo l('admin-montage-notes');?> [FRENCH ONLY]</h5>
  </div>
  <div class="card-body m-250" style="font-size:13px;" id="notesversion">

    <?php /*
    <div class="text-secondary font-italic">
      <strong>v0.xx (à venir)</strong><br/>
      <div class="row">
        <div class="col">
          <h5><span>APK</span></h5>
          <ul>
          </ul>
        </div>
        <div class="col">
          <h5><span>CRM</span></h5>
          <ul>
          </ul>
        </div>
      </div>
    </div>
    */?>


    <div>
      <strong>v5.3 &bull; du 11/03/2024</small></strong><br/>
      <div class="row">
        <div class="col">
          <h5><span>APK</span></h5>
          <ul>
            <li>Ajout de la prise en charge du second ID AS400 (ID_ITA) dans les recherches produits</li>
            <li>Commande : Warning si PLV commandée mais stock non disponible / ou quantité inferieure disponible</li>
            <li>Commande : historisation des remplacements de produits</li>
          </ul>
        </div>
        <div class="col">
          <h5><span>CRM</span></h5>
          <ul>
            <li>Ajout de la prise en charge du second ID AS400 (ID_ITA) dans les recherches produits</li>
            <li>Stockage en base des remplacements de produits (stats à venir)</li>
          </ul>
        </div>
      </div>
    </div>

    <div>
      <strong>v5.2 <small> &bull; du 27/02/2024</small></strong><br/>
      <div class="row">
        <div class="col">
          <h5><span>APK</span></h5>
          <ul>
            <li>Ajout du choix de la langue italien dans l'application + <strong>Traduction de toute l'application</strong></li>
            <li>Correction d'un bug au clic sur une tache commercial qui faisait planter l'application</li>
            <li>Correction d'un bug qui n'affichait pas la dernière DN PEM</li>
            <li>Correction d'un bug qui n'affichait pas les photos des NEWS dans la section prospection</li>
            <li>Autorisation pour les DR et la Direction d'acceder au module de prospection</li>
            <li>Corretion d'un bug qui ne mettait pas immédiatement à jours les données des contacts des prospects</li>
          </ul>
        </div>
        <div class="col">
          <h5><span>CRM</span></h5>
          <ul>
            <li>Ajout du module de traduction en italien (Administration des traductions et passage de tout le CRM en multilangue)</li>
            <li>Optimisation de la vitesse d'affichage du tableau de bord des visites</li>
            <li>Optimisation de la vitesse d'affichage du tableau de bord des commandes</li>
          </ul>
        </div>
      </div>
    </div>



    <div>
      <strong>v4.8 <small> &bull; du 28/11/2023</small></strong><br/>
      <div class="row">
        <div class="col">
          <h5><span>APK</span></h5>
          <ul>
            <li>Correction bug d recalcul du planning de tournée quand tournée trop grande</li>
          </ul>
        </div>
        <div class="col">
          <h5><span>CRM</span></h5>
          <ul>
          </ul>
        </div>
      </div>
    </div>

    <div>
      <strong>v4.7 <small> &bull; du 23/11/2023</small></strong><br/>
      <div class="row">
        <div class="col">
          <h5><span>APK</span></h5>
          <ul>
            <li>Refonte du système de verification de communication avec le CRM (résolution de problèmes de performances globales)</li>
            <li>Correction de l'affichage du code barre dans la fiche produit</li>
          </ul>
        </div>
        <div class="col">
          <h5><span>CRM</span></h5>
          <ul>
            <li>Mise a disposition du code source de l'application mobile sur le serveur CRM Prod dans : /home/datas/gescom/apk/source</li>
          </ul>
        </div>
      </div>
    </div>

    <div>
      <strong>v4.6 <small> &bull; du 24/10/2023</small></strong><br/>
      <div class="row">
        <div class="col">
          <h5><span>APK</span></h5>
          <ul>
            <li>Ajout de la fonctionnalité de scan des colis lors des visites</li>
            <li>Possibilité de modifier un contact sans devoir le supprimer</li>
            <li>Possibilité de modifier / supprimer une note (par tout le monde)</li>
            <li>Pouvoir modifier dates en cas de commandes en attente ( Ajout d'un rappel des dates dans le recap de la commande)</li>
            <li>Ajout de pluisieurs choix dans le rapport (questionnaire fin de visite) </li>
            <li>Récupération & Affichage des Strats PEM dans l'APK</li>
          </ul>
        </div>
        <div class="col">
          <h5><span>CRM</span></h5>
          <ul>
            <li>Intégration des Colis depuis Logis</li>
            <li>Commandes : ajout de la possibilité de générer une commande minos depuis une commande externe</li>
            <li>Administration des strats PEM dans le CRM</li>
          </ul>
        </div>
      </div>
    </div>


    <div>
      <strong>v4.0 <small> &bull; du 19/05/2023</small></strong><br/>
      <div class="row">
        <div class="col">
          <h5><span>APK</span></h5>
          <ul>
            <li>Ajout de la visite PEM</li>
          </ul>
        </div>
        <div class="col">
          <h5><span>CRM</span></h5>
          <ul>
            <li>Ajout de l'onglet "Visite PEM" avec son contenu dans la fiche visite</li>
          </ul>
        </div>
      </div>
    </div>

    <div>
      <strong>v3.7 <small> &bull; du 06/04/2023</small></strong><br/>
      <div class="row">
        <div class="col">
          <h5><span>APK</span></h5>
          <ul>
            <li>Visite : "Photo d'arrivée" devient "Photo de début de visite"</li>
            <li>Visite : "Photo vue d'ensemble" devient "Photo de fin de visite"</li>
            <li>Visite : Il est désormais possible de prendre 10 photos d'arrivées & 10 photos de fin</li>
            <li>Visite : Le questionnaire de fin de visite est désormais placé avant les OP</li>
            <li>Le switch de nom de domaine est actif si le domaine principal ne répond pas.</li>
            <li>Rajout du débug dans le menu du l'application</li>
          </ul>
        </div>
        <div class="col">
          <h5><span>CRM</span></h5>
          <ul>
            <li>Visite : les noms des photos ont été modifiés comme sur l'application</li>
            <li>Visite : les informations du questionnaire sont désormais affichées sur la page de la visite</li>
          </ul>
        </div>
      </div>
    </div>



    <div>
      <strong>v3.6 <small> &bull; du 10/03/2023</small></strong><br/>
      <div class="row">
        <div class="col">
          <h5><span>APK</span></h5>
          <ul>
            <li>A la création/modification d'un prospect, l'enseigne est désormais une liste</li>
            <li>Ajout de la DN 0.2m</li>
            <li>Ajout de l'année de la semaine de départ dans le planning de tournée</li>
            <li>Corrections de divers bugs mineurs mentionnés par mails</li>
            <li>Correction d'un bug lié à la création des contacts</li>
          </ul>
        </div>
        <div class="col">
          <h5><span>CRM</span></h5>
          <ul>
            <li>Correction du problème de deconnexion intempestives</li>
          </ul>
        </div>
      </div>
    </div>

    <div>
      <strong>v3.4 <small> &bull; du 03/01/2023</small></strong><br/>
      <div class="row">
        <div class="col">
          <h5><span>APK</span></h5>
          <ul>
            <li>Blocage login application si pas d'ID de représentant</li>
            <li>Taches, correction bug d'appartenance aux clients</li>
            <li>Ajout des prochaines visites clients</li>
            <li>Correction des notes clients</li>
            <li>Correction (Ma tournée) sur les nombres de semaines</li>
          </ul>
        </div>
        <div class="col">
          <h5><span>CRM</span></h5>
          <ul>
          </ul>
        </div>
      </div>
    </div>

    <div>
      <strong>v3.1 <small> &bull; du 02/12/2022</small></strong><br/>
      <div class="row">
        <div class="col">
          <h5><span>APK</span></h5>
          <ul>
            <li>Correction du bug de génération de tournée</li>
            <li>Ajout de plusieurs alertes et "garde-fous" lors de la génération des tournées (compteur de clients manquants,  avertissements de générations, alerte si l'on quitte la génération)</li>
            <li>Nouvelle fonctionnalité : Possibilité depuis le menu de réaliser une mise à jour forcée de la base de données (sans plus avoir besoin de se déconnecter de l'application)</li>
            <li>Refonte ergonomie du Menu de navigation</li>
            <li>Ajout texte explicatif dans Mon espace > Mes RDV </li>
            <li>Ajout popup d'information pour les opérations : Synchronisation forcée, Mise à jour forcée et Deconnexion</li>
          </ul>
        </div>
        <div class="col">
          <h5><span>CRM</span></h5>
          <ul>
          </ul>
        </div>
      </div>
    </div>

    <div>
      <strong>v3.0 <small> &bull; du 18/11/2022</small></strong><br/>
      <div class="row">
        <div class="col">
          <h5><span>APK</span></h5>
          <ul>
            <li>Mes clients : se base désormais sur les id de représentant de l'AS400</li>
          </ul>
        </div>
        <div class="col">
          <h5><span>CRM</span></h5>
          <ul>
          </ul>
        </div>
      </div>
    </div>
  
<div>
      <strong>v2.25 <small> &bull; du 15/11/2022</small></strong><br/>
      <div class="row">
        <div class="col">
          <h5><span>APK</span></h5>
          <ul>
            <li>Prospect : Ajout de champs (SIRET,CNUD,N°TVA,Adresse de livraison,Promoteur,...)</li>
            <li>Prospection : Ajout du controle des champs prospects remplis avant création client</li>
          </ul>
        </div>
        <div class="col">
          <h5><span>CRM</span></h5>
          <ul>
            <li>Mail création client DV : ajout des champs supplémentaires du prospect</li>
          </ul>
        </div>
      </div>
    </div>
<div>
      <strong>v2.24 <small> &bull; du 14/11/2022</small></strong><br/>
      <div class="row">
        <div class="col">
          <h5><span>APK</span></h5>
          <ul>
            <li>Prospection : Finalisation de l'interface de prospection</li>
            <li>Prospection : Envoi de demande de création clients</li>
            <li>CS - CA  : Récupération du CA objectif du mois du prospect</li>
          </ul>
        </div>
        <div class="col">
          <h5><span>CRM</span></h5>
          <ul>
            <li>Prospection : tableau de bord des prospections</li>
            <li>Prospection : Evolutions de la fiche de suivi de prospection</li>
            <li>Prospects : corrections de bugs d'interface (liens disfonctionnels)</li>
            <li>Prospects : Evolutions de la fiche des prospects</li>
            <li>CS - CA : Interface d'injection et consultation du CA Objectif des CS</li>
          </ul>
        </div>
      </div>
    </div>

    <div>
      <strong>v2.23 <small> &bull; du 27/10/2022</small></strong><br/>
      <div class="row">
        <div class="col">
          <h5><span>APK</span></h5>
          <ul>
            <li>Ajout rubrique "Mon espace"</li>
            <li>Mon espace : Ajout rubrique "Ma tournée" qui calcule de le plan de tournée</li>
            <li>Ma tournée : Ajout d'un système de recherche sur le nom & code du client</li>
            <li>Mon espace : Ajout rubrique "Kilomètres" qui permet d'enregistrer chaque jours les kilometres réalisés</li>
            <li>Mon espace : Retrait de la rubrique "Note de frais"</li>
            <li>Mon espace : Ajout de la rubrique (statique) "RDV clients"</li>
            <li>Visite : Les OPs des précédentes visite sont désormais remontées automatique</li>
            <li>Visite : Possibilité de chercher dans les OPs</li>
            <li>Visite : Ajout du questionnaire de fin de visite</li>
          </ul>
        </div>
        <div class="col">
          <h5><span>CRM</span></h5>
          <ul>
            <li>Correction des mails pour l'annulation de visite</li>
            <li>Refonte interface CRM (menu)</li>
            <li>Refonte interface des tâches de prospection (en tableau)</li>
          </ul>
        </div>
      </div>
    </div>


    <div>
      <strong>v2.20 <small> &bull; du 09/09/2022</small></strong><br/>
      <div class="row">
        <div class="col">
          <h5><span>APK</span></h5>
          <ul>
            <li>Fiche client : permettre le choix des dates pour recalculer les stats clients</li>
            <li>Prospection : début de l'interface prospection (Fiche prospect, gestion des taches)</li>
          </ul>
        </div>
        <div class="col">
          <h5><span>CRM</span></h5>
          <ul>
            <li>Consultation des taches de prospections</li>
          </ul>
        </div>
      </div>
    </div>

    <div>
      <strong>v2.18 <small> &bull; du 09/08/2022</small></strong><br/>
      <div class="row">
        <div class="col">
          <h5><span>APK</span></h5>
          <ul>
            <li>Pouvoir faire un retour en cas d’erreur quand la question est posée « souhaitez vous faire une commande » et revenir juste avant la question sans effacer les données déjà renseignées (début de visite, photos…)</li>
            <li>Lors de l'envoi par mail d'une commande au client, une adresse email valide est désormais exigée</li>
            <li>Permettre l’ajout des informations de contacts dans le CRM (appli et back office)</li>
            <li>Permettre au promoteur de passer une visite en "Annuler visite" (rouge)</li>
            <li>Permettre au promoteur de définir lui-même ses futures visites ( Système de récurence + Modification + Supression)</li>
            
          </ul>
        </div>
        <div class="col">
          <h5><span>CRM</span></h5>
          <ul>
            <li>Optimiser l'affichage du CRM back Office sur appareil mobile</li>
            <li>Permettre l’ajout des informations de contacts dans le CRM (appli et back office)</li>
            <li>Ajouter les champs suivants dans la fiche client : Type de commande, client à faire avant ouverture, flasher l'arrivage, commande Labell</li>
            <li>Dans les stats commandes & visites : lien vers le promoteur et ses chiffres</li>
            <li>Dans les stats : ajout des colonnes "rayon plein", "visites prévues"</li>
            <li>Refonte de la stat des commandes</li>
          </ul>
        </div>
      </div>
    </div>

<div>
      <strong>v2.17 <small> &bull; du 27/06/2022</small></strong><br/>
      <div class="row">
        <div class="col">
          <h5><span>APK</span></h5>
          <ul>
            <li>File d'attente : correction d'un bug (Si "Autre" bloqué, non traitemente des photos)</li>
            <li>File d'attente : supression des éléments illisibles/bloqués</li>
            <li>File d'attente "Autre" : Tentative d'envoi immédiat après avoir ajouté un élément dans "Autre"</li>
            <li>Debug/logs : Ajouts de multiples messages de logs</li>
            <li>Debug : Ajout du "User" dans l'objet de debug</li>
            <li>Logs : Limitation à 500 mesages de logs lors de l'envoi du debug</li>
            <li>Accueil : Boutton paramètres masqué (car non utilisé)</li>
            <li>Commandes/Visites : Ajout de la possibilité de rajouter une commande a une visite sans commande ou de supprimer une commande lors d'une visite avec commande</li>
            <li>Mes clients : correction bug pouvant générer des clients ne faisant pas parti du planning</li>
          </ul>
        </div>
        <div class="col">
          <h5><span>CRM</span></h5>
          <ul>
            <li>Debug : refonte interface debug pour une meilleure lisibilité</li>
          </ul>
        </div>
      </div>
    </div>

    <div>
      <strong>v2.16 <small> &bull; du 13/06/2022</small></strong><br/>
      <div class="row">
        <div class="col">
          <h5><span>APK</span></h5>
          <ul>
            <li>Visites : plus de traitement de la file d'attente durant la prise de visite</li>
            <li>Corrections divers dans le traitement de la file d'attente des photos</li>
            <li>Ajout du largHeap & Hardware acceleration (meilleur gestion mémoire pour l'appareil photo)</li>
          </ul>
        </div>
        <div class="col">
          <h5><span>CRM</span></h5>
          <ul>
            <li>Debug : récéption des logs internes et du contenu du répertoire de synchronisation</li>
          </ul>
        </div>
      </div>
    </div>

    <div>
      <strong>v2.15 <small> &bull; du 01/06/2022</small></strong><br/>
      <div class="row">
        <div class="col">
          <h5><span>APK</span></h5>
          <ul>
            <li>Meilleur gestion de la file d'attente en cas d'erreurs</li>
            <li>"Mes clients" est désormais récupéré depuis le serveur (et non plus déduit du planning)</li>
          </ul>
        </div>
        <div class="col">
          <h5><span>CRM</span></h5>
          <ul>
            <li>Statistiquess : diverses corections et évolutions (ajout colonnes code client, visite&commande à zéro...)</li>
          </ul>
        </div>
      </div>
    </div>

  <div>
      <strong>v2.14 <small> &bull; du 11/05/2022</small></strong><br/>
      <div class="row">
        <div class="col">
          <h5><span>APK</span></h5>
          <ul>
            <li>Accélération du traitement de la file d'attente</li>
            <li>Fiche client : possibilité de consulter les notes/remarques laissées par les collaborateurs</li>
            <li>Fiche client : possibilité d'écrire une note/remarque pour un client</li>
            <li>Messagerie : intégration de la nouvelle messagerie</li>
            <li>Notifications (accueil): ajout d'une notification en cas de message dans la messagerie</li>
          </ul>
        </div>
        <div class="col">
          <h5><span>CRM</span></h5>
          <ul>
            <li>Fiche client : Possibilité de constulter les notes laissées par les promoteurs</li>
            <li>Messagerie : intégration de la nouvelle messagerie</li>
            <li>Partage de photos : dans les photos des visites, il est désormais possible de partager une photo via la messagerie</li>
          </ul>
        </div>
      </div>
    </div>


    <div>
      <strong>v2.13 <small> &bull; du 21/04/2022</small></strong><br/>
      <div class="row">
        <div class="col">
          <h5><span>APK</span></h5>
          <ul>
            <li>Passage du traitement de la file d'attente de 5 minutes à 2 minutes</li>
          </ul>
        </div>
        <div class="col">
          <h5><span>CRM</span></h5>
          <ul>
          </ul>
        </div>
      </div>
    </div>


    <div>
      <strong>v2.12 <small> &bull; du 14/04/2022</small></strong><br/>
      <div class="row">
        <div class="col">
          <h5><span>APK</span></h5>
          <ul>
            <li>Refonte complète du système d'envoi au serveur & file d'attente</li>
          </ul>
        </div>
        <div class="col">
          <h5><span>CRM</span></h5>
          <ul>
          </ul>
        </div>
      </div>
    </div>


    <div>
      <strong>v2.11 <small> &bull; du 11/04/2022</small></strong><br/>
      <div class="row">
        <div class="col">
          <h5><span>APK</span></h5>
          <ul>
            <li>Scan : possibilité de lire les QR-code</li>
            <li>Debug : allégement du contenu pour éviter les fichiers tronqués</li>
            <li>File d'attente : ajout d'un nettoyage supplémentaire de sécurité pour les éléments envoyés</li>
          </ul>
        </div>
        <div class="col">
          <h5><span>CRM</span></h5>
          <ul>
            <li>Statistiques : ajout des tableaux de statistiques</li>
          </ul>
        </div>
      </div>
    </div>


    <div>
      <strong>v2.10 <small> &bull; du 31/03/2022</small></strong><br/>
      <div class="row">
        <div class="col">
          <h5><span>APK</span></h5>
          <ul>
            <li>Commande sans visite : Ajout de la page de séléction/redaction de la raison</li>
            <li>Visite sans commande : Ajout de la page de séléction/redaction de la raison</li>
            <li>Franco de ports non atteints : Ajout de la page de séléction/redaction de la raison</li>
            <li>Nouvelle commande : par défaut, toutes les commandes auront une date de livraison souhaitée & date de prochaine visite à +7 jours (cela règle un bug calendrier)</li>
            <li>Nouvelle commande : affichage de la prochaine date de passage prévue dans le planning (si présente)</li>
          </ul>
        </div>
        <div class="col">
          <h5><span>CRM</span></h5>
          <ul>
            <li>Clients : ajout de la périodicité (+log de changements)</li>
            <li>Visites : retrait de la colonne des 15 dernières visites</li>
            <li>Visites : debug de la recherche des visites</li>
            <li>Commandes : refonte du tableau de bord des commandes</li>
            <li>Commandes & Visites : mise en mémoire des filtres de recherche</li>
          </ul>
        </div>
      </div>
    </div>
    <div>
      <strong>v2.9</strong><br/>
      <div class="row">
        <div class="col">
          <h5><span>APK</span></h5>
          <ul>
            <li>Correction d'un bug provoqué lors de la deconnexion quand le user{} des commandes est vide</li>
            <li>Supression de certains messages d'erreur</li>
          </ul>
        </div>
        <div class="col">
          <h5><span>CRM</span></h5>
        </div>
      </div>
    </div>
    <div>
      <strong>v2.8</strong><br/>
      <div class="row">
        <div class="col">
          <h5><span>APK</span></h5>
          <ul>
            <li>Correction d'un bug "Mes clients" n'affichant pas les bons clients</li>
          </ul>
        </div>
        <div class="col">
          <h5><span>CRM</span></h5>
        </div>
      </div>
    </div>
    <div>
      <strong>v2.7</strong><br/>
      <div class="row">
        <div class="col">
          <h5><span>APK</span></h5>
          <ul>
            <li>Ajout garde fou pour prévenir des commandes et visites sans utilisateurs</li>
            <li>Passage du traitement de la file d'attente à 5 minutes</li>
          </ul>
        </div>
        <div class="col">
          <h5><span>CRM</span></h5>
        </div>
      </div>
    </div>
    <div>
      <strong>v2.6</strong><br/>
      <div class="row">
        <div class="col">
          <h5><span>APK</span></h5>
          <ul>
            <li>Debug : Ajout du menu "Debug" qui envoi global.commandes au serveur</li>
          </ul>
        </div>
        <div class="col">
          <h5><span>CRM</span></h5>
          <ul>
            <li>Admin > Debug : lecture des débugs APK</li>
          </ul>
        </div>
      </div>
    </div>
    <div>
      <strong>v2.4</strong><br/>
      <div class="row">
        <div class="col">
          <h5><span>APK</span></h5>
          <ul>
            <li>Correction bug planning & mes clients</li>
          </ul>
        </div>
        <div class="col">
          <h5><span>CRM</span></h5>

        </div>
      </div>
    </div>
    <div>
      <strong>v2.3</strong><br/>
      <div class="row">
        <div class="col">
          <h5><span>APK</span></h5>
          <ul>
            <li>Planning : possibilité de faire un appui long sur une visite pour la passer en verte</li>
            <li>Planning : chargement du planning sur 6 mois (-3 à +3)</li>
            <li>Mes clients : affichage des clients d'un prospecteur sur 6 mois (-3 à +3)</li>
            <li>Accueil : supression du bouton "Plus" au bas de la page d'accueil</li>
            <li>BUG : "visite non présente dans le planning" => en réalité, le client n'existait pas dans l'application car "commande_par = 0" mais "livre = 1", donc envoi dans l'application des clients "livre" => problème réglé</li>
            <li>Visites : ajout de la possibilité de supprimer une visite/commande qui a été démarrée par erreur</li>
          </ul>
        </div>
        <div class="col">
          <h5><span>CRM</span></h5>
          <ul>
              <li>Planning : sauvegarde des visites passées en vert dans le CRM</li>
              <li>Planning : possibilité de consulter un planning de représentant</li>
              <li>Planning : possibilité de choisir une date de début et une date de fin pour la consultation du planning</li>
              <li>Fiche client : ajout de la DN du magasin dans la fiche client</li>
              <li>DN & Logs : ajout de logs dans le LiveLogs en cas de souci d'intégration de la DN</li>
              <li>Photos : Ajout d'un système de galerie photo permettant de naviguer entre les photos en pleine page</li>
          </ul>
        </div>
      </div>
    </div>


    <div>
      <strong>v2.2</strong><br/>
      <div class="row">
        <div class="col">
          <h5><span>APK</span></h5>
          <ul>
            <li>Planning : Correction d'un bug rendant les interractions inactives (boutons non fonctionnels)</li>
            <li>Commande : Ajout de la possibilité de créer une commande sans visite</li>
          </ul>
        </div>
        <div class="col">
          <h5><span>CRM</span></h5>
          <ul>
              <li>Commande : modification de la colonne "Visite liée" pour les commandes sans visite (affichage de la raison) </li>
          </ul>
        </div>
      </div>
    </div>

    <div>
      <strong>v2.1</strong><br/>
      <div class="row">
        <div class="col">
          <h5><span>APK</span></h5>
          <ul>
            <li>Commande : Date souhaitée de livraison & Date prochaine visite corrigées</li>
            <li>Mes commandes en cours : correction nom du client différent entre affiché et dans la commande</li>
            <li>Visite : correction du bug de DN vide</li>
          </ul>
        </div>
        <div class="col">
          <h5><span>CRM</span></h5>
          <ul>
              <li>Livelogs : Mise en couleurs des logs</li>
          </ul>
        </div>
      </div>
    </div>

    <div>
      <strong>v0.42</strong><br/>
      <div class="row">
        <div class="col">
          <h5><span>APK</span></h5>
          <ul>
            <li>Visites : envoi au serveur de l'ID AS400 au lieu de l'ID de CRM</li>
            <li>Récap commande : correction du scroll</li>
            <li>Correction divers bugs</li>
          </ul>
        </div>
        <div class="col">
          <h5><span>CRM</span></h5>
          <ul>
            <li>Visites : adaptation de l'ID AS400 du client dans toutes les visites</li>
          </ul>
        </div>
      </div>
    </div>


    <div>
      <strong>v0.41</strong><br/>
      <div class="row">
        <div class="col">
          <h5><span>APK</span></h5>
          <ul>
            <li>Envoi des visites : refonte du système d'envoi des photos pour éviter les crash mémoires</li>
            <li>Accueil : ajout d'un bandeau visuel lors de la synchronisation avec le serveur (envoi des visites & commandes)</li>
            <li>Accueil : Supression du lien "Toutes mes visites"</li>
            <li>Accueil : Activation du bouton "Calendrier"</li>
            <li>Accueil : les visites déjà effectuées apparaitront sur font vert clair</li>
            <li>Accueil : il est désormais possible de cliquer sur Objectif/CA pour le rafraichir</li>
            <li>Système : appel de la synchronisation dès que l'utilisateur se connecte a internet</li>
          </ul>
        </div>
        <div class="col">
          <h5><span>CRM</span></h5>
          <ul>
            <li>Admin : ajout d'un encart permettant de recalculer en live le CA d'un promoteur</li>
            <li>Admin : ajout des logs decommunication en temps réel entre l'APK et le CRM</li>
            <li>Commandes : ajout de la page "Détails de la commande"</li>
          </ul>
        </div>
      </div>
    </div>

    <div>
      <strong>v0.40</strong><br/>
      <div class="row">
        <div class="col">
          <h5><span>APK</span></h5>
          <ul>
            <li>Intégration corrigée du planning des visites</li>
            <li>Ajout de la page de consultation du planning (Toutes mes visites)</li>
            <li>Correction bug menu pastille mes commandes en cours</li>
            <li>Visites : refonte de l'interface des visites</li>
          </ul>
        </div>
        <div class="col">
          <h5><span>CRM</span></h5>
          <ul></ul>
        </div>
      </div>
    </div>

    <div>
      <strong>v0.39</strong><br/>
      <div class="row">
        <div class="col">
          <h5><span>APK</span></h5>
          <ul>
            <li>Refonte complète du système de file d'attente pour envoyer les éléments un par un</li>
            <li>Correction d'un problème d'affichage dans "Mes commandes en cours" si pas de commande mais visite envoyée</li>
          </ul>
        </div>
        <div class="col">
          <h5><span>CRM</span></h5>
          <ul></ul>
        </div>
      </div>
    </div>

    <div>
      <strong>v0.38</strong><br/>
      <div class="row">
        <div class="col">
          <h5><span>APK</span></h5>
          <ul>
            <li>File d'attente : problème d'actualisation de la file d'attente corrigé</li>
            <li>Thème sombre : tentative de correction de certains boutons sombres</li>
            <li>Accueil : ajustement de la taille du courant/objectif K€</li>
          </ul>
        </div>
        <div class="col">
          <h5><span>CRM</span></h5>
          <ul></ul>
        </div>
      </div>
    </div>

    <div>
      <strong>v0.37</strong><br/>
      <div class="row">
        <div class="col">
          <h5><span>APK</span></h5>
          <ul>
            <li>Commande : correction d'un bug de défillement dans la liste des produits</li>
            <li>Commande : Ajout de l'ID as 400, du prix unitaire et prix total</li>
            <li>Accueil : le bouton "Mes clients" affiche désormais le référentiel client filtré par les clients du planning du promoteur</li>
            <li>Commande : possibilité de mettre en attente une commande</li>
            <li>Memoire : correction d'un bug de surcharge potentiel de mémoire lors du chargement de l'application</li>
          </ul>
        </div>
        <div class="col">
          <h5><span>CRM</span></h5>
          <ul></ul>
        </div>
      </div>
    </div>





    <div>
      <strong>v0.36</strong><br/>
      <div class="row">
        <div class="col">
          <h5><span>APK</span></h5>
          <ul>
            <li>Corrections divers bugs</li>
            <li>Modification du traitement de la syncQueue (file d'attente de traitement)</li>
            <li>Timer de la syncQueue passé à 600 secondes (10min), avant : 5 secondes</li>
          </ul>
        </div>
        <div class="col">
          <h5><span>CRM</span></h5>
          <ul>
            <li>Ajout de fonctionnalités de prévention d'erreurs lors de l'intégration des commandes & visites</li>
            <li>Logs développeur plus verbeux en cas d'erreur</li>
          </ul>
        </div>
      </div>
    </div>


    <div>
      <strong>v0.35</strong><br/>
      <div class="row">
        <div class="col">
          <h5><span>APK</span></h5>
          <ul>
            <li><strong>Bug couleur frnco de port :</strong> PROBLEME RESOLU</li>
            <li><strong>Bug validation commande :</Strong> PROBLEME RESOLU</li>
            <li>Retrait du mode fullscreen de l'application (barres haut et bas android de retour)</li>
            <li>Visite : lors des séléctions dans la DN, le bouton retour fait retourner sur la DN</li>
          </ul>
        </div>
        <div class="col">
          <h5><span>CRM</span></h5>
          <ul>
            <li>Réinjection du référentiel facture</li>
          </ul>
        </div>
      </div>
    </div>











    <div>
      <strong>v0.34</strong><br/>
      <div class="row">
        <div class="col">
          <h5><span>APK</span></h5>
          <ul>
            <li><strong>Bug Synchro/Maj : PROBLEME RESOLU</strong></li>
            <li><strong>BUG :</Strong> correction d'un bug de la 0.33 qui empéchait d'entrer dans l'écran de saisie des commandes</li>
            <li>Planning : récupération et affichage des visites du jour depuis le planning</li>
          </ul>
        </div>
        <div class="col">
          <h5><span>CRM</span></h5>
          <ul>
            <li>Planning : Intégration du planning CSV</li>
            <li>Planning : API d'exportation du planning promoteur vers l'APK</li>
          </ul>
        </div>
      </div>
    </div>


    <div>
      <strong>v0.33</strong><br/>
      <div class="row">
        <div class="col">
          <h5><span>APK</span></h5>
          <ul>
            <li>Commande : ajout de la <strong>demande de la raison</strong> si le franco de port n'est pas atteint</li>
            <li>Fiche client : remontée du <strong>CA, nb commandes, nb visites</strong> de l'année en cours</li>
            <li>Fiche client : remontée des <strong>Promos</strong> présentes en magasin (issue de la dernière visite)</li>
            <li>Visite / Dn ABC : modification du metrage, <strong>passage en liste séléctionnable</strong></li>
            <li>Visite / DN Concurence : Les marques, gammes et metrage <strong>ne sont plus des textes libres mais des listes</strong></li>
            <li>Commande : passage du <strong>franco de port</strong> à 700€</li>
            <li>Accueil : récupération via API (1/j) de l'<strong>objectif commercial</strong> et de la progression du promoteur</li>
          </ul>
        </div>
        <div class="col">
          <h5><span>CRM</span></h5>
          <ul>
            <li>Commandes : pour toute commande < <?php echo FRANCO_DE_PORT;?>€, ajout d'un icon d'information qui contient la raison pour laquelle le franco de port n'est pas atteint</li>
            <li>Admin & Sécurité : Ajout de la barre de navigation dans l'administration permettant de se rendre dans la section sécurité</li>
            <li>Admin & Sécurité : Ajout des <strong>profils de sécurité</strong> avec la possibilité de créer, modifier et supprimer des profils</li>
            <li>Admin & Sécurité : Ajout du module d'attribution de droits spécifiques à un profil</li>
            <li>Affichage du profile de sécurité de l'utilisateur dans le menu latéral</li>
            <li>Utilisateur : Possibilité pour un administrateur de changer le profil de sécurité d'un utilisateur</li>
            <li>Objectifs promoteurs : injection en base du référentiels des objectifs + api de récupération de l'info</li>
            <li>AS400 : correction fichier commande pour AS400, l'ID de CRM de la commande est désormais bien placé à droite</li>
          </ul>
        </div>
      </div>
    </div>

    <div>
      <strong>v0.32</strong><br/>
      <div class="row">
        <div class="col">
          <h5><span>APK</span></h5>
          <ul>
            <li>Fiche client : les visites sont désormais mise à jour automatiquement (si on vient de faire une visite, on la retrouve dans le détails fiche client via une actualisation serveur, necessite internet)</li>
            <li>Commande & Récap : le total (€) commandé passe en vert/rouge si inf/sup à <?php echo FRANCO_DE_PORT;?>€</li>
            <li>Fiche client : correction du bug text bouton "Visite" (Débuter/modifier/terminer la visite)</li>
            <li>Fiche client : masquage des fonctionnalités en cours de développement</li>
            <li>Menu : masquer temporairement le champ de recherche</li>
          </ul>
        </div>
        <div class="col">
          <h5><span>CRM</span></h5>
          <ul>
            <li>Diverses corrections commandes/visites (bug insertion prix à 0, multiple visite même jour chez le client...)</li>
          </ul>
        </div>
      </div>
    </div>

    <div>
      <strong>v0.31</strong><br/>
      <div class="row">
        <div class="col">
          <h5><span>APK</span></h5>
          <ul>
            <li>Visite : (Réorganisation des étapes) Passage des <strong>photos vue de face après le contrôle des OP</strong></li>
            <li>Visite : renommage de plusieurs éléments ( Planogramme => Photos vue de face...)</li>
            <li>Visite : Si pas de commande, ajout du champ pour <strong>préciser la raison</strong></li>
            <li>Menu : renommage "Mes commandes & visites" en "Commandes et Visites en cours"</li>
            <li>Visite : refonte du système de saisie de la <strong>DN</strong> (tableau ABC/Concurence)</li>
            <li>Fiche client : possibilité de consulter les dernieres commandes + détails des commandes + Ajout du total HT facturé des commandes</li>
            <li>Fiche client : possibilité de consulter les dernieres visites</li>
            <li>Fiche client : possibilité de consulter les photos des dernières visites</li>
            <li>Ajout de la détéction dans l'application lorsque le serveur GESCOM est hors ligne</li>
            <li>Commande : Ajout d'un message de confirmation avant d'aller au recap</li>
            <li>Récap : Ajout d'un message de confirmation avant d'envoyer à ABC</li>
          </ul>
        </div>
        <div class="col">
          <h5><span>CRM</span></h5>
          <ul>
            <li>Commande : alignement à droite du numéro ID commande CRM dans les fichiers générés pour l'AS400</li>
            <li>Visite: Réorganisation des timing des étapes suite au déplacement des photos vue de face & renommage</li>
            <li>Visite : ajout de l'affichage de la raison si pas de commande</li>
            <li>Visite : refonte du système de visualisation de la <strong>DN</strong> (tableau ABC/Concurence)</li>
            <li>BDD APK : ajout des produits a tarif à 0€ (ex: gamme COSMIA)</li>
            <li>Commande AS400 : ajout du code client MINOS dans le champ "client livré" du fichier d'export</li>
          </ul>
        </div>
      </div>
    </div>


    <div>
      <strong>v0.30</strong><br/>
      <div class="row">
        <div class="col">
          <h5><span>APK</span></h5>
          <ul>
            <li>Désormais, les boutons + et - lors de l'<strong>édition de quantité</strong> des produits dans une commande ajoutent ou soustraient le PCB et non plus 1</li>
            <li>Possibilité de <strong>modifier manuellement</strong> la quantité d'un produit (avec contrôle multiple du PCB)</li>
            <li>Date de livraison estimée et prochaine visite : <strong>affichage d'un calendrier</strong> pour la séléction des dates (plutot qu'un spinner)</li>
            <li>Envoi auto du <strong>numéro de version</strong> de l'APK utilisé lors de chaque échange avec le serveur</li>
            <li>Tous les fichiers de base de données de l'APK sont désormais préfixés du numéro de version de l'APK afin d'être sur qu'en cas de montée de version, l'application n'utilise pas les fichiers de base de données des précédentes versions</li>
            <li>Forcage du redimensionnement de la photo avant enregistrement (max 800px)</li>
            <li>Ajout de l'historique des commandes (via Menu) qui permet d'accéder au détail des 100 dernières commandes passée par le promoteur</li>
          </ul>
        </div>
        <div class="col">
          <h5><span>CRM</span></h5>
          <ul>
            <li>OP/Promo : ajout fonction pour ordonner la liste OP/Promo</li>
            <li>OP/Promo : ajout fonction pour supprimer une OP/Promo</li>
            <li>Admin : ajout des informations sur l'espace de stockage serveur</li>
            <li>Utilisateurs : Ajout du numéro de version de l'APK ainsi que la date du dernier échange dans le tableau de bord</li>
            <li>Utilisateurs : diverses réorganisations du tableau de bord</li>
          </ul>
        </div>
      </div>
    </div>




    <div>
      <strong>v0.29</strong><br/>
      <ul>
        <li>Il n'est désormais plus possible d'envoyer une visite en doublon sur le serveur</li>
        <li>Suppression de toutes les visites qui ont été envoyées en plusieurs exemplaires au serveur</li>
        <li>Ajout de la possibilité de passer directement a la fin de visite si pas de commande</li>
        <li>CRM : correction d'un bug d'affichage dans les étapes de visites ( l'étape 0 "Nouvelle visite" n'était pas définie, ce qui affichait une erreur)</li>
        <li>Ajout de la page des notifications accessible depuis l'icone de la page d'accueil</li>
        <li>Ajout de la pastille des notifications</li>
        <li>Si il y a des éléments en attente de réseau et d'envoi au serveur => affichage d'une notification sur la home page de l'application</li>
        <li>Correction de la possibilité de retourner dans une commande alors qu'elle est en attente d'envoi au serveur</li>
        <li>CRM : correction dans "Visite", affichage du nom de l'enseigne à la place de la raison sociale</li>
        <li>CRM : dans "Visite" : Ajout de la colonne "Commande" avec l'ID de la commande et son total</li>
        <li>CRM : diverses corrections dans les tableaux d'affichage des visites et commandes</li>
        <li>CRM : Ajout des notes de version dans la partie administration</li>
      </ul>
    </div>

  </div>
</div>
