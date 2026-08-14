<?php
require_once '../config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $category_id = $_POST['category_id'];
    $excerpt = $_POST['excerpt'];
    $content = $_POST['content'];
    $author = $_POST['author'];
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $is_trending = isset($_POST['is_trending']) ? 1 : 0;

    // Nouveaux champs
    $status = $_POST['status']; // 'published' ou 'draft'
    $published_at = !empty($_POST['published_at']) ? $_POST['published_at'] : date('Y-m-d H:i:s');

    $image_path = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $filename = $_FILES['image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {
            $new_name = uniqid('news_', true) . '.' . $ext;
            $destination = '../uploads/' . $new_name;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
                $image_path = 'uploads/' . $new_name;
            }
        }
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO articles (category_id, title, excerpt, content, author, image, is_featured, is_trending, status, published_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$category_id, $title, $excerpt, $content, $author, $image_path, $is_featured, $is_trending, $status, $published_at]);

        $action_label = ($status === 'draft') ? 'Enregistré en brouillon' : 'Publié';
        logActivity($pdo, $_SESSION['admin_name'] ?? 'Admin', "Article $action_label", "Article : $title");

        $msg_text = ($status === 'draft') ? "Article enregistré en brouillon." : "Article publié avec succès !";
        $message = '<div class="p-4 mb-6 bg-green-50 text-green-700 rounded-lg border border-green-200 flex items-center"><i class="fas fa-check-circle mr-2"></i> ' . $msg_text . '</div>';
    } catch (Exception $e) {
        $message = '<div class="p-4 mb-6 bg-red-50 text-red-700 rounded-lg border border-red-200">Erreur : ' . $e->getMessage() . '</div>';
    }
}

$categories_list = getCategories($pdo);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Article — <?= $site_name ?> Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-gray-50 flex h-screen overflow-hidden">

    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>

    <main class="flex-1 flex flex-col overflow-y-auto">
        <header class="h-16 bg-white border-b flex items-center justify-between px-8 sticky top-0 z-10">
            <h2 class="font-semibold text-gray-800">Créer un nouvel article</h2>
            <a href="index.php" class="text-sm text-gray-500 hover:text-gray-800"><i class="fas fa-arrow-left mr-1"></i> Retour</a>
        </header>

        <div class="p-8 max-w-5xl">
            <?= $message ?>

            <form action="" method="POST" enctype="multipart/form-data" class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="p-8 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="md:col-span-2 space-y-2">
                            <label class="text-sm font-semibold text-gray-700">Titre de l'article</label>
                            <input type="text" name="title" required placeholder="Entrez le titre ici..." class="w-full p-4 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition text-lg font-medium">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-gray-700">Catégorie</label>
                            <select name="category_id" class="w-full p-4 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition appearance-none bg-white">
                                <?php foreach($categories_list as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"><?= $cat['name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-gray-700">Auteur</label>
                            <input type="text" name="author" required placeholder="Nom de l'auteur" class="w-full p-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-gray-700">Statut</label>
                            <select name="status" class="w-full p-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition bg-white">
                                <option value="published">Publié</option>
                                <option value="draft">Brouillon</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-gray-700">Date/Heure de publication</label>
                            <input type="datetime-local" name="published_at" value="<?= date('Y-m-d\TH:i') ?>" class="w-full p-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition">
                            <p class="text-[10px] text-blue-500">Laissez tel quel pour publier immédiatement, ou choisissez une date future pour programmer.</p>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-gray-700">Image de couverture</label>
                        <input type="file" name="image" accept="image/*" class="w-full p-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-gray-700">Résumé (Extrait)</label>
                        <textarea name="excerpt" rows="2" placeholder="Un bref aperçu de l'article..." class="w-full p-4 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition"></textarea>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-gray-700">Contenu complet</label>
                        <textarea name="content" rows="10" placeholder="Écrivez votre article ici..." class="w-full p-4 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition"></textarea>
                    </div>

                    <div class="flex flex-wrap gap-6 p-4 bg-gray-50 rounded-xl">
                        <label class="flex items-center space-x-3 cursor-pointer">
                            <input type="checkbox" name="is_featured" class="w-5 h-5 text-blue-600 rounded">
                            <span class="text-sm font-medium text-gray-700">Mettre à la une</span>
                        </label>
                        <label class="flex items-center space-x-3 cursor-pointer">
                            <input type="checkbox" name="is_trending" class="w-5 h-5 text-blue-600 rounded">
                            <span class="text-sm font-medium text-gray-700">Marquer comme Tendance</span>
                        </label>
                    </div>
                </div>

                <div class="p-8 bg-gray-50 border-t flex justify-end gap-4">
                    <button type="submit" name="status" value="draft" class="px-8 py-4 border border-gray-300 rounded-xl font-bold text-gray-600 hover:bg-white transition">
                        Enregistrer en Brouillon
                    </button>
                    <button type="submit" name="status" value="published" class="bg-blue-600 text-white px-10 py-4 rounded-xl font-bold hover:bg-blue-700 transition shadow-lg shadow-blue-200 flex items-center">
                        <i class="fas fa-paper-plane mr-2"></i> Enregistrer / Publier
                    </button>
                </div>
            </form>
        </div>
    </main>
</body>
</html>
