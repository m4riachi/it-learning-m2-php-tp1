<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Simuler un utilisateur connecté (à remplacer par votre système d'authentification)
$userId = 1;

// Connexion à la base de données
$database = new Database();
$db = $database->getConnection();

if (isset($_GET['message'])) {
    $message = $_GET['message'];
}

// Traitement de la réservation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_id'])) {
    $message = createReservation($userId, $_POST['book_id'], $db);
    header("Location: index.php?message=" . urlencode($message));
}

// Récupération des recommandations
$recommendedBooks = getRecommendedBooks($userId, $db);
$popularBooks = getPopularBooks($userId, $db);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bibliothèque - Recommandations</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-8">Recommandations de Livres</h1>

    <?php if (isset($message)): ?>
        <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-4">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <!-- Recommandations basées sur les genres -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-semibold mb-4">Basé sur vos genres préférés</h2>
            <div class="grid gap-4">
                <?php foreach ($recommendedBooks as $book): ?>
                    <div class="border p-4 rounded">
                        <h3 class="font-semibold"><?php echo htmlspecialchars($book['title']); ?></h3>
                        <p class="text-sm text-gray-600">Genre: <?php echo htmlspecialchars($book['genre']); ?></p>
                        <form method="POST" class="mt-2">
                            <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
                            <button type="submit"
                                    class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                                Réserver
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Livres populaires -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-semibold mb-4">Livres Populaires</h2>
            <div class="grid gap-4">
                <?php foreach ($popularBooks as $book): ?>
                    <div class="border p-4 rounded">
                        <h3 class="font-semibold"><?php echo htmlspecialchars($book['title']); ?></h3>
                        <p class="text-sm text-gray-600">Genre: <?php echo htmlspecialchars($book['genre']); ?></p>
                        <p class="text-sm text-gray-500">Score: <?php echo $book['popularity_score']; ?></p>
                        <form method="POST" class="mt-2">
                            <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
                            <button type="submit"
                                    class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                                Réserver
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
</body>
</html>
