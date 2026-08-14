<?php
require_once 'config.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);

    // Nettoyage expert du numéro (Mali)
    $phone = preg_replace('/[^0-9]/', '', $_POST['phone']);
    if (strpos($phone, '223') === 0 && strlen($phone) > 8) {
        $phone = substr($phone, 3); // On normalise à 8 chiffres pour la DB
    }

    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validation Expert
    if (strlen($password) < 6) {
        $error = "Le mot de passe doit faire au moins 6 caractères.";
    } elseif ($password !== $confirm_password) {
        $error = "Les mots de passe ne correspondent pas.";
    } else {
        try {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (full_name, phone, password) VALUES (?, ?, ?)");
            $stmt->execute([$full_name, $phone, $hashed_password]);

            $redirect = isset($_GET['redirect']) ? '&redirect=' . urlencode($_GET['redirect']) : '';
            header('Location: login.php?registered=1' . $redirect);
            exit;
        } catch (Exception $e) {
            $error = "Ce numéro de téléphone est déjà utilisé.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Créer un compte — <?= $site_name ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
        .auth-container { max-width: 400px; margin: 60px auto; padding: 40px; background: var(--paper); border: 1px solid var(--line); border-radius: 12px; }
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
        <h2>Rejoignez la communauté</h2>
        <?php if($error): ?><div class="error-msg"><?= $error ?></div><?php endif; ?>

        <form action="" method="POST">
            <div class="form-group">
                <label>Nom & Prénom (affiché sous les commentaires)</label>
                <input type="text" name="full_name" placeholder="Ex: Moussa Traoré" required>
            </div>
            <div class="form-group">
                <label>Numéro de téléphone</label>
                <input type="tel" name="phone" placeholder="Ex: 70 00 00 00" required>
                <small style="display:block; margin-top:5px; color:var(--gray); font-size:11px;">Format: 8 chiffres (+223 accepté)</small>
            </div>
            <div class="form-group">
                <label>Mot de passe</label>
                <input type="password" name="password" required>
            </div>
            <div class="form-group">
                <label>Confirmer le mot de passe</label>
                <input type="password" name="confirm_password" required>
            </div>
            <button type="submit" class="btn-auth">Créer mon compte</button>
        </form>

        <div style="margin: 25px 0; display: flex; align-items: center; gap: 10px; color: var(--line);">
            <hr style="flex:1; border:0; border-top:1px solid var(--line);">
            <span style="font-size:12px; color:var(--gray);">OU</span>
            <hr style="flex:1; border:0; border-top:1px solid var(--line);">
        </div>

        <div style="display: flex; flex-direction: column; gap: 10px;">
            <button class="btn-auth" style="background:#fff; color:#444; border:1px solid var(--line); display:flex; align-items:center; justify-content:center; gap:10px;">
                <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" width="18"> S'inscrire avec Google
            </button>
        </div>
        <p style="margin-top:25px; text-align:center; font-size:14px; color:var(--gray);">
            Déjà inscrit ? <a href="login.php<?= isset($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) : '' ?>" style="color:var(--blue); font-weight:600;">Se connecter</a>
        </p>
    </div>
</body>
</html>
