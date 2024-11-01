# Système de Recommandation de Livres pour Bibliothèque

## Description
Système de recommandation qui suggère des livres aux utilisateurs en se basant sur leurs lectures passées, leurs genres préférés et la popularité des livres. Le système analyse l'historique des réservations pour créer des recommandations personnalisées.

## Fonctionnalités
- Recommandations basées sur les genres préférés de l'utilisateur
- Suggestions de livres populaires
- Système de réservation de livres
- Calcul automatique du score de popularité
- Interface utilisateur intuitive

## Prérequis
- PHP 7.4 ou supérieur
- MySQL 5.7 ou supérieur
- Serveur web Apache
- Module PDO PHP activé

## Installation

### 1. Configuration du Serveur Web
Copier les fichiers du projet dans le répertoire du serveur web, par exemple `./www` du wamp-server.


### 2. Base de Données
```sql
-- Créer les tables
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    genre VARCHAR(100) NOT NULL,
    popularity_score FLOAT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE reservations (
    id_user INT,
    id_book INT,
    date_reservation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_user) REFERENCES users(id),
    FOREIGN KEY (id_book) REFERENCES books(id),
    PRIMARY KEY (id_user, id_book)
);

-- Insérer des données de test
INSERT INTO users (name, email) VALUES ('Test User', 'test@example.com');

INSERT INTO books (title, genre, popularity_score) VALUES
('Le Seigneur des Anneaux', 'Fantasy', 0),
('Fondation', 'Science-Fiction', 0),
('1984', 'Science-Fiction', 0),
('Le Nom de la Rose', 'Historique', 0),
('Da Vinci Code', 'Thriller', 0);
```

### 3. Structure du Projet
```
project/
├── config/
│   └── database.php     # Configuration de la base de données
├── includes/
│   └── functions.php    # Fonctions utilitaires
└── index.php           # Page principale
```

### 4. Configuration de la Base de Données
Modifier le fichier `config/database.php` avec vos informations :
```php
private $host = "localhost";
private $db_name = "library_system";
private $username = "votre_username";
private $password = "votre_password";
```

## Utilisation

1. Accéder au site via `http://localhost/nom_du_projet`
2. Les recommandations sont affichées sur la page d'accueil
3. Pour réserver un livre, cliquer sur le bouton "Réserver"
4. Les recommandations sont mises à jour automatiquement en fonction des réservations

## Fonctionnement du Système de Recommandation

### Score de Popularité
- Calculé automatiquement lors des réservations
- Basé sur le nombre de réservations des 30 derniers jours
- Mis à jour à chaque nouvelle réservation

### Recommandations Personnalisées
- Basées sur les genres les plus réservés par l'utilisateur
- Prend en compte la popularité générale des livres
- Exclut les livres déjà réservés par l'utilisateur

## Maintenance

### Mise à Jour des Scores
Les scores de popularité sont mis à jour automatiquement, mais peuvent être recalculés manuellement :
```sql
UPDATE books b
SET popularity_score = (
    SELECT COUNT(*)
    FROM reservations r
    WHERE r.id_book = b.id
    AND r.date_reservation >= DATE_SUB(NOW(), INTERVAL 30 DAY)
);
```

## Licence
Ce projet est sous licence MIT. Voir le fichier `LICENSE` pour plus de détails.
