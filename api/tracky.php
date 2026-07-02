<?php
/**
 * API Tracky - Point d'entree unique application mobile (format Legacy Base64 CRM)
 * Entree : corps JSON {"d":"base64(JSON)"}, ou $_POST['d'] / $_GET['d']. Sortie : {"d": "base64(JSON)"}.
 * Token : dans le payload decode ou en clair (?token= / Authorization). Toutes les routes protegees.
 *
 * GET  : ?d=base64({token, methode}) ou ?token=...&methode=get_retours|get_detail_bordereau (&id_bordereau=)|get_magasins|get_produit|get_stats (&ean= pour get_produit)
 * POST : body ou form d = base64(JSON) avec token + donnees retours (objet ou tableau), upsert si doublon meme jour.
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../conf.php';

if (!isset($db) || !$db) {
    $db = new db();
}

/** Reponse Legacy : encapsule le JSON dans {"d": "base64(json)"} et arrete le script */
function trackyRep($data) {
    $json = json_encode($data);
    echo json_encode(['d' => base64_encode($json)]);
    exit;
}

// --- 1. Recuperation du payload : JSON brut { "d": "..." } puis POST/GET ---
$input = file_get_contents('php://input');
$jsonBody = (is_string($input) && $input !== '') ? json_decode($input, true) : null;
if (!is_array($jsonBody)) {
    $jsonBody = [];
}

$b64 = null;
if (isset($jsonBody['d'])) {
    $b64 = $jsonBody['d'];
} elseif (isset($_POST['d'])) {
    $b64 = $_POST['d'];
} elseif (isset($_GET['d'])) {
    $b64 = $_GET['d'];
}

$paramsFromD = null;
if ($b64 === null || $b64 === '') {
    // GET sans corps : token / methode en query (comportement historique)
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $paramsFromD = null;
    } else {
        trackyRep(['error' => 'Invalid payload']);
    }
} else {
    $decoded = @base64_decode($b64, true);
    if ($decoded === false || $decoded === '') {
        trackyRep(['error' => 'Invalid Encoding']);
    }
    $paramsFromD = json_decode($decoded, true);
    if (!is_array($paramsFromD)) {
        trackyRep(['error' => 'Invalid payload']);
    }
}

// --- Recuperation du token (payload d ou URL/header) ---
$token = null;
if (is_array($paramsFromD) && !empty($paramsFromD['token'])) {
    $token = $paramsFromD['token'];
}
if (empty($token) && !empty($_GET['token'])) {
    $token = trim($_GET['token']);
}
if (empty($token) && !empty($_SERVER['HTTP_AUTHORIZATION'])) {
    $auth = trim($_SERVER['HTTP_AUTHORIZATION']);
    if (stripos($auth, 'Bearer ') === 0) {
        $token = trim(substr($auth, 7));
    } else {
        $token = $auth;
    }
}
// Si le token est envoye en Base64, le decoder avant tokenCheck
if (!empty($token)) {
    $decodedToken = @base64_decode($token, true);
    if ($decodedToken !== false && preg_match('/^[\x20-\x7E]+$/', $decodedToken)) {
        $token = $decodedToken;
    }
}

if (empty($token)) {
    trackyRep(['error' => 'Unauthorized']);
}

$id_user = login::tokenCheck($token);
if ($id_user === false || $id_user < 1) {
    trackyRep(['error' => 'Unauthorized']);
}

$user_id = (int) $id_user;
if ($user_id < 1) {
    trackyRep(['error' => 'Unable to resolve user from token']);
}

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8',
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    error_log('[tracky.php] PDO connect: ' . $e->getMessage());
    trackyRep(['error' => 'Database error']);
}

// Colonne id_bordereau (VARCHAR 50) si absente — avant tout SELECT/INSERT sur cette table
try {
    $hasCol = (int) $pdo->query(
        "SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'retours_produits_apk' AND COLUMN_NAME = 'id_bordereau'"
    )->fetchColumn();
    if ($hasCol === 0) {
        $pdo->exec("ALTER TABLE retours_produits_apk ADD COLUMN id_bordereau VARCHAR(50) NULL DEFAULT NULL AFTER user_id");
    }
} catch (PDOException $e) {
    error_log('[tracky.php] id_bordereau column: ' . $e->getMessage());
}

try {
    $hasTbl = (int) $pdo->query(
        "SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'bordereaux_index'"
    )->fetchColumn();
    if ($hasTbl === 0) {
        $pdo->exec(
            "CREATE TABLE bordereaux_index (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                user_id INT UNSIGNED NOT NULL,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );
    }
} catch (PDOException $e) {
    error_log('[tracky.php] bordereaux_index table: ' . $e->getMessage());
}

$methode = $paramsFromD['methode'] ?? $_GET['methode'] ?? $_POST['methode'] ?? null;
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $methode === null) {
    $methode = 'get_retours';
}

// --- Routage par methode (GET et POST) ---
switch ($methode) {
    case 'get_retours': {
        $stmt = $pdo->prepare("
            SELECT
                MAX(id_bordereau) AS id_bordereau,
                MAX(code_magasin) AS code_magasin,
                MAX(date_retour) AS date_visite,
                MAX(photo_path) AS photo,
                COUNT(*) AS nb_articles
            FROM retours_produits_apk
            WHERE user_id = :user_id
            GROUP BY COALESCE(NULLIF(TRIM(id_bordereau), ''), CONCAT('_legacy_', id)), user_id
            ORDER BY MAX(date_retour) DESC
        ");
        $stmt->execute([':user_id' => $user_id]);
        $retours = $stmt->fetchAll(PDO::FETCH_ASSOC);
        trackyRep(['retours' => $retours]);
        exit;
    }

    case 'get_detail_bordereau': {
        $idBordereau = trim((string) ($paramsFromD['id_bordereau'] ?? $_GET['id_bordereau'] ?? ''));
        if ($idBordereau === '') {
            trackyRep(['error' => 'Parametre id_bordereau requis']);
            exit;
        }
        $stmt = $pdo->prepare('SELECT * FROM retours_produits_apk WHERE id_bordereau = :id_bordereau AND user_id = :user_id ORDER BY id ASC');
        $stmt->execute([':id_bordereau' => $idBordereau, ':user_id' => $user_id]);
        $lignes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        trackyRep(['lignes' => $lignes]);
        exit;
    }

    case 'get_magasins': {
        $stmt = $pdo->query("SELECT id, id_as400 AS code, enseigne AS nom FROM ref_client WHERE deleted = 0 AND actif = 1 ORDER BY nom");
        $magasins = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        trackyRep(['magasins' => $magasins]);
        exit;
    }

    case 'get_produit': {
        $ean = trim($paramsFromD['ean'] ?? $paramsFromD['code'] ?? $_GET['ean'] ?? $_GET['code'] ?? '');
        if ($ean === '') {
            trackyRep(['error' => 'Parametre ean ou code requis']);
            exit;
        }
        $stmt = $pdo->prepare("SELECT a.id, a.id_as400, a.libelle, a.gencode, a.retour_autorise FROM ref_article a WHERE (a.id_as400 = :ean OR a.gencode = :ean2) AND a.deleted = 0 AND a.actif = 1 LIMIT 1");
        $stmt->execute([':ean' => $ean, ':ean2' => $ean]);
        $produit = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$produit) {
            http_response_code(404);
            trackyRep(['error' => 'Produit non trouve']);
            exit;
        }
        $stmtTarif = $pdo->prepare("SELECT tarif FROM ref_tarif WHERE code_article = :id AND deleted = 0 LIMIT 1");
        $stmtTarif->execute([':id' => $produit['id_as400']]);
        $prix = $stmtTarif->fetchColumn();
        $stmtLots = $pdo->prepare("SELECT num_lot FROM ref_article_lot WHERE id_as400_article = :ref");
        $stmtLots->execute([':ref' => $produit['id_as400']]);
        $lots_possibles = $stmtLots->fetchAll(PDO::FETCH_COLUMN, 0);
        trackyRep([
            'id'                => (int) $produit['id'],
            'nom_produit'       => $produit['libelle'],
            'reference'         => $produit['id_as400'],
            'ean'               => $produit['gencode'] ?? '',
            'prix'              => $prix !== false ? (float) $prix : null,
            'retour_autorise'   => (int) ($produit['retour_autorise'] ?? 1),
            'lots_possibles'    => $lots_possibles,
        ]);
        exit;
    }

    case 'get_stats': {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM retours_produits_apk WHERE user_id = :user_id AND DATE(date_retour) = CURDATE()");
        $stmt->execute([':user_id' => $user_id]);
        $retours_aujourdhui = (int) $stmt->fetchColumn();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM retours_produits_apk WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $user_id]);
        $retours_total = (int) $stmt->fetchColumn();
        trackyRep([
            'retours_aujourdhui' => $retours_aujourdhui,
            'retours_total'      => $retours_total,
        ]);
        exit;
    }

    case 'get_sync_data': {
        // 1. Récupération des magasins (enseignes)
        $stmtMag = $pdo->query("SELECT id_as400 AS id, enseigne AS nom FROM ref_client WHERE deleted = 0 AND actif = 1 ORDER BY nom ASC");
        $magasins = $stmtMag->fetchAll(PDO::FETCH_ASSOC);

        // 2. Récupération des produits (articles)
        $stmtArt = $pdo->query("SELECT gencode AS ean, libelle AS nom, id_as400 AS ref, 0.00 AS prix, retour_autorise 
        FROM ref_article 
        WHERE deleted = 0 
        AND actif = 1 
        AND retour_autorise = 1"); $produits = $stmtArt->fetchAll(PDO::FETCH_ASSOC);

        // 3. Récupération des lots (ref_article_lot)
        $stmtLots = $pdo->query("SELECT id_as400_article AS id_article, num_lot FROM ref_article_lot");
        $lots = $stmtLots ? $stmtLots->fetchAll(PDO::FETCH_ASSOC) : [];

        trackyRep([
            'magasins'      => $magasins,
            'produits'      => $produits,
            'lots'          => $lots,
            'count_magasins' => count($magasins),
            'count_produits' => count($produits),
            'count_lots'    => count($lots),
        ]);
        exit;
    }

    case 'set_retour': {
        // Insertion photo + lignes : voir bloc POST ci-dessous ($paramsFromD['data'], ['photo'])
        break;
    }

    default: {
        if ($methode !== 'set_retour' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(404);
            trackyRep(['error' => 'Unknown method']);
        }
        break;
    }
}

// --- POST : synchronisation retours (payload: photo + data[]) ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    trackyRep(['error' => 'Method Not Allowed']);
}

$data = $paramsFromD;
if (!is_array($data)) {
    trackyRep(['error' => 'Invalid payload']);
}

// Payload : photo (base64 optionnel) + data (tableau de lignes)
if (!isset($data['data']) || !is_array($data['data'])) {
    trackyRep(['error' => 'Invalid payload: expected "data" array']);
}

$items = $data['data'];
$required = ['code_magasin', 'num_lot', 'scan_produit', 'code_produit', 'quantite', 'action_produit', 'date_retour'];
foreach ($items as $idx => $item) {
    if (!is_array($item)) {
        trackyRep(['error' => 'Invalid item at index ' . $idx]);
    }
    foreach ($required as $key) {
        if (!array_key_exists($key, $item)) {
            trackyRep(['error' => 'Missing field: ' . $key . ' (item ' . $idx . ')']);
        }
    }
    $action_produit = trim((string) ($item['action_produit'] ?? ''));
    if (!in_array($action_produit, ['detruit', 'recupere', 'absent'], true)) {
        trackyRep(['error' => 'action_produit must be "detruit", "recupere" or "absent" (item ' . $idx . ')']);
    }
}

$sqlInsert = "INSERT INTO retours_produits_apk (user_id, id_bordereau, code_magasin, num_lot, scan_produit, code_produit, quantite, action_produit, date_retour, photo_path) VALUES (:user_id, :id_bordereau, :code_magasin, :num_lot, :scan_produit, :code_produit, :quantite, :action_produit, NOW(), :photo_path)";
$stmtInsert = $pdo->prepare($sqlInsert);
$stmtBordereau = $pdo->prepare('INSERT INTO bordereaux_index (user_id) VALUES (:user_id)');

$ids = [];
try {
    $pdo->beginTransaction();

    $stmtBordereau->execute([':user_id' => $user_id]);
    $newId = (int) $pdo->lastInsertId();
    $id_bordereau = 'RAPPEL_' . $user_id . '_' . str_pad((string) $newId, 5, '0', STR_PAD_LEFT);

    // Photo : meme nom que l'ID bordereau
    $photo_name = null;
    if (!empty($data['photo']) && is_string($data['photo'])) {
        $raw = base64_decode($data['photo'], true);
        if ($raw !== false && strlen($raw) > 0) {
            $uploadDir = (defined('FILES') ? FILES : (__DIR__ . '/../datas/')) . 'uploads/retours/';
            if (!is_dir($uploadDir)) {
                @mkdir($uploadDir, 0755, true);
            }
            if (is_dir($uploadDir) && is_writable($uploadDir)) {
                $photo_name = preg_replace('/[^a-zA-Z0-9_.-]/', '_', $id_bordereau) . '.jpg';
                $path = $uploadDir . $photo_name;
                if (@file_put_contents($path, $raw) === false) {
                    error_log('[tracky.php] Failed to save photo: ' . $path);
                    $photo_name = null;
                }
            } else {
                error_log('[tracky.php] Upload dir not writable: ' . $uploadDir);
            }
        }
    }

    foreach ($items as $item) {
        $stmtInsert->execute([
            ':user_id'        => $user_id,
            ':id_bordereau'   => $id_bordereau,
            ':code_magasin'   => trim((string) ($item['code_magasin'] ?? '')),
            ':num_lot'        => trim((string) ($item['num_lot'] ?? '')),
            ':scan_produit'   => trim((string) ($item['scan_produit'] ?? '')),
            ':code_produit'   => trim((string) ($item['code_produit'] ?? '')),
            ':quantite'       => (int) ($item['quantite'] ?? 0),
            ':action_produit' => trim((string) ($item['action_produit'] ?? '')),
            ':photo_path'     => $photo_name,
        ]);
        $ids[] = (int) $pdo->lastInsertId();
    }
    $pdo->commit();
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('[tracky.php] DB error: ' . $e->getMessage());
    trackyRep(['error' => 'Database error']);
}

// Génération du fichier RAPxxxx.txt dans le même dossier que les commandes
// (uniquement si au moins une ligne possède une quantité > 0)
try {
    if (class_exists('retours')) {
        $rapFile = retours::generateRappelTxtFromBordereau($id_bordereau, $user_id, $newId, $pdo);
        if ($rapFile) {
            error_log('[tracky.php] RAP export generated: ' . $rapFile . ' for ' . $id_bordereau);
        } else {
            error_log('[tracky.php] RAP export skipped (no qty > 0 or missing context) for ' . $id_bordereau);
        }
    } else {
        error_log('[tracky.php] class retours not found, RAP export skipped for ' . $id_bordereau);
    }
} catch (Throwable $e) {
    error_log('[tracky.php] RAP export error: ' . $e->getMessage());
}

http_response_code(201);
trackyRep(['success' => true, 'id_bordereau' => $id_bordereau, 'count' => count($items), 'ids' => $ids]);
