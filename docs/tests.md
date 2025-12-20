# Campagne de Tests - Hôtels Premium

## Vue d'ensemble

Ce document décrit la stratégie de tests et la couverture de code pour le projet Hôtels Premium.

## Environnement de test

Framework utilisé : PHPUnit 11.5
Configuration : phpunit.dist.xml
Base de données de test : Base séparée configurée dans .env.test

## Structure des tests

Les tests sont organisés dans le dossier tests/ avec la même structure que le code source.

```
tests/
├── bootstrap.php                    # Configuration de bootstrap
└── Controller/
    └── TravauxControllerTest.php    # Tests du contrôleur Travaux
```

## Types de tests

### Tests unitaires
Ces tests vérifient les composants isolés comme les entités, repositories et services.

### Tests d'intégration
Ces tests vérifient les contrôleurs et l'interaction avec la base de données.

### Tests fonctionnels
Ces tests vérifient les fonctionnalités complètes de l'application.

## Tests existants

### TravauxControllerTest
- testIndex() - Test de la page d'index des travaux (implémenté)
- testNew() - Test de création de travaux (incomplet, marqué markTestIncomplete)
- testShow() - Test d'affichage d'un travail (incomplet, marqué markTestIncomplete)
- testEdit() - Test de modification de travaux (incomplet, marqué markTestIncomplete)
- testRemove() - Test de suppression de travaux (incomplet, marqué markTestIncomplete)

## Tests à implémenter

### Controllers

#### AdminControllerTest
Tests pour le contrôleur administrateur :
- testLogin() - Test de connexion admin
- testDashboard() - Test d'accès au dashboard
- testOccupation() - Test de la vue occupation temps réel
- testOccupationRange() - Test de la vue occupation sur plage de dates

#### ReservationControllerTest
Tests pour le contrôleur de réservation :
- testIndex() - Test de la liste des réservations
- testNew() - Test de création de réservation
- testShow() - Test d'affichage d'une réservation
- testCancel() - Test d'annulation de réservation
- testApiChambresDisponibles() - Test de l'API de disponibilité

#### ClientControllerTest
Tests pour le contrôleur client :
- testDashboard() - Test du dashboard client
- testAccessRestriction() - Test des restrictions d'accès

### Repositories

#### ReservationRepositoryTest
Tests pour le repository des réservations :
- testIsChambreAvailable() - Test de disponibilité chambre
- testFindByClient() - Test de recherche par client
- testGetCurrentlyOccupiedChambres() - Test chambres actuellement occupées

#### TravauxRepositoryTest
Tests pour le repository des travaux :
- testFindLate() - Test des travaux en retard

#### ChambreRepositoryTest
Tests pour le repository des chambres :
- testFindWithCurrentWorks() - Test chambres actuellement en travaux

### Entités

Pour chaque entité, créer des tests unitaires :
- Test des getters et setters
- Test des relations entre entités
- Test des validations

## Exécution des tests

### Exécuter tous les tests

```bash
php bin/phpunit
```

### Exécuter un test spécifique

Pour tester un fichier spécifique :
```bash
php bin/phpunit tests/Controller/TravauxControllerTest.php
```

Pour tester une méthode spécifique :
```bash
php bin/phpunit --filter testIndex
```

### Voir la couverture de code

```bash
php bin/phpunit --coverage-html coverage/
```

Ensuite, ouvrir coverage/index.html dans un navigateur pour voir les résultats.

## Résultats attendus

### Couverture minimale
- Contrôleurs : 70% minimum
- Repositories : 80% minimum
- Services : 80% minimum
- Entités : 60% minimum

## Bonnes pratiques

1. Isolation : Chaque test doit être indépendant et ne pas dépendre d'autres tests.

2. Setup/Teardown : Utiliser setUp() et tearDown() pour préparer et nettoyer l'environnement de test.

3. Fixtures : Utiliser les fixtures pour les données de test plutôt que de créer les données manuellement.

4. Assertions : Utiliser des assertions claires et spécifiques pour faciliter le débogage.

5. Nommage : Nommer les tests de manière claire, par exemple testNomDeLaMethode().

## Commandes utiles

Créer un test avec MakerBundle :
```bash
php bin/console make:test
```

Voir la configuration PHPUnit :
```bash
cat phpunit.dist.xml
```

Exécuter avec mode verbeux pour plus de détails :
```bash
php bin/phpunit -v
```

