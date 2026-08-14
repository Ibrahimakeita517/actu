<?php
require_once 'config.php';

try {
    echo "<h1>Configuration des interactions...</h1>";

    // 1. Structure des articles
    $cols = [
        "status" => "ENUM('published', 'draft') DEFAULT 'published'",
        "published_at" => "DATETIME DEFAULT CURRENT_TIMESTAMP"
    ];
    foreach ($cols as $col => $definition) {
        try { $pdo->exec("ALTER TABLE articles ADD $col $definition"); } catch (Exception $e) {}
    }

    // 2. Table des LIKES
    $pdo->exec("CREATE TABLE IF NOT EXISTS article_likes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        article_id INT,
        user_id INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_like (article_id, user_id),
        FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");
    echo "<p style='color:green'>✅ Système de Likes prêt.</p>";

    // 3. Admin User (ID 999)
    $stmt = $pdo->prepare("SELECT id FROM users WHERE id = 999");
    $stmt->execute();
    if (!$stmt->fetch()) {
        $pass = password_hash('admin', PASSWORD_DEFAULT);
        $pdo->exec("INSERT INTO users (id, full_name, phone, password) VALUES (999, 'Keita (Admin)', '83221696', '$pass')");
    }

    echo "<hr><p><b>Configuration terminée !</b></p>";
    echo "<a href='index.php'>Aller sur le site</a>";

} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage();
}
?>
