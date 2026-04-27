# TaskManager

Application Laravel de gestion de tâches personnelles avec authentification.

## Fonctionnalités

- Inscription/Connexion/Déconnexion
- CRUD complet des tâches
- Catégories et statuts
- Filtres par statut et catégorie
- Changement rapide de statut
- Pagination

## Installation

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```