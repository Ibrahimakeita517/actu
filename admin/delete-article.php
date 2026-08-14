<?php
require_once '../config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    try {
        // Optionnel : Supprimer le fichier image du serveur
        $stmt = $pdo->prepare("SELECT image FROM articles WHERE id = ?");
        $stmt->execute([$id]);
        $art = $stmt->fetch();
        if ($art && $art['image'] && file_exists('../' . $art['image'])) {
            unlink('../' . $art['image']);
        }

        $stmt = $pdo->prepare("DELETE FROM articles WHERE id = ?");
        $stmt->execute([$id]);
    } catch (Exception $e) {
        // Gérer l'erreur si nécessaire
    }
}

header('Location: index.php');
exit;
?>
