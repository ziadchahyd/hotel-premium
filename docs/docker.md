# Docker - Hôtels Premium

## Vue d'ensemble

Ce document décrit la configuration Docker pour le projet Hôtels Premium.

## Prérequis

- Docker version 20.10 ou supérieure
- Docker Compose version 2.0 ou supérieure

## Structure Docker

### Fichiers Docker

- Dockerfile - Image PHP/Symfony
- docker-compose.yml - Orchestration des services
- .dockerignore - Fichiers à ignorer lors du build

## Services

### Application PHP/Symfony
Image : php:8.2-fpm ou symfony/php
Port : 9000 (PHP-FPM)
Volumes : Code source monté

### Base de données PostgreSQL
Image : postgres:17
Port : 5432
Variables d'environnement :
  - POSTGRES_DB: hotel_premium
  - POSTGRES_USER: postgres
  - POSTGRES_PASSWORD: password

### Nginx (Optionnel)
Image : nginx:alpine
Port : 80
Configuration : Proxy vers PHP-FPM

## Utilisation

### Démarrer les services

```bash
docker-compose up -d
```

### Arrêter les services

```bash
docker-compose down
```

### Voir les logs

```bash
docker-compose logs -f
```

### Exécuter des commandes Symfony

Via docker-compose exec :
```bash
docker-compose exec php bin/console cache:clear
```

Pour les migrations :
```bash
docker-compose exec php bin/console doctrine:migrations:migrate
```

Pour les fixtures :
```bash
docker-compose exec php bin/console doctrine:fixtures:load
```

## Configuration

### Variables d'environnement

Créer un fichier .env.docker avec :

```env
DATABASE_URL=postgresql://postgres:password@db:5432/hotel_premium?serverVersion=17
```

### Ports

- Application : http://localhost:8000
- PostgreSQL : localhost:5432
- Si Nginx est configuré : http://localhost:80

## Build de l'image

```bash
docker-compose build
```

## Dépannage

### Accès à la base de données

```bash
docker-compose exec db psql -U postgres -d hotel_premium
```

### Shell dans le container PHP

```bash
docker-compose exec php bash
```

### Réinitialiser les volumes

```bash
docker-compose down -v
```

## Production

Note importante : Cette configuration est pour le développement. Pour la production, il faut ajuster plusieurs éléments :
- Variables d'environnement sécurisées
- Volumes persistants
- Configuration Nginx optimisée
- HTTPS/SSL
- Gestion des secrets

