<?php
require_once 'config.php';

// Récupérer les articles pour la une
$all_articles = getLatestArticles($pdo, 20);

// Article à la une (le plus récent marqué comme featured, ou juste le plus récent)
$featured_articles = array_filter($all_articles, function($a) { return $a['is_featured']; });
$hero = !empty($featured_articles) ? reset($featured_articles) : reset($all_articles);

// Articles les plus lus (trending)
$trending = array_filter($all_articles, function($a) { return $a['is_trending']; });
if (empty($trending)) $trending = array_slice($all_articles, 1, 5);

$categories_list = getCategories($pdo);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- SEO Dynamique -->
    <title><?= $site_name ?> — <?= $site_tagline ?></title>
    <meta name="description" content="Toute l'actualité du Mali, du Sahel et de l'international en temps réel sur <?= $site_name ?>.">
    <meta property="og:title" content="<?= $site_name ?> — <?= $site_tagline ?>">
    <meta property="og:description" content="Informations vérifiées, analyses et reportages exclusifs.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= $base_url ?>">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Styles -->
    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- Breaking News Ticker -->
<div class="ticker-wrap">
    <div class="ticker-title">FLASH</div>
    <div class="ticker">
        <?php foreach(array_slice($all_articles, 0, 5) as $art): ?>
            <div class="ticker__item"><?= htmlspecialchars($art['title']) ?></div>
        <?php endforeach; ?>
    </div>
</div>

<div class="status-bar">
    <div class="wrap">
        <div class="status-left">
            <span class="dot"></span>
            <span id="clock">00:00:00</span>
            <span class="sep">|</span>
            <span class="location">Bamako, ML</span>
        </div>
        <div class="status-mid">
            <span><b id="articleCount"><?= count($all_articles) + 120 ?></b> articles publiés aujourd'hui</span>
            <span>Dernière mise à jour <b id="lastUpdate">à l'instant</b></span>
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
            <div class="theme-switch" onclick="toggleTheme()" title="Changer de thème" style="margin-left:15px; display:inline-block; vertical-align:middle;">
                <svg class="sun" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
            </div>
        </div>
    </div>
</div>

<header class="navbar">
    <div class="wrap">
        <a href="index.php" class="logo"><?= $site_name ?><span>.</span></a>
        <nav class="nav-links">
            <a href="index.php" class="active">À la une</a>
            <?php foreach($categories_list as $cat): ?>
                <a href="category.php?id=<?= $cat['slug'] ?>"><?= $cat['name'] ?></a>
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

<main>
    <div class="wrap">
        <?php if($hero): ?>
        <section class="hero-grid">
            <article class="lead">
                <a href="article.php?id=<?= $hero['id'] ?>">
                    <div class="img-frame">
                        <img src="<?= $hero['image'] ?: 'https://picsum.photos/seed/news/1000/562' ?>" alt="<?= htmlspecialchars($hero['title']) ?>">
                        <span class="live-tag">RECENT</span>
                    </div>
                    <span class="cat-pill eyebrow"><?= $hero['category_name'] ?></span>
                    <h1><?= htmlspecialchars($hero['title']) ?></h1>
                    <p class="dek"><?= htmlspecialchars($hero['excerpt']) ?></p>
                    <div class="byline">
                        <span class="author"><?= htmlspecialchars($hero['author']) ?></span>
                        <span class="sep">·</span>
                        <span><?= date('d M, H:i', strtotime($hero['created_at'])) ?></span>
                    </div>
                </a>
            </article>

            <aside class="most-read">
                <div class="section-header">
                    <h2 class="eyebrow">Les plus lus</h2>
                </div>
                <div class="mr-list">
                    <?php
                    $i = 1;
                    foreach(array_slice($trending, 0, 5) as $art):
                    ?>
                    <a class="mr-item" href="article.php?id=<?= $art['id'] ?>">
                        <span class="mr-num"><?= sprintf('%02d', $i++) ?></span>
                        <div class="mr-text">
                            <h3><?= htmlspecialchars($art['title']) ?></h3>
                            <div class="byline"><?= $art['category_name'] ?></div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </aside>
        </section>
        <?php endif; ?>

        <!-- Blocs par catégories -->
        <?php foreach(array_slice($categories_list, 0, 3) as $cat):
            $cat_arts = getArticlesByCategory($pdo, $cat['slug'], 3);
            if(!empty($cat_arts)):
        ?>
        <section class="category-block" id="<?= $cat['slug'] ?>">
            <div class="section-header">
                <h2 class="eyebrow"><?= $cat['name'] ?></h2>
                <a href="category.php?id=<?= $cat['slug'] ?>" class="view-all">Tout voir →</a>
            </div>
            <div class="article-row">
                <?php foreach($cat_arts as $art): ?>
                <article class="card">
                    <a href="article.php?id=<?= $art['id'] ?>">
                        <div class="card-img">
                            <img src="<?= $art['image'] ?: 'https://picsum.photos/seed/'.$art['id'].'/400/250' ?>" alt="<?= htmlspecialchars($art['title']) ?>">
                        </div>
                        <div class="card-content">
                            <h3><?= htmlspecialchars($art['title']) ?></h3>
                            <div class="byline"><?= date('d M', strtotime($art['created_at'])) ?></div>
                        </div>
                    </a>
                </article>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; endforeach; ?>
    </div>
</main>

<section class="newsletter">
    <div class="wrap">
        <div class="news-box">
            <div class="news-text">
                <h2>L'essentiel, dans votre boîte mail.</h2>
                <p>Recevez notre sélection quotidienne des faits marquants du Mali.</p>
            </div>
            <form class="news-form">
                <input type="email" placeholder="votre@email.com" required>
                <button type="submit" class="btn-dark">S'inscrire</button>
            </form>
        </div>
    </div>
</section>

<footer>
    <div class="wrap">
        <div class="foot-grid">
            <div class="foot-brand">
                <div class="logo"><?= $site_name ?><span>.</span></div>
                <p>L'actualité du Mali et du Sahel, organisée pour être lue vite et comprise juste.</p>
            </div>
            <div class="foot-col">
                <h4>Rubriques</h4>
                <?php foreach($categories_list as $cat): ?>
                    <a href="category.php?id=<?= $cat['slug'] ?>"><?= $cat['name'] ?></a>
                <?php endforeach; ?>
            </div>
            <div class="foot-col">
                <h4>Journal</h4>
                <a href="admin.php">Administration</a>
                <a href="#">La rédaction</a>
                <a href="#">Contact</a>
            </div>
        </div>
        <div class="foot-bottom">
            <span>© <?= date('Y') ?> <?= $site_name ?> — Bamako, Mali.</span>
        </div>
    </div>
</footer>

<script src="script.js"></script>
</body>
</html>
