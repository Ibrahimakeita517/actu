<?php
require_once 'config.php';

$query = isset($_GET['q']) ? trim($_GET['q']) : '';
$results = [];

if (!empty($query)) {
    $results = searchArticles($pdo, $query);
}

$categories_list = getCategories($pdo);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recherche : <?= htmlspecialchars($query) ?> — <?= $site_name ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .search-header { padding: 60px 0; border-bottom: 1px solid var(--line); margin-bottom: 40px; }
        .search-header h1 { font-size: 32px; margin-bottom: 10px; }
        .search-stats { color: var(--gray); font-size: 14px; }
        .no-results { text-align: center; padding: 100px 0; }
        .search-bar-page { max-width: 600px; margin: 20px 0; display: flex; gap: 10px; }
        .search-bar-page input { flex: 1; padding: 12px 20px; border: 1px solid var(--line); border-radius: 30px; font-family: inherit; font-size: 16px; outline: none; transition: border-color 0.2s; }
        .search-bar-page input:focus { border-color: var(--blue); }
        .search-bar-page button { padding: 10px 25px; border-radius: 30px; background: var(--ink); color: #fff; border: none; cursor: pointer; font-weight: 600; }
    </style>
</head>
<body>

<div class="status-bar">
    <div class="wrap">
        <div class="status-left">
            <a href="index.php" class="back-link">← Retour à l'accueil</a>
        </div>
        <div class="status-right">
            <?php if(isUserLoggedIn()): ?>
                <span style="font-size:11px; margin-right:15px;">Salut, <b><?= htmlspecialchars($_SESSION['full_name']) ?></b></span>
                <a href="logout_user.php" style="font-size:11px; color:var(--red); text-decoration:none;">Déconnexion</a>
            <?php else: ?>
                <a href="login.php" style="font-size:11px; margin-right:10px; text-decoration:none;">Connexion</a>
                <a href="register.php" style="font-size:11px; text-decoration:none;">Inscription</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<header class="navbar">
    <div class="wrap">
        <a href="index.php" class="logo"><?= $site_name ?><span>.</span></a>
        <nav class="nav-links">
            <a href="index.php">À la une</a>
            <?php foreach($categories_list as $cat): ?>
                <a href="category.php?id=<?= $cat['slug'] ?>"><?= $cat['name'] ?></a>
            <?php endforeach; ?>
        </nav>
    </div>
</header>

<main class="wrap">
    <section class="search-header">
        <h1>Résultats de recherche</h1>
        <form action="search.php" method="GET" class="search-bar-page">
            <input type="text" name="q" value="<?= htmlspecialchars($query) ?>" placeholder="Rechercher un article..." required>
            <button type="submit">Rechercher</button>
        </form>
        <?php if(!empty($query)): ?>
            <p class="search-stats">Il y a <?= count($results) ?> résultat(s) pour "<b><?= htmlspecialchars($query) ?></b>"</p>
        <?php endif; ?>
    </section>

    <?php if(empty($results) && !empty($query)): ?>
        <div class="no-results">
            <h2 style="color:var(--gray);">Aucun article ne correspond à votre recherche.</h2>
            <p>Essayez avec d'autres mots-clés comme "Mali", "Sahel" ou une catégorie.</p>
        </div>
    <?php elseif(!empty($results)): ?>
        <div class="article-row" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 30px;">
            <?php foreach($results as $art): ?>
            <article class="card">
                <a href="article.php?id=<?= $art['id'] ?>">
                    <div class="card-img" style="aspect-ratio: 16/9; overflow: hidden; border-radius: 8px; margin-bottom: 15px;">
                        <img src="<?= $art['image'] ?: 'https://picsum.photos/seed/'.$art['id'].'/400/250' ?>" alt="<?= htmlspecialchars($art['title']) ?>" style="width:100%; height:100%; object-fit:cover;">
                    </div>
                    <div class="card-content">
                        <span class="cat-pill eyebrow" style="font-size:10px;"><?= $art['category_name'] ?></span>
                        <h3 style="margin-top:10px; font-size:18px; line-height:1.3;"><?= htmlspecialchars($art['title']) ?></h3>
                        <p style="font-size:14px; color:var(--gray); margin-top:10px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                            <?= htmlspecialchars($art['excerpt']) ?>
                        </p>
                        <div class="byline" style="margin-top:15px; font-size:12px; color:var(--gray);">
                            <?= date('d M Y', strtotime($art['created_at'])) ?>
                        </div>
                    </div>
                </a>
            </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<footer style="margin-top:80px; padding:60px 0; border-top:1px solid var(--line);">
    <div class="wrap">
        <div class="foot-bottom">
            <span>© <?= date('Y') ?> <?= $site_name ?> — Bamako, Mali.</span>
        </div>
    </div>
</footer>

</body>
</html>
