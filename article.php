<?php
require_once 'config.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$article = getArticleById($pdo, $id);

if (!$article) {
    header("Location: index.php");
    exit;
}

// Traitement de l'ajout d'un commentaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_comment'])) {
    if (isUserLoggedIn()) {
        $content = trim($_POST['comment_content']);
        $user_id = $_SESSION['user_id'];

        if (!empty($content)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO comments (article_id, user_id, content) VALUES (?, ?, ?)");
                $stmt->execute([$id, $user_id, $content]);
                header("Location: article.php?id=$id#comments");
                exit;
            } catch (PDOException $e) {
                // Si l'utilisateur n'existe pas en DB (ex: admin non présent dans table users)
                $error_comment = "Erreur : Votre compte ne permet pas de commenter ou a été supprimé.";
            }
        }
    } else {
        // Redirection vers login avec retour automatique
        header("Location: login.php?redirect=" . urlencode("article.php?id=$id"));
        exit;
    }
}

// Articles suggérés
$stmt = $pdo->prepare("SELECT a.*, c.name as category_name FROM articles a
                       JOIN categories c ON a.category_id = c.id
                       WHERE a.category_id = ? AND a.id != ?
                       ORDER BY created_at DESC LIMIT 3");
$stmt->execute([$article['category_id'], $id]);
$related = $stmt->fetchAll();

// Récupérer les commentaires
$comments = getCommentsByArticle($pdo, $id);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- SEO Dynamique -->
    <title><?= htmlspecialchars($article['title']) ?> — <?= $site_name ?></title>
    <meta name="description" content="<?= htmlspecialchars($article['excerpt']) ?>">
    <meta property="og:title" content="<?= htmlspecialchars($article['title']) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($article['excerpt']) ?>">
    <meta property="og:image" content="<?= $base_url . '/' . $article['image'] ?>">
    <meta property="og:type" content="article">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        .comment-section { margin-top: 60px; padding-top: 40px; border-top: 2px solid var(--line); }
        .comment-item { padding: 20px; background: var(--surface); border-radius: 8px; margin-bottom: 20px; }
        .comment-meta { display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 13px; color: var(--gray); }
        .comment-author { font-weight: 700; color: var(--ink); }
        .comment-form textarea { width: 100%; padding: 15px; border: 1px solid var(--line); border-radius: 8px; margin-bottom: 15px; font-family: inherit; resize: vertical; }
        .login-box-comment { padding: 30px; background: var(--surface); text-align: center; border-radius: 8px; }
    </style>
</head>
<body class="article-page">

<div class="status-bar">
    <div class="wrap">
        <div class="status-left">
            <a href="index.php" class="back-link">← Retour à l'accueil</a>
        </div>
        <div class="status-right">
            <?php if(isUserLoggedIn()): ?>
                <span style="font-size:11px; margin-right:15px;">Bienvenue, <b><?= htmlspecialchars($_SESSION['full_name']) ?></b></span>
                <a href="logout_user.php" style="font-size:11px; color:var(--red); text-decoration:none;">Déconnexion</a>
            <?php else: ?>
                <a href="login.php" style="font-size:11px; margin-right:10px;">Connexion</a>
                <a href="register.php" style="font-size:11px;">Inscription</a>
            <?php endif; ?>
            <div class="theme-switch" onclick="toggleTheme()" style="margin-left:15px;">
                <svg class="sun" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
            </div>
        </div>
    </div>
</div>

<header class="navbar">
    <div class="wrap">
        <a href="index.php" class="logo"><?= $site_name ?><span>.</span></a>
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
    <article class="single-article wrap">
        <header class="article-header">
            <span class="cat-pill eyebrow"><?= $article['category_name'] ?></span>
            <h1><?= htmlspecialchars($article['title']) ?></h1>
            <p class="article-lead"><?= htmlspecialchars($article['excerpt']) ?></p>

            <div class="article-meta">
                <div class="author-info">
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($article['author']) ?>&background=random" alt="<?= htmlspecialchars($article['author']) ?>" class="avatar">
                    <div>
                        <div class="author-name"><?= htmlspecialchars($article['author']) ?></div>
                        <div class="pub-date">Publié le <?= date('d/m/Y à H:i', strtotime($article['created_at'])) ?></div>
                    </div>
                </div>
            </div>
        </header>

        <div class="featured-image">
            <img src="<?= $article['image'] ?: 'https://picsum.photos/seed/'.$article['id'].'/1000/600' ?>" alt="<?= htmlspecialchars($article['title']) ?>">
        </div>

        <div class="article-body">
            <?= nl2br(htmlspecialchars($article['content'])) ?>
        </div>

        <!-- Section Commentaires -->
        <section class="comment-section" id="comments">
            <h2 class="eyebrow">Commentaires (<?= count($comments) ?>)</h2>

            <?php if(isset($error_comment)): ?>
                <div style="background:#fee2e2; color:#991b1b; padding:15px; border-radius:8px; margin-bottom:20px; font-size:14px;">
                    <?= $error_comment ?>
                </div>
            <?php endif; ?>

            <div class="comment-form">
                <form action="" method="POST">
                    <textarea name="comment_content" placeholder="Partagez votre avis sur cet article..." required></textarea>

                    <?php if(isUserLoggedIn()): ?>
                        <button type="submit" name="submit_comment" class="btn-primary">Publier le commentaire</button>
                    <?php else: ?>
                        <div style="display:flex; align-items:center; gap:15px;">
                            <button type="submit" name="submit_comment" class="btn-primary">Se connecter pour publier</button>
                            <p style="font-size:12px; color:var(--gray);">Connectez-vous pour rejoindre la discussion.</p>
                        </div>
                    <?php endif; ?>
                </form>
            </div>

            <div class="comment-list" style="margin-top:40px;">
                <?php if(empty($comments)): ?>
                    <p style="color:var(--gray); font-style:italic;">Soyez le premier à réagir !</p>
                <?php else: ?>
                    <?php foreach($comments as $com): ?>
                        <div class="comment-item">
                            <div class="comment-meta">
                                <span class="comment-author"><?= htmlspecialchars($com['full_name']) ?></span>
                                <span>Le <?= date('d/m/Y à H:i', strtotime($com['created_at'])) ?></span>
                            </div>
                            <div class="comment-content">
                                <?= nl2br(htmlspecialchars($com['content'])) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <footer class="article-footer">
            <div class="tags">
                <span class="tag">#Mali</span>
                <span class="tag">#<?= $article['category_name'] ?></span>
            </div>
        </footer>
    </article>

    <?php if(!empty($related)): ?>
    <section class="related-articles wrap">
        <h2 class="eyebrow">À lire aussi</h2>
        <div class="article-row">
            <?php foreach($related as $a): ?>
                <article class="card">
                    <a href="article.php?id=<?= $a['id'] ?>">
                        <div class="card-img">
                            <img src="<?= $a['image'] ?: 'https://picsum.photos/seed/'.$a['id'].'/400/250' ?>" alt="<?= htmlspecialchars($a['title']) ?>">
                        </div>
                        <div class="card-content">
                            <h3><?= htmlspecialchars($a['title']) ?></h3>
                        </div>
                    </a>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
</main>

<footer>
    <div class="wrap">
        <div class="foot-bottom">
            <span>© <?= date('Y') ?> <?= $site_name ?></span>
            <a href="index.php">Retour à l'accueil</a>
        </div>
    </div>
</footer>

<script src="script.js"></script>
</body>
</html>
