<?php
// Initialisation de la session au tout début
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configuration de la base de données
$host = 'localhost';
$db   = 'actu_db';
$user = 'root'; // Par défaut sur Laragon
$pass = '';     // Par défaut sur Laragon (vide)
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

// Paramètres du site (Valeurs par défaut)
$site_name = "FLUX";
$site_tagline = "L'actualité du Mali et du Sahel en continu";
$base_url = "http://localhost/actu";

// Charger les paramètres depuis la base de données
try {
    $settings_query = $pdo->query("SELECT * FROM site_settings");
    $db_settings = [];
    while ($row = $settings_query->fetch()) {
        $db_settings[$row['setting_key']] = $row['setting_value'];
    }

    if (isset($db_settings['site_name'])) $site_name = $db_settings['site_name'];

    // Récupération précise des réglages de maintenance
    $maintenance_mode = $db_settings['maintenance_mode'] ?? '0';
    $maintenance_message = isset($db_settings['maintenance_message']) && !empty($db_settings['maintenance_message'])
                           ? $db_settings['maintenance_message']
                           : "Le site est actuellement en maintenance. Nous revenons bientôt.";

    // Sécurité : Admin n'est jamais bloqué
    $is_admin_path = strpos($_SERVER['REQUEST_URI'], '/admin/') !== false;
    $is_admin_logged = (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true);

    if ($maintenance_mode === '1' && !$is_admin_path && !$is_admin_logged) {
        die("<!DOCTYPE html>
        <html lang='fr'>
        <head>
            <meta charset='UTF-8'>
            <title>Maintenance — $site_name</title>
            <style>
                body { font-family: sans-serif; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; background: #0b0c10; color: #fff; text-align: center; }
                .box { background: #1a1c23; padding: 60px; border-radius: 30px; box-shadow: 0 20px 50px rgba(0,0,0,0.5); border: 1px solid #2d3139; max-width: 600px; }
                h1 { color: #3b82f6; font-size: 32px; margin-bottom: 20px; }
                p { line-height: 1.8; font-size: 20px; color: #9ca3af; }
                .logo { font-weight: 800; font-size: 30px; margin-bottom: 40px; display: block; letter-spacing: -1px; }
                .dot { color: #3b82f6; }
            </style>
        </head>
        <body>
            <div class='box'>
                <span class='logo'>$site_name<span class='dot'>.</span></span>
                <h1>Mode Maintenance</h1>
                <p>".nl2br(htmlspecialchars($maintenance_message))."</p>
            </div>
        </body>
        </html>");
    }
} catch (Exception $e) {
    // Si la table n'existe pas encore, on continue avec les valeurs par défaut
}

// Fonctions pour récupérer les données
function getCategories($pdo) {
    return $pdo->query("SELECT * FROM categories")->fetchAll();
}

function getLatestArticles($pdo, $limit = 10) {
    return $pdo->query("SELECT a.*, c.name as category_name FROM articles a
                        LEFT JOIN categories c ON a.category_id = c.id
                        WHERE a.status = 'published' AND a.published_at <= NOW()
                        ORDER BY published_at DESC LIMIT $limit")->fetchAll();
}

function getArticlesByCategory($pdo, $cat_slug, $limit = 6) {
    $stmt = $pdo->prepare("SELECT a.*, c.name as category_name FROM articles a
                           JOIN categories c ON a.category_id = c.id
                           WHERE c.slug = ? AND a.status = 'published' AND a.published_at <= NOW()
                           ORDER BY published_at DESC LIMIT ?");
    $stmt->execute([$cat_slug, $limit]);
    return $stmt->fetchAll();
}

function getArticleById($pdo, $id) {
    $stmt = $pdo->prepare("SELECT a.*, c.name as category_name FROM articles a
                           LEFT JOIN categories c ON a.category_id = c.id
                           WHERE a.id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// Nouvelles fonctions pour les commentaires et utilisateurs
function getCommentsByArticle($pdo, $article_id) {
    $stmt = $pdo->prepare("SELECT c.*, u.full_name FROM comments c
                           JOIN users u ON c.user_id = u.id
                           WHERE c.article_id = ?
                           ORDER BY c.created_at DESC");
    $stmt->execute([$article_id]);
    return $stmt->fetchAll();
}

function isUserLoggedIn() {
    global $pdo;

    if (isset($_SESSION['user_id'])) {
        // Vérifier si l'utilisateur existe toujours en DB pour éviter les erreurs de clé étrangère
        $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        if ($stmt->fetch()) {
            return true;
        } else {
            // Session invalide (utilisateur supprimé), on nettoie
            unset($_SESSION['user_id']);
            unset($_SESSION['full_name']);
        }
    }

    // Tentative de reconnexion automatique via cookie
    if (isset($_COOKIE['user_phone'])) {
        $phone = $_COOKIE['user_phone'];
        $stmt = $pdo->prepare("SELECT * FROM users WHERE phone = ?");
        $stmt->execute([$phone]);
        $user = $stmt->fetch();

        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['full_name'] = $user['full_name'];
            return true;
        }
    }

    return false;
}

function searchArticles($pdo, $query, $limit = 20) {
    $searchTerm = "%$query%";
    $stmt = $pdo->prepare("SELECT a.*, c.name as category_name FROM articles a
                           LEFT JOIN categories c ON a.category_id = c.id
                           WHERE a.title LIKE ? OR a.content LIKE ? OR a.excerpt LIKE ?
                           ORDER BY a.created_at DESC LIMIT ?");
    $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $limit]);
    return $stmt->fetchAll();
}

/**
 * Fonctions Admin & Stats
 */

function logActivity($pdo, $user_name, $action, $details = '') {
    $stmt = $pdo->prepare("INSERT INTO activity_log (user_name, action, details) VALUES (?, ?, ?)");
    $stmt->execute([$user_name, $action, $details]);
}

function getAdminStats($pdo) {
    $stats = [];

    // Total utilisateurs
    $stats['total_users'] = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

    // Total articles
    $stats['total_articles'] = $pdo->query("SELECT COUNT(*) FROM articles")->fetchColumn();

    // Publiés aujourd'hui
    $stats['published_today'] = $pdo->query("SELECT COUNT(*) FROM articles WHERE DATE(created_at) = CURDATE()")->fetchColumn();

    return $stats;
}
?>
