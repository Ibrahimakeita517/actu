<?php
require_once '../config.php';

// Protection
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Récupérer tous les articles publiés
$articles = $pdo->query("SELECT a.*, c.name as category_name FROM articles a
                        LEFT JOIN categories c ON a.category_id = c.id
                        WHERE a.status = 'published' AND a.published_at <= NOW()
                        ORDER BY published_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tous les Articles — <?= $site_name ?> Admin</title>
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
            <h2 class="font-semibold text-gray-800">Gestion des Articles Publiés</h2>
            <a href="add-article.php" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-bold hover:bg-blue-700 transition">
                <i class="fas fa-plus mr-2"></i> Ajouter un article
            </a>
        </header>

        <div class="p-8">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="p-6 border-b flex justify-between items-center">
                    <div>
                        <h3 class="font-bold text-gray-800 text-lg">Liste des articles</h3>
                        <p class="text-sm text-gray-500">Gérez vos publications en ligne.</p>
                    </div>
                    <span class="bg-blue-50 text-blue-600 px-3 py-1 rounded-full text-xs font-bold">
                        <?= count($articles) ?> Articles
                    </span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Image</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Titre & Catégorie</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Auteur</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php if(empty($articles)): ?>
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-gray-500 italic">Aucun article publié pour le moment.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($articles as $art): ?>
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4">
                                        <img src="../<?= $art['image'] ?: 'uploads/default.jpg' ?>" class="w-12 h-12 object-cover rounded-lg shadow-sm">
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-semibold text-gray-700 leading-tight"><?= htmlspecialchars($art['title']) ?></div>
                                        <div class="text-xs text-blue-500 font-medium mt-1"><?= htmlspecialchars($art['category_name']) ?></div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        <?= htmlspecialchars($art['author']) ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        <?= date('d/m/Y', strtotime($art['published_at'])) ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <div class="flex items-center space-x-4">
                                            <a href="edit-article.php?id=<?= $art['id'] ?>" class="text-blue-500 hover:text-blue-700 transition" title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="../article.php?id=<?= $art['id'] ?>" target="_blank" class="text-gray-400 hover:text-gray-600 transition" title="Voir sur le site">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="delete-article.php?id=<?= $art['id'] ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet article ? Cette action est irréversible.')" class="text-red-400 hover:text-red-600 transition" title="Supprimer">
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
