<?php
require_once '../config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$message = '';

// Récupérer les paramètres actuels
$settings_res = $pdo->query("SELECT * FROM site_settings")->fetchAll();
$settings = [];
foreach ($settings_res as $row) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST['settings'] as $key => $value) {
        $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->execute([$key, $value, $value]);
    }
    $message = '<div class="p-4 mb-6 bg-green-50 text-green-700 rounded-lg border border-green-200">Paramètres mis à jour avec succès !</div>';

    logActivity($pdo, $_SESSION['admin_name'] ?? 'Admin', 'Mise à jour paramètres', 'Modification des réglages généraux');

    // Rafraîchir les données locales après sauvegarde
    $settings_res = $pdo->query("SELECT * FROM site_settings")->fetchAll();
    foreach ($settings_res as $row) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paramètres — Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-gray-50 flex h-screen overflow-hidden">

    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>

    <main class="flex-1 flex flex-col overflow-y-auto">
        <header class="h-16 bg-white border-b flex items-center px-8 sticky top-0 z-10">
            <h2 class="font-semibold text-gray-800">Configuration du système</h2>
        </header>

        <div class="p-8 max-w-4xl">
            <?= $message ?>
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <form action="" method="POST" class="p-8 space-y-8">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-gray-700">Nom du site (Verrouillé)</label>
                            <input type="text" value="<?= htmlspecialchars($settings['site_name'] ?? 'FLUX') ?>" readonly class="w-full p-4 border border-gray-100 bg-gray-50 text-gray-400 rounded-xl cursor-not-allowed outline-none">
                            <input type="hidden" name="settings[site_name]" value="<?= htmlspecialchars($settings['site_name'] ?? 'FLUX') ?>">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-gray-700">Email de contact</label>
                            <input type="email" name="settings[contact_email]" value="<?= htmlspecialchars($settings['contact_email'] ?? 'contact@flux.ml') ?>" class="w-full p-4 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition">
                        </div>
                    </div>

                    <div class="p-6 bg-slate-50 rounded-2xl border border-slate-100 space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="bg-blue-600 p-2 rounded-lg text-white">
                                    <i class="fas fa-tools"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-gray-800">Mode Maintenance</h4>
                                    <p class="text-xs text-gray-500">Bloque l'accès au public</p>
                                </div>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="hidden" name="settings[maintenance_mode]" value="0">
                                <input type="checkbox" name="settings[maintenance_mode]" value="1" id="m_mode" <?= ($settings['maintenance_mode'] ?? '0') == '1' ? 'checked' : '' ?> class="sr-only peer">
                                <div class="w-14 h-7 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>

                        <div id="maintenance_details" class="<?= ($settings['maintenance_mode'] ?? '0') == '1' ? '' : 'hidden' ?> pt-4 border-t border-slate-200">
                            <label class="text-sm font-bold text-gray-700 block mb-2">Message personnalisé à afficher</label>
                            <textarea name="settings[maintenance_message]" rows="4" placeholder="Ex: Notre équipe technique met à jour le serveur..." class="w-full p-4 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition bg-white"><?= htmlspecialchars($settings['maintenance_message'] ?? '') ?></textarea>
                            <p class="text-xs text-blue-500 mt-2 font-medium"><i class="fas fa-info-circle"></i> Ce texte sera affiché en grand au centre de l'écran pour les visiteurs.</p>
                        </div>
                    </div>

                    <script>
                        document.getElementById('m_mode').addEventListener('change', function() {
                            const details = document.getElementById('maintenance_details');
                            if(this.checked) {
                                details.classList.remove('hidden');
                            } else {
                                details.classList.add('hidden');
                            }
                        });
                    </script>

                    <div class="pt-6 border-t flex justify-end">
                        <button type="submit" class="bg-blue-600 text-white px-12 py-4 rounded-xl font-bold hover:bg-blue-700 transition shadow-xl shadow-blue-200">
                            Enregistrer la configuration
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</body>
</html>
