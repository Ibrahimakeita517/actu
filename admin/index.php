<?php
require_once '../config.php';

// Protection
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$stats = getAdminStats($pdo);

// Récupérer les articles
$articles = $pdo->query("SELECT a.*, c.name as category_name FROM articles a
                        LEFT JOIN categories c ON a.category_id = c.id
                        ORDER BY created_at DESC LIMIT 10")->fetchAll();

// Récupérer les 5 dernières activités
$activities = $pdo->query("SELECT * FROM activity_log ORDER BY created_at DESC LIMIT 5")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — <?= $site_name ?> Admin</title>
    <!-- Tailwind CSS & FontAwesome -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 flex h-screen overflow-hidden">

    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col overflow-y-auto">
        <!-- Header -->
        <header class="h-16 bg-white border-b flex items-center justify-between px-8 sticky top-0 z-10">
            <h2 class="font-semibold text-gray-800">Tableau de bord</h2>
            <div class="flex items-center space-x-4">
                <div class="text-right mr-4">
                    <p class="text-sm font-semibold"><?= $_SESSION['admin_name'] ?? 'Admin' ?></p>
                    <p class="text-xs text-green-500">En ligne</p>
                </div>
                <img src="https://ui-avatars.com/api/?name=Keita&background=0D8ABC&color=fff" class="w-10 h-10 rounded-full border border-gray-200">
            </div>
        </header>

        <div class="p-8">
            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-newspaper text-xl"></i>
                        </div>
                        <span class="text-xs font-medium text-gray-400">Total Articles</span>
                    </div>
                    <p class="text-3xl font-bold text-gray-900"><?= $stats['total_articles'] ?></p>
                </div>

                <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-green-50 text-green-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-calendar-check text-xl"></i>
                        </div>
                        <span class="text-xs font-medium text-gray-400">Publiés Aujourd'hui</span>
                    </div>
                    <p class="text-3xl font-bold text-gray-900"><?= $stats['published_today'] ?></p>
                </div>

                <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-purple-50 text-purple-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-users text-xl"></i>
                        </div>
                        <span class="text-xs font-medium text-gray-400">Utilisateurs</span>
                    </div>
                    <p class="text-3xl font-bold text-gray-900"><?= $stats['total_users'] ?></p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Recent Articles -->
                <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="p-6 border-b flex justify-between items-center">
                        <h3 class="font-bold text-gray-800 text-lg">Articles récents</h3>
                        <a href="add-article.php" class="text-sm font-semibold text-blue-600 hover:underline">Ajouter +</a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-gray-50 border-b">
                                <tr>
                                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Titre</th>
                                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Catégorie</th>
                                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php foreach($articles as $art): ?>
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <?php if($art['is_featured']): ?>
                                                <span class="w-2 h-2 bg-blue-500 rounded-full mr-3" title="À la une"></span>
                                            <?php endif; ?>
                                            <span class="font-semibold text-gray-700 truncate max-w-xs"><?= htmlspecialchars($art['title']) ?></span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-3 py-1 bg-gray-100 text-gray-600 text-xs font-bold rounded-full uppercase">
                                            <?= htmlspecialchars($art['category_name']) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        <?= date('d/m/Y', strtotime($art['created_at'])) ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <div class="flex items-center space-x-3">
                                            <a href="edit-article.php?id=<?= $art['id'] ?>" class="text-blue-500 hover:text-blue-700">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="delete-article.php?id=<?= $art['id'] ?>" onclick="return confirm('Supprimer ?')" class="text-red-400 hover:text-red-600">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm">
                    <div class="p-6 border-b">
                        <h3 class="font-bold text-gray-800 text-lg">Activités</h3>
                    </div>
                    <div class="p-6 space-y-6">
                        <?php if(empty($activities)): ?>
                            <p class="text-gray-400 text-sm italic">Aucune activité récente.</p>
                        <?php else: ?>
                            <?php foreach($activities as $act): ?>
                            <div class="flex space-x-4">
                                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 text-xs">
                                    <i class="fas fa-bolt"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-800"><?= htmlspecialchars($act['action']) ?></p>
                                    <p class="text-xs text-gray-500"><?= htmlspecialchars($act['user_name']) ?> • <?= date('H:i', strtotime($act['created_at'])) ?></p>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <a href="activity.php" class="block text-center text-sm font-semibold text-blue-600 hover:underline pt-4 border-t">Voir tout le journal</a>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
