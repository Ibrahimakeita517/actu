<?php
session_start();
session_destroy();

// Supprimer le cookie "Se souvenir de moi"
if (isset($_COOKIE['user_phone'])) {
    setcookie('user_phone', '', time() - 3600, "/");
}

header('Location: index.php');
exit;
?>
