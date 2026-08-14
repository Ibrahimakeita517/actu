<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Sidebar -->
<aside class="w-64 bg-slate-900 text-white flex-shrink-0 flex flex-col">
    <div class="p-6 border-b border-slate-800">
        <h1 class="text-2xl font-bold tracking-tighter text-blue-400"><?= $site_name ?> Admin</h1>
    </div>
    <nav class="flex-1 p-4 space-y-2 mt-4">

        <a href="index.php" class="flex items-center space-x-3 p-3 rounded-lg transition <?= ($current_page == 'index.php') ? 'bg-blue-600 text-white font-medium' : 'text-gray-400 hover:bg-slate-800 hover:text-white' ?>">
            <i class="fas fa-home w-5"></i> <span>Tableau de bord</span>
        </a>

        <a href="add-article.php" class="flex items-center space-x-3 p-3 rounded-lg transition <?= ($current_page == 'add-article.php') ? 'bg-blue-600 text-white font-medium' : 'text-gray-400 hover:bg-slate-800 hover:text-white' ?>">
            <i class="fas fa-plus-circle w-5"></i> <span>Nouvel Article</span>
        </a>

        <a href="articles.php" class="flex items-center space-x-3 p-3 rounded-lg transition <?= ($current_page == 'articles.php') ? 'bg-blue-600 text-white font-medium' : 'text-gray-400 hover:bg-slate-800 hover:text-white' ?>">
            <i class="fas fa-newspaper w-5"></i> <span>Articles Publiés</span>
        </a>

        <a href="drafts.php" class="flex items-center space-x-3 p-3 rounded-lg transition <?= ($current_page == 'drafts.php') ? 'bg-blue-600 text-white font-medium' : 'text-gray-400 hover:bg-slate-800 hover:text-white' ?>">
            <i class="fas fa-file-alt w-5"></i> <span>Brouillons</span>
        </a>

        <a href="activity.php" class="flex items-center space-x-3 p-3 rounded-lg transition <?= ($current_page == 'activity.php') ? 'bg-blue-600 text-white font-medium' : 'text-gray-400 hover:bg-slate-800 hover:text-white' ?>">
            <i class="fas fa-list-ul w-5"></i> <span>Journal d'activité</span>
        </a>

        <a href="settings.php" class="flex items-center space-x-3 p-3 rounded-lg transition <?= ($current_page == 'settings.php') ? 'bg-blue-600 text-white font-medium' : 'text-gray-400 hover:bg-slate-800 hover:text-white' ?>">
            <i class="fas fa-cog w-5"></i> <span>Paramètres</span>
        </a>

        <div class="pt-8 text-xs font-semibold text-gray-500 uppercase px-3">Navigation</div>
        <a href="../index.php" target="_blank" class="flex items-center space-x-3 p-3 text-gray-400 hover:bg-slate-800 hover:text-white rounded-lg transition">
            <i class="fas fa-external-link-alt w-5"></i> <span>Voir le site</span>
        </a>
    </nav>
    <div class="p-4 mt-auto">
        <a href="logout.php" class="flex items-center space-x-3 p-3 bg-red-500/10 text-red-400 rounded-lg hover:bg-red-500 hover:text-white transition">
            <i class="fas fa-sign-out-alt w-5"></i> <span>Déconnexion</span>
        </a>
    </div>
</aside>
