# Hôtels Premium - Système de Gestion d'Hôtels

## Description

Application web de gestion d'hôtels développée avec Symfony 7.3 et PHP 8.2. Le système permet de gérer les hôtels, chambres, réservations, travaux et services pour la chaîne d'hôtels "Hôtels Premium".

## Fonctionnalités

### Première Partie - Administration
- Gestion des hôtels, chambres et services
- Visualisation des chambres par hôtel
- Gestion des classements de chambres et prestations
- Consultation des prix par nuit
- Suivi de l'occupation en temps réel et sur plage de dates

### Deuxième Partie - Travaux
- Saisie et planification des travaux par chambre
- Suivi des travaux avec dates de début/fin
- Détection des travaux en retard

### Troisième Partie - Réservations
- Inscription et authentification clients
- Réservation de chambres avec vérification de disponibilité
- Annulation de réservations
- Tableau de bord client

## Prérequis

- PHP >= 8.2
- Composer >= 2.0
- PostgreSQL >= 17 (ou MySQL/MariaDB)
- Symfony CLI (optionnel mais recommandé)
- Node.js et npm (pour les assets)

## Installation

### Cloner le projet

```bash
git clone https://github.com/ziadchahyd/hotel-premium.git
cd hotel-premium
```

### Installer les dépendances

```bash
composer install
```

### Configurer l'environnement

Copiez le fichier .env et configurez vos variables. Éditez le fichier .env et configurez votre base de données :

```env
DATABASE_URL="postgresql://postgres:password@127.0.0.1:5432/hotel_premium?serverVersion=17&charset=utf8"
```

### Créer la base de données

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

### Charger les données de test (optionnel)

```bash
php bin/console doctrine:fixtures:load
```

### Installer les assets (optionnel)

```bash
npm install
npm run build
```

### Démarrer le serveur de développement

```bash
symfony server:start
```

Ou avec PHP intégré :

```bash
php -S localhost:8000 -t public
```

L'application sera accessible à l'adresse : http://localhost:8000

## Comptes par défaut (après fixtures)

### Administrateur
- Email : admin@hotel-premium.com
- Mot de passe : admin123 (à changer en production)

### Client de test
- Email : client@example.com
- Mot de passe : client123

## Structure du projet

```
hotel-premium/
├── assets/              # Assets front-end (JS, CSS)
├── bin/                 # Exécutables (console, phpunit)
├── config/              # Configuration Symfony
├── docs/                # Documentation du projet
├── migrations/          # Migrations Doctrine
├── public/              # Point d'entrée web
├── src/
│   ├── Command/         # Commandes console
│   ├── Controller/      # Contrôleurs
│   ├── DataFixtures/    # Fixtures pour les données de test
│   ├── Entity/          # Entités Doctrine
│   ├── Form/            # Formulaires Symfony
│   ├── Repository/      # Repositories Doctrine
│   └── Security/        # Configuration de sécurité
├── templates/           # Templates Twig
├── tests/               # Tests PHPUnit
└── translations/        # Fichiers de traduction
```

## Tests

Pour exécuter tous les tests :

```bash
php bin/phpunit
```

Pour exécuter un test spécifique :

```bash
php bin/phpunit tests/Controller/TravauxControllerTest.php
```

Pour voir la couverture de code :

```bash
php bin/phpunit --coverage-html coverage/
```

Voir docs/tests.md pour plus de détails sur les tests.

## Docker (Optionnel)

Un fichier docker-compose.yml est disponible pour faciliter le déploiement.

```bash
docker-compose up -d
```

Voir docs/docker.md pour les instructions détaillées.

## Base de données

Le schéma de la base de données est disponible dans :
- docs/schema.sql - Script SQL de création
- docs/schema.md - Documentation du schéma
- Migrations Doctrine dans migrations/

## Commandes utiles

### Doctrine

Créer une migration :
```bash
php bin/console make:migration
```

Exécuter les migrations :
```bash
php bin/console doctrine:migrations:migrate
```

Voir le schéma SQL :
```bash
php bin/console doctrine:schema:create --dump-sql
```

### Cache

Vider le cache :
```bash
php bin/console cache:clear
```

Vider le cache de production :
```bash
php bin/console cache:clear --env=prod
```

### Fixtures

Charger les fixtures :
```bash
php bin/console doctrine:fixtures:load
```

Charger sans purge :
```bash
php bin/console doctrine:fixtures:load --append
```

## Développement

Créer une nouvelle entité :
```bash
php bin/console make:entity
```

Créer un contrôleur :
```bash
php bin/console make:controller
```

Créer un formulaire :
```bash
php bin/console make:form
```

## Contribution

Ce projet est développé par une équipe de 3 personnes dans le cadre d'un projet pédagogique.

## Licence

Proprietary - Tous droits réservés

## Support

Pour toute question ou problème, contactez l'équipe de développement.



