<?php
require_once '../config.php';

if (isset($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Nettoyage du numéro pour la comparaison (enlève les espaces)
    $clean_phone = preg_replace('/[^0-9]/', '', $username);

    // Identifiants spécifiques demandés
    if (($clean_phone === '83221696' || strtolower($username) === 'keita') && $password === 'admin') {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_name'] = 'Keita';
        header('Location: index.php');
        exit;
    } else {
        $error = 'Identifiants admin incorrects.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion Admin — <?= $site_name ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #0b0c10; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
        .login-box { background: #fff; padding: 40px; border-radius: 12px; width: 100%; max-width: 400px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); }
        h1 { font-size: 24px; margin-bottom: 8px; text-align: center; }
        p.subtitle { text-align: center; color: #666; font-size: 14px; margin-bottom: 24px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px; }
        input { width: 100%; padding: 12px; border: 1px solid #e4e6eb; border-radius: 6px; box-sizing: border-box; }
        .btn-login { width: 100%; background: #2151f5; color: #fff; border: none; padding: 14px; border-radius: 6px; font-weight: 600; cursor: pointer; }
        .error { color: #d6392f; text-align: center; margin-bottom: 15px; font-size: 14px; border: 1px solid #f5c2c7; background: #f8d7da; padding: 10px; border-radius: 6px; }
    </style>
</head>
<body>
    <div class="login-box">
        <h1>Espace Admin</h1>
        <p class="subtitle">Connectez-vous avec vos accès Keita</p>
        <?php if($error): ?><div class="error"><?= $error ?></div><?php endif; ?>
        <form action="" method="POST">
            <div class="form-group">
                <label>Numéro ou Nom (Keita)</label>
                <input type="text" name="username" placeholder="83 22 16 96" required autofocus>
            </div>
            <div class="form-group">
                <label>Mot de passe</label>
                <input type="password" name="password" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn-login">Accéder au Panel</button>
        </form>
    </div>
</body>
</html>
