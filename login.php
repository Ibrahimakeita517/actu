<?php
require_once 'config.php';

// Si déjà connecté, redirection vers l'accueil
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Nettoyage expert du numéro
    $phone = preg_replace('/[^0-9]/', '', $_POST['phone']);
    if (strpos($phone, '223') === 0 && strlen($phone) > 8) {
        $phone = substr($phone, 3);
    }

    $password = $_POST['password'];
    $remember = isset($_POST['remember']);

    // VÉRIFICATION SPÉCIALE : Identifiants Admin Keita
    if ($phone === '83221696' && $password === 'admin') {
        $_SESSION['user_id'] = 999; // ID spécial admin
        $_SESSION['full_name'] = 'Keita (Admin)';
        $_SESSION['admin_logged_in'] = true;

        header('Location: admin/index.php');
        exit;
    }

    // Connexion Utilisateur Normal
    $stmt = $pdo->prepare("SELECT * FROM users WHERE phone = ?");
    $stmt->execute([$phone]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['full_name'] = $user['full_name'];

        if ($remember) {
            setcookie('user_phone', $phone, time() + (86400 * 30), "/");
        }

        // Redirection intelligente
        $target = isset($_GET['redirect']) ? $_GET['redirect'] : 'index.php';
        header("Location: $target");
        exit;
    } else {
        $error = "Numéro de téléphone ou mot de passe incorrect.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion — <?= $site_name ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
        .auth-container { max-width: 400px; margin: 80px auto; padding: 40px; background: var(--paper); border: 1px solid var(--line); border-radius: 12px; }
        .auth-container h2 { margin-bottom: 24px; font-size: 24px; letter-spacing: -0.02em; }
        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; margin-bottom: 8px; font-size: 13px; font-weight: 600; color: var(--gray); }
        .form-group input { width: 100%; padding: 12px; border: 1px solid var(--line); border-radius: 8px; font-family: inherit; box-sizing: border-box; }
        .btn-auth { width: 100%; background: var(--blue); color: #fff; border: none; padding: 14px; border-radius: 8px; cursor: pointer; font-weight: 700; margin-top: 10px; }
        .error-msg { background: #fee2e2; color: #991b1b; padding: 12px; border-radius: 6px; margin-bottom: 20px; font-size: 14px; text-align: center; }
    </style>
</head>
<body>
    <div class="auth-container">
        <a href="index.php" style="display:inline-block; margin-bottom:20px; font-size:12px; color:var(--blue);">← Retour au site</a>
        <h2>Connexion</h2>

        <?php if($error): ?><div class="error-msg"><?= $error ?></div><?php endif; ?>

        <form action="" method="POST">
            <div class="form-group">
                <label>Numéro de téléphone</label>
                <input type="tel" name="phone" placeholder="83 22 16 96" required autofocus>
            </div>
            <div class="form-group">
                <label>Mot de passe</label>
                <input type="password" name="password" required>
            </div>
            <div class="form-group" style="display: flex; align-items: center; gap: 10px; margin-bottom: 25px;">
                <input type="checkbox" name="remember" id="remember" style="width: auto;">
                <label for="remember" style="margin-bottom: 0; cursor: pointer;">Rester connecté</label>
            </div>
            <button type="submit" class="btn-auth">Se connecter</button>
        </form>
        <p style="margin-top:25px; text-align:center; font-size:14px; color:var(--gray);">
            Pas encore de compte ? <a href="register.php<?= isset($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) : '' ?>" style="color:var(--blue); font-weight:600;">Inscription</a>
        </p>
    </div>
</body>
</html>
