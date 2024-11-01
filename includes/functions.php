<?php
/**
 * Récupère les livres recommandés pour un utilisateur spécifique
 * basé sur ses genres préférés
 *
 * @param int $userId ID de l'utilisateur
 * @param PDO $db Instance de connexion à la base de données
 * @return array Liste des livres recommandés
 */
function getRecommendedBooks($userId, $db) {
    // Construction de la requête SQL pour obtenir les recommandations
    $query = "
        -- Sélection des livres avec le type de recommandation
        SELECT DISTINCT b.*, 'genre' as recommendation_type
        FROM books b
        -- Sous-requête pour obtenir les 3 genres les plus lus par l'utilisateur
        INNER JOIN (
            SELECT genre, COUNT(*) as count
            FROM reservations r
            INNER JOIN books b ON r.id_book = b.id
            WHERE r.id_user = :userId
            GROUP BY genre
            ORDER BY count DESC  -- Trie par nombre de lectures
            LIMIT 3             -- Limite aux 3 genres les plus populaires
        ) as preferred_genres ON b.genre = preferred_genres.genre
        -- Exclusion des livres déjà réservés par l'utilisateur
        WHERE b.id NOT IN (
            SELECT id_book FROM reservations WHERE id_user = :userId
        )
        -- Tri par score de popularité
        ORDER BY b.popularity_score DESC
        LIMIT 10  -- Limite aux 10 meilleures recommandations
    ";

    // Préparation et exécution de la requête avec protection contre les injections SQL
    $stmt = $db->prepare($query);
    $stmt->execute(['userId' => $userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Récupère les livres les plus populaires non réservés par l'utilisateur
 *
 * @param int $userId ID de l'utilisateur
 * @param PDO $db Instance de connexion à la base de données
 * @return array Liste des livres populaires
 */
function getPopularBooks($userId, $db) {
    // Requête pour obtenir les 5 livres les plus populaires
    $query = "
        -- Sélection des livres avec marquage comme 'popular'
        SELECT b.*, 'popular' as recommendation_type
        FROM books b
        -- Exclusion des livres déjà réservés par l'utilisateur
        WHERE b.id NOT IN (
            SELECT id_book FROM reservations WHERE id_user = :userId
        )
        ORDER BY b.popularity_score DESC  -- Tri par popularité
        LIMIT 5                          -- Limite aux 5 plus populaires
    ";

    $stmt = $db->prepare($query);
    $stmt->execute(['userId' => $userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Crée une nouvelle réservation pour un utilisateur
 *
 * @param int $userId ID de l'utilisateur
 * @param int $bookId ID du livre
 * @param PDO $db Instance de connexion à la base de données
 * @return string Message de confirmation ou d'erreur
 */
function createReservation($userId, $bookId, $db) {
    try {
        // Vérification de l'existence d'une réservation
        $checkQuery = "SELECT COUNT(*) FROM reservations WHERE id_user = :userId AND id_book = :bookId";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->execute(['userId' => $userId, 'bookId' => $bookId]);

        // Si la réservation existe déjà, retourner un message d'erreur
        if ($checkStmt->fetchColumn() > 0) {
            return "Ce livre est déjà réservé";
        }

        // Insertion de la nouvelle réservation
        $query = "INSERT INTO reservations (id_user, id_book, date_reservation) VALUES (:userId, :bookId, NOW())";
        $stmt = $db->prepare($query);
        $stmt->execute(['userId' => $userId, 'bookId' => $bookId]);

        // Mise à jour du score de popularité du livre
        updatePopularityScore($bookId, $db);

        return "Réservation effectuée avec succès";
    } catch(PDOException $e) {
        // Gestion des erreurs de base de données
        return "Erreur lors de la réservation : " . $e->getMessage();
    }
}

/**
 * Met à jour le score de popularité d'un livre
 * basé sur le nombre de réservations des 30 derniers jours
 *
 * @param int $bookId ID du livre
 * @param PDO $db Instance de connexion à la base de données
 * @return void
 */
function updatePopularityScore($bookId, $db) {
    // Requête pour mettre à jour le score de popularité
    $query = "
        UPDATE books 
        SET popularity_score = (
            -- Calcul du nombre de réservations sur les 30 derniers jours
            SELECT COUNT(*) 
            FROM reservations 
            WHERE id_book = :bookId 
            AND date_reservation >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        )
        WHERE id = :bookId
    ";

    $stmt = $db->prepare($query);
    $stmt->execute(['bookId' => $bookId]);
}
