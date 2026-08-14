<?php
require_once 'config.php';

$cat_slug = isset($_GET['id']) ? $_GET['id'] : 'economie';

// Récupérer les infos de la catégorie
$stmt = $pdo->prepare("SELECT * FROM categories WHERE slug = ?");
$stmt->execute([$cat_slug]);
$category = $stmt->fetch();

if (!$category) {
    header("Location: index.php");
    exit;
}

$cat_name = $category['name'];
$cat_articles = getArticlesByCategory($pdo, $cat_slug, 20);
$categories_list = getCategories($pdo);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($cat_name) ?> — <?= $site_name ?></title>

    <!-- SEO -->
    <meta name="description" content="Retrouvez tous les articles de la catégorie <?= htmlspecialchars($cat_name) ?> sur <?= $site_name ?>.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="status-bar">
    <div class="wrap">
        <div class="status-left">
            <a href="index.php" class="back-link">← Accueil</a>
            <span class="sep">/</span>
            <span class="location"><?= htmlspecialchars($cat_name) ?></span>
        </div>
        <div class="status-right">
            <?php if(isUserLoggedIn()): ?>
                <span style="font-size:11px; margin-right:15px;">Salut, <b><?= htmlspecialchars($_SESSION['full_name']) ?></b></span>
                <?php if(isset($_SESSION['admin_logged_in'])): ?>
                    <a href="admin/index.php" style="font-size:11px; color:var(--blue); text-decoration:none; margin-right:15px; font-weight:bold;">Panel Admin</a>
                <?php endif; ?>
                <a href="logout_user.php" style="font-size:11px; color:var(--red); text-decoration:none;">Déconnexion</a>
            <?php else: ?>
                <a href="login.php" style="font-size:11px; margin-right:10px; text-decoration:none;">Connexion</a>
                <a href="register.php" style="font-size:11px; text-decoration:none;">Inscription</a>
            <?php endif; ?>
            <div class="theme-switch" onclick="toggleTheme()">
                <svg class="sun" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
            </div>
        </div>
    </div>
</div>

<header class="navbar">
    <div class="wrap">
        <a href="index.php" class="logo"><?= $site_name ?><span>.</span></a>
        <nav class="nav-links">
            <?php foreach($categories_list as $cat): ?>
                <a href="category.php?id=<?= $cat['slug'] ?>" <?= ($cat['slug'] == $cat_slug) ? 'class="active"' : '' ?>><?= $cat['name'] ?></a>
            <?php endforeach; ?>
        </nav>
        <div class="nav-actions">
            <form action="search.php" method="GET" class="search-form-nav">
                <input type="text" name="q" placeholder="Rechercher..." required>
                <button type="submit">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                </button>
            </form>
            <button class="btn-primary">S'abonner</button>
        </div>
    </div>
</header>

<main class="wrap">
    <header class="page-header" style="padding: 60px 0; border-bottom: 1px solid var(--line); margin-bottom: 40px;">
        <h1 style="font-size: 48px; letter-spacing: -0.03em;"><?= htmlspecialchars($cat_name) ?></h1>
        <p style="color: var(--gray); font-size: 18px; margin-top: 10px;">L'actualité <?= strtolower($cat_name) ?> au Mali et ailleurs.</p>
    </header>

    <div class="article-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 40px; padding-bottom: 80px;">
        <?php if(empty($cat_articles)): ?>
            <p>Aucun article n'a encore été publié dans cette catégorie.</p>
        <?php else: ?>
            <?php foreach($cat_articles as $art): ?>
                <article class="card">
                    <a href="article.php?id=<?= $art['id'] ?>">
                        <div class="card-img">
                            <img src="<?= $art['image'] ?: 'https://picsum.photos/seed/'.$art['id'].'/400/250' ?>" alt="<?= htmlspecialchars($art['title']) ?>">
                        </div>
                        <div class="card-content">
                            <span class="eyebrow" style="color: var(--blue); margin-bottom: 8px; display: block;"><?= date('d/m/Y', strtotime($art['created_at'])) ?></span>
                            <h3><?= htmlspecialchars($art['title']) ?></h3>
                            <p style="font-size: 14px; color: var(--gray); margin-top: 12px; line-height: 1.5;"><?= htmlspecialchars($art['excerpt']) ?></p>
                        </div>
                    </a>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>

<footer>
    <div class="wrap">
        <div class="foot-bottom">
            <span>© <?= date('Y') ?> <?= $site_name ?></span>
            <div class="foot-legal">
                <a href="index.php">Accueil</a>
                <a href="#">Contact</a>
            </div>
        </div>
    </div>
</footer>

<script src="script.js"></script>
</body>
</html>
