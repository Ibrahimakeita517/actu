<?php
require_once '../config.php';

// Protection
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Récupérer les brouillons (status = 'draft') OU les articles programmés (status = 'published' mais date future)
$drafts = $pdo->query("SELECT a.*, c.name as category_name FROM articles a
                      LEFT JOIN categories c ON a.category_id = c.id
                      WHERE a.status = 'draft' OR (a.status = 'published' AND a.published_at > NOW())
                      ORDER BY published_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Brouillons — <?= $site_name ?> Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-gray-50 flex h-screen overflow-hidden">

    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col overflow-y-auto">
        <header class="h-16 bg-white border-b flex items-center justify-between px-8 sticky top-0 z-10">
            <h2 class="font-semibold text-gray-800">Gestion des Brouillons & Programmations</h2>
        </header>

        <div class="p-8">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="p-6 border-b">
                    <h3 class="font-bold text-gray-800 text-lg">Brouillons et articles en attente</h3>
                    <p class="text-sm text-gray-500">Ces articles ne sont pas visibles sur le site public.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Titre</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Statut</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Date Prévue</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php if(empty($drafts)): ?>
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-gray-500 italic">Aucun brouillon pour le moment.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($drafts as $art): ?>
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4">
                                        <span class="font-semibold text-gray-700"><?= htmlspecialchars($art['title']) ?></span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php if($art['status'] === 'draft'): ?>
                                            <span class="px-3 py-1 bg-yellow-100 text-yellow-700 text-xs font-bold rounded-full">BROUILLON</span>
                                        <?php else: ?>
                                            <span class="px-3 py-1 bg-blue-100 text-blue-700 text-xs font-bold rounded-full">PROGRAMMÉ</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        <?= date('d/m/Y H:i', strtotime($art['published_at'])) ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <div class="flex items-center space-x-3">
                                            <a href="edit-article.php?id=<?= $art['id'] ?>" class="text-blue-500 hover:text-blue-700 font-medium">Modifier / Publier</a>
                                            <a href="delete-article.php?id=<?= $art['id'] ?>" onclick="return confirm('Supprimer ce brouillon ?')" class="text-red-400 hover:text-red-600">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
