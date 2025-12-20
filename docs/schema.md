# Schéma de la Base de Données

## Vue d'ensemble

La base de données hotel_premium est composée de plusieurs tables interconnectées pour gérer les hôtels, chambres, réservations, clients et travaux.

## Tables principales

### Hotel
Cette table stocke les informations des hôtels de la chaîne.

Colonnes :
- id (INT, PK) - Identifiant unique
- name (VARCHAR 255) - Nom de l'hôtel
- adress (VARCHAR 255) - Adresse
- city (VARCHAR 100) - Ville
- description (TEXT) - Description
- created_at (DATETIME) - Date de création

Relations :
- Un hôtel a plusieurs chambres (OneToMany vers Chambre)

### Chambre
Représente une chambre dans un hôtel.

Colonnes :
- id (INT, PK) - Identifiant unique
- number (INT) - Numéro de chambre
- floor (INT) - Étage
- area (FLOAT) - Surface en m²
- price_per_night (FLOAT) - Prix par nuit
- is_available (BOOLEAN) - Disponibilité
- hotel_id (INT, FK) - Référence à Hotel
- classement_id (INT, FK) - Référence à ClassementH

Relations :
- Appartient à un hôtel (ManyToOne vers Hotel)
- Appartient à un classement (ManyToOne vers ClassementH)
- Peut avoir plusieurs services (ManyToMany vers Service)
- Peut avoir plusieurs travaux (OneToMany vers Travaux)
- Peut avoir plusieurs réservations (OneToMany vers Reservation)

### ClassementH
Définit les classements de chambres comme Standard, Deluxe, Suite, etc.

Colonnes :
- id (INT, PK) - Identifiant unique
- name (VARCHAR 100) - Nom du classement
- description (TEXT) - Description
- base_price (FLOAT) - Prix de base

Relations :
- Un classement peut avoir plusieurs chambres (OneToMany vers Chambre)

### Service
Liste des services disponibles comme WiFi, Climatisation, etc.

Colonnes :
- id (INT, PK) - Identifiant unique
- name (VARCHAR 100) - Nom du service
- description (TEXT) - Description

Relations :
- Un service peut être associé à plusieurs chambres (ManyToMany vers Chambre)

### Client
Comptes utilisateurs pour les clients.

Colonnes :
- id (INT, PK) - Identifiant unique
- email (VARCHAR 180, UNIQUE) - Email utilisé comme identifiant
- password (VARCHAR) - Mot de passe hashé
- roles (JSON) - Rôles, généralement ROLE_CLIENT
- first_name (VARCHAR 100) - Prénom
- last_name (VARCHAR 100) - Nom
- created_at (DATETIME) - Date de création

Relations :
- Un client peut avoir plusieurs réservations (OneToMany vers Reservation)

### Admin
Comptes administrateurs.

Colonnes :
- id (INT, PK) - Identifiant unique
- email (VARCHAR 180, UNIQUE) - Email utilisé comme identifiant
- password (VARCHAR) - Mot de passe hashé
- roles (JSON) - Rôles, généralement ROLE_ADMIN
- first_name (VARCHAR 100) - Prénom
- last_name (VARCHAR 100) - Nom
- created_at (DATETIME) - Date de création

### Reservation
Réservations de chambres par les clients.

Colonnes :
- id (INT, PK) - Identifiant unique
- check_in (DATE) - Date d'arrivée
- check_out (DATE) - Date de départ
- status (VARCHAR 50) - Statut : pending, confirmed, cancelled
- total_price (FLOAT) - Prix total
- created_at (DATETIME) - Date de création
- client_id (INT, FK) - Référence à Client
- chambre_id (INT, FK) - Référence à Chambre

Relations :
- Appartient à un client (ManyToOne vers Client)
- Appartient à une chambre (ManyToOne vers Chambre)

### Travaux
Travaux de maintenance ou remplacement sur les chambres.

Colonnes :
- id (INT, PK) - Identifiant unique
- title (VARCHAR 250) - Titre des travaux
- description (TEXT) - Description détaillée
- start_date (DATETIME) - Date de début
- end_date (DATETIME) - Date de fin prévue
- is_done (BOOLEAN) - Indique si les travaux sont terminés ou non
- chambre_id (INT, FK) - Référence à Chambre

Relations :
- Appartient à une chambre (ManyToOne vers Chambre)

## Tables de relation

### chambre_service
Table de liaison ManyToMany entre Chambre et Service.

Colonnes :
- chambre_id (INT, FK) - Référence à Chambre
- service_id (INT, FK) - Référence à Service

## Diagramme de relations

Hotel (1) vers (N) Chambre
Chambre (N) vers (1) Hotel
Chambre (N) vers (1) ClassementH
Chambre (N) vers (N) Service via chambre_service
Chambre (1) vers (N) Travaux
Chambre (1) vers (N) Reservation

Client (1) vers (N) Reservation

## Contraintes importantes

1. Unicité : Une chambre ne peut pas être réservée deux fois sur la même période. Cette vérification est faite au niveau de l'application.

2. Disponibilité : Une chambre en travaux (is_done = false) ne peut pas être réservée.

3. Dates : Pour les réservations, check_out doit être après check_in.

4. Intégrité référentielle : Toutes les clés étrangères sont contraintes pour maintenir la cohérence des données.

## Index recommandés

Pour améliorer les performances, les index suivants sont recommandés :
- hotel_id sur Chambre
- client_id sur Reservation
- chambre_id sur Reservation (pour vérification disponibilité)
- check_in et check_out sur Reservation (pour requêtes de disponibilité)
- email sur Client et Admin (déjà unique, indexé automatiquement)

