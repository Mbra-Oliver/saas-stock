# Formation - API de Gestion de Stock SaaS Multi-Vendor

## Table des Matières

1. [Introduction](#introduction)
2. [Architecture du Projet](#architecture-du-projet)
3. [Installation et Configuration](#installation-et-configuration)
4. [Base de Données](#base-de-données)
5. [Authentification et Autorisation](#authentification-et-autorisation)
6. [Documentation des APIs](#documentation-des-apis)
7. [Tests et Validation](#tests-et-validation)
8. [Déploiement](#déploiement)

---

## Introduction

Cette API REST a été développée avec Laravel 12 pour gérer un système de stock multi-vendor (SaaS) avec les fonctionnalités suivantes :

- **Multi-tenant** : Support de plusieurs entreprises (companies/vendors)
- **Multi-entrepôt** : Gestion de plusieurs entrepôts par entreprise
- **Gestion des rôles** : admin, gestionnaire, caissier, auditeur
- **Gestion complète du stock** : Produits, catégories, fournisseurs, mouvements de stock
- **Commandes et ventes** : Création de commandes, génération de factures PDF
- **Inventaires** : Lancement et gestion des inventaires physiques
- **Rapports** : Génération de rapports en PDF/Excel
- **API RESTful** : Toutes les opérations via API documentées avec Swagger

---

## Architecture du Projet

### Stack Technique

- **Backend** : Laravel 12 (PHP 8.2+)
- **Base de données** : MySQL/PostgreSQL
- **Authentification** : Laravel Sanctum (Token-based)
- **Permissions** : Spatie Laravel Permission
- **Documentation API** : L5-Swagger (OpenAPI 3.0)
- **Export** : Laravel Excel, DomPDF
- **Queue** : Laravel Queue pour tâches asynchrones

### Structure des Dossiers

```
app/
├── Http/
│   ├── Controllers/
│   │   └── Api/
│   │       ├── AuthController.php
│   │       ├── CompanyController.php
│   │       ├── WarehouseController.php
│   │       ├── CategoryController.php
│   │       ├── SupplierController.php
│   │       ├── ProductController.php
│   │       ├── StockController.php
│   │       ├── OrderController.php
│   │       ├── InventoryController.php
│   │       └── ReportController.php
│   ├── Requests/
│   │   ├── Auth/
│   │   ├── Product/
│   │   ├── Stock/
│   │   └── ...
│   └── Middleware/
├── Models/
│   ├── User.php
│   ├── Company.php
│   ├── Warehouse.php
│   ├── Category.php
│   ├── Supplier.php
│   ├── Product.php
│   ├── Stock.php
│   ├── StockMovement.php
│   ├── Order.php
│   ├── OrderItem.php
│   ├── Inventory.php
│   └── ...
├── Service/
│   ├── AuthService.php
│   ├── ProductService.php
│   ├── StockService.php
│   ├── OrderService.php
│   ├── InventoryService.php
│   └── ReportService.php
├── Traits/
│   └── ApiResponse.php
├── Policies/
│   ├── ProductPolicy.php
│   ├── OrderPolicy.php
│   └── ...
├── Jobs/
│   ├── ProcessStockMovement.php
│   ├── GenerateReportJob.php
│   └── ...
└── Events/
    ├── StockUpdated.php
    └── ...
```

---

## Installation et Configuration

### Prérequis

- PHP 8.2+
- Composer
- MySQL 8.0+ ou PostgreSQL 13+
- Node.js & NPM (pour assets)

### Étapes d'Installation

#### 1. Cloner le projet

```bash
git clone <repository-url>
cd stock-api
```

#### 2. Installer les dépendances

```bash
composer install
npm install
```

#### 3. Configuration de l'environnement

Copier le fichier `.env.example` vers `.env` :

```bash
cp .env.example .env
```

Configurer les variables dans `.env` :

```env
APP_NAME="Stock Management API"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=stock_api
DB_USERNAME=root
DB_PASSWORD=

SANCTUM_STATEFUL_DOMAINS=localhost:3000
SESSION_DRIVER=cookie
```

#### 4. Générer la clé d'application

```bash
php artisan key:generate
```

#### 5. Exécuter les migrations

```bash
php artisan migrate:fresh
```

#### 6. Créer les seeders (données de test)

```bash
php artisan db:seed
```

#### 7. Publier la configuration Swagger

```bash
php artisan l5-swagger:generate
```

#### 8. Démarrer le serveur

```bash
php artisan serve
```

L'API sera accessible sur : `http://localhost:8000`
La documentation Swagger sur : `http://localhost:8000/api/documentation`

---

## Base de Données

### Schéma des Tables Principales

#### 1. **companies** (Entreprises/Vendors)
Chaque entreprise a ses propres données isolées (multi-tenant).

| Champ | Type | Description |
|-------|------|-------------|
| id | bigint | Clé primaire |
| name | string | Nom de l'entreprise |
| email | string | Email |
| phone | string | Téléphone |
| address | string | Adresse |
| currency | string | Devise (XAF, EUR, USD) |
| status | enum | active, inactive, suspended |
| subscription_plan | enum | free, basic, premium, enterprise |

#### 2. **users** (Utilisateurs)
Les utilisateurs appartiennent à une entreprise.

| Champ | Type | Description |
|-------|------|-------------|
| id | bigint | Clé primaire |
| company_id | bigint | FK vers companies |
| name | string | Nom complet |
| email | string | Email |
| phone | string | Téléphone |
| status | enum | active, inactive, suspended |

#### 3. **warehouses** (Entrepôts)
Chaque entreprise peut avoir plusieurs entrepôts.

| Champ | Type | Description |
|-------|------|-------------|
| id | bigint | Clé primaire |
| company_id | bigint | FK vers companies |
| name | string | Nom de l'entrepôt |
| code | string | Code unique |
| address | string | Adresse |
| manager_id | bigint | FK vers users (gestionnaire) |

#### 4. **products** (Produits)

| Champ | Type | Description |
|-------|------|-------------|
| id | bigint | Clé primaire |
| company_id | bigint | FK vers companies |
| name | string | Nom du produit |
| sku | string | Code SKU unique |
| barcode | string | Code-barres |
| category_id | bigint | FK vers categories |
| supplier_id | bigint | FK vers suppliers |
| purchase_price | decimal | Prix d'achat |
| selling_price | decimal | Prix de vente |
| alert_quantity | integer | Seuil d'alerte stock bas |
| track_stock | boolean | Activer le suivi du stock |

#### 5. **stocks**
Table pivot reliant produits et entrepôts.

| Champ | Type | Description |
|-------|------|-------------|
| id | bigint | Clé primaire |
| product_id | bigint | FK vers products |
| warehouse_id | bigint | FK vers warehouses |
| quantity | integer | Quantité en stock |
| reserved_quantity | integer | Quantité réservée |
| available_quantity | integer | Quantité disponible |

#### 6. **stock_movements** (Mouvements de stock)
Historique de tous les mouvements.

| Champ | Type | Description |
|-------|------|-------------|
| id | bigint | Clé primaire |
| company_id | bigint | FK vers companies |
| product_id | bigint | FK vers products |
| warehouse_id | bigint | FK vers warehouses |
| type | enum | in, out, transfer, adjustment, return |
| quantity | integer | Quantité |
| quantity_before | integer | Quantité avant |
| quantity_after | integer | Quantité après |
| created_by | bigint | FK vers users |

#### 7. **orders** (Commandes)

| Champ | Type | Description |
|-------|------|-------------|
| id | bigint | Clé primaire |
| company_id | bigint | FK vers companies |
| warehouse_id | bigint | FK vers warehouses |
| order_number | string | Numéro unique |
| type | enum | sale, purchase, return |
| status | enum | draft, pending, confirmed, processing, completed, cancelled |
| customer_name | string | Nom du client |
| subtotal | decimal | Sous-total |
| tax_amount | decimal | Montant de la taxe |
| total | decimal | Montant total |
| payment_status | enum | unpaid, partial, paid |

---

## Authentification et Autorisation

### 1. Système de Rôles (Spatie Permission)

L'application utilise 4 rôles principaux :

| Rôle | Permissions |
|------|-------------|
| **admin** | Accès complet à toutes les fonctionnalités |
| **gestionnaire** | Gestion des produits, stocks, commandes, rapports |
| **caissier** | Création de commandes, consultation de stocks |
| **auditeur** | Lecture seule, accès aux rapports |

### 2. Authentification via Sanctum

Laravel Sanctum est utilisé pour l'authentification par token.

#### Inscription

**Endpoint** : `POST /api/register`

**Body** :
```json
{
  "name": "Jean Dupont",
  "email": "jean@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "phone": "+237123456789",
  "company_name": "Mon Entreprise"
}
```

**Réponse** :
```json
{
  "success": true,
  "message": "Utilisateur créé avec succès",
  "data": {
    "user": {
      "id": 1,
      "name": "Jean Dupont",
      "email": "jean@example.com",
      "company_id": 1
    },
    "token": "1|xxxxxxxxxxxxxxxxxxxxxxxxxx"
  }
}
```

#### Connexion

**Endpoint** : `POST /api/login`

**Body** :
```json
{
  "email": "jean@example.com",
  "password": "password123"
}
```

**Réponse** :
```json
{
  "success": true,
  "message": "Connexion réussie",
  "data": {
    "user": {
      "id": 1,
      "name": "Jean Dupont",
      "email": "jean@example.com",
      "roles": ["admin"]
    },
    "token": "2|xxxxxxxxxxxxxxxxxxxxxxxxxx"
  }
}
```

#### Utilisation du Token

Pour toutes les requêtes protégées, ajouter le header :

```
Authorization: Bearer {token}
```

#### Déconnexion

**Endpoint** : `POST /api/logout`

**Headers** : `Authorization: Bearer {token}`

---

## Documentation des APIs

### Format de Réponse Standard

Toutes les réponses utilisent le trait `ApiResponse` :

**Succès** :
```json
{
  "success": true,
  "message": "Opération effectuée avec succès",
  "data": { ... }
}
```

**Erreur** :
```json
{
  "success": false,
  "message": "Une erreur est survenue",
  "errors": { ... }
}
```

**Avec Pagination** :
```json
{
  "success": true,
  "message": "Données récupérées avec succès",
  "data": [ ... ],
  "pagination": {
    "current_page": 1,
    "per_page": 15,
    "total": 100,
    "last_page": 7,
    "has_more_page": true
  }
}
```

### Endpoints Principaux

#### 1. Authentification

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| POST | `/api/register` | Inscription |
| POST | `/api/login` | Connexion |
| POST | `/api/logout` | Déconnexion |
| POST | `/api/forgot-password` | Réinitialisation mot de passe |
| GET | `/api/me` | Profil utilisateur |

#### 2. Entreprises (Companies)

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| GET | `/api/companies` | Liste des entreprises |
| POST | `/api/companies` | Créer une entreprise |
| GET | `/api/companies/{id}` | Détails d'une entreprise |
| PUT | `/api/companies/{id}` | Modifier une entreprise |
| DELETE | `/api/companies/{id}` | Supprimer une entreprise |

#### 3. Entrepôts (Warehouses)

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| GET | `/api/warehouses` | Liste des entrepôts |
| POST | `/api/warehouses` | Créer un entrepôt |
| GET | `/api/warehouses/{id}` | Détails d'un entrepôt |
| PUT | `/api/warehouses/{id}` | Modifier un entrepôt |
| DELETE | `/api/warehouses/{id}` | Supprimer un entrepôt |

#### 4. Catégories (Categories)

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| GET | `/api/categories` | Liste des catégories |
| POST | `/api/categories` | Créer une catégorie |
| GET | `/api/categories/{id}` | Détails d'une catégorie |
| PUT | `/api/categories/{id}` | Modifier une catégorie |
| DELETE | `/api/categories/{id}` | Supprimer une catégorie |
| GET | `/api/categories/{id}/products` | Produits d'une catégorie |

#### 5. Fournisseurs (Suppliers)

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| GET | `/api/suppliers` | Liste des fournisseurs |
| POST | `/api/suppliers` | Créer un fournisseur |
| GET | `/api/suppliers/{id}` | Détails d'un fournisseur |
| PUT | `/api/suppliers/{id}` | Modifier un fournisseur |
| DELETE | `/api/suppliers/{id}` | Supprimer un fournisseur |
| GET | `/api/suppliers/{id}/products` | Produits d'un fournisseur |

#### 6. Produits (Products)

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| GET | `/api/products` | Liste des produits |
| POST | `/api/products` | Créer un produit |
| GET | `/api/products/{id}` | Détails d'un produit |
| PUT | `/api/products/{id}` | Modifier un produit |
| DELETE | `/api/products/{id}` | Supprimer un produit |
| GET | `/api/products/{id}/stock` | Stock d'un produit |
| POST | `/api/products/{id}/image` | Upload image |
| POST | `/api/products/import` | Importer depuis Excel/CSV |
| GET | `/api/products/export` | Exporter vers Excel/CSV |
| GET | `/api/products/search?q={query}` | Rechercher un produit |

#### 7. Gestion du Stock

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| GET | `/api/stocks` | Liste des stocks |
| POST | `/api/stocks/in` | Entrée de stock |
| POST | `/api/stocks/out` | Sortie de stock |
| POST | `/api/stocks/transfer` | Transfert entre entrepôts |
| POST | `/api/stocks/adjustment` | Ajustement manuel |
| GET | `/api/stocks/movements` | Historique des mouvements |
| GET | `/api/stocks/alerts` | Alertes de stock bas |

**Exemple Entrée de Stock** :

POST `/api/stocks/in`
```json
{
  "product_id": 1,
  "warehouse_id": 1,
  "quantity": 100,
  "unit_cost": 5000,
  "supplier_id": 1,
  "reference": "BL-001",
  "notes": "Livraison du 08/11/2025"
}
```

#### 8. Commandes (Orders)

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| GET | `/api/orders` | Liste des commandes |
| POST | `/api/orders` | Créer une commande |
| GET | `/api/orders/{id}` | Détails d'une commande |
| PUT | `/api/orders/{id}` | Modifier une commande |
| DELETE | `/api/orders/{id}` | Supprimer une commande |
| POST | `/api/orders/{id}/confirm` | Confirmer une commande |
| POST | `/api/orders/{id}/cancel` | Annuler une commande |
| GET | `/api/orders/{id}/invoice` | Générer facture PDF |

**Exemple Création de Commande** :

POST `/api/orders`
```json
{
  "warehouse_id": 1,
  "customer_name": "Client ABC",
  "customer_email": "client@example.com",
  "customer_phone": "+237123456789",
  "items": [
    {
      "product_id": 1,
      "quantity": 10,
      "unit_price": 7500
    },
    {
      "product_id": 2,
      "quantity": 5,
      "unit_price": 12000
    }
  ],
  "payment_method": "cash",
  "notes": "Livraison urgente"
}
```

#### 9. Inventaires

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| GET | `/api/inventories` | Liste des inventaires |
| POST | `/api/inventories` | Créer un inventaire |
| GET | `/api/inventories/{id}` | Détails d'un inventaire |
| PUT | `/api/inventories/{id}/items` | Mise à jour des quantités |
| POST | `/api/inventories/{id}/complete` | Compléter l'inventaire |
| POST | `/api/inventories/{id}/adjust` | Ajuster les stocks |

**Exemple Démarrage Inventaire** :

POST `/api/inventories`
```json
{
  "warehouse_id": 1,
  "inventory_date": "2025-11-08",
  "notes": "Inventaire trimestriel Q4 2025"
}
```

#### 10. Rapports

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| GET | `/api/reports/sales` | Rapport des ventes |
| GET | `/api/reports/stock` | Rapport de stock |
| GET | `/api/reports/purchases` | Rapport des achats |
| POST | `/api/reports/generate` | Générer un rapport personnalisé |

**Exemple Rapport de Ventes** :

GET `/api/reports/sales?start_date=2025-11-01&end_date=2025-11-30&format=pdf`

---

## Tests et Validation

### Tester les APIs avec Postman

#### 1. Importer la Collection Swagger

La documentation Swagger peut être exportée en collection Postman :

1. Accéder à `http://localhost:8000/api/documentation`
2. Cliquer sur le bouton d'export JSON
3. Importer le fichier dans Postman

#### 2. Configuration de l'Environnement

Dans Postman, créer un environnement avec les variables :

```
base_url: http://localhost:8000/api
token: (sera défini après login)
```

#### 3. Workflow de Test Complet

1. **Inscription** : POST `/register`
   - Récupérer le token dans la réponse
   - Le sauvegarder dans la variable `token`

2. **Créer un Entrepôt** : POST `/warehouses`
   - Header : `Authorization: Bearer {{token}}`

3. **Créer une Catégorie** : POST `/categories`

4. **Créer un Fournisseur** : POST `/suppliers`

5. **Créer un Produit** : POST `/products`

6. **Entrée de Stock** : POST `/stocks/in`

7. **Créer une Commande** : POST `/orders`

8. **Générer une Facture** : GET `/orders/{id}/invoice`

### Tests Unitaires

Les tests peuvent être exécutés avec Pest :

```bash
php artisan test
```

Exemple de test :

```php
test('can create a product', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)
        ->postJson('/api/products', [
            'name' => 'Test Product',
            'sku' => 'TEST-001',
            'selling_price' => 10000,
        ]);

    $response->assertStatus(201)
        ->assertJson(['success' => true]);
});
```

---

## Déploiement

### Déploiement sur un Serveur Linux (Ubuntu)

#### 1. Prérequis Serveur

```bash
sudo apt update
sudo apt install php8.2 php8.2-fpm php8.2-mysql php8.2-xml php8.2-curl php8.2-mbstring
sudo apt install mysql-server nginx composer
```

#### 2. Configuration Nginx

Fichier `/etc/nginx/sites-available/stock-api` :

```nginx
server {
    listen 80;
    server_name api.example.com;
    root /var/www/stock-api/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Activer le site :

```bash
sudo ln -s /etc/nginx/sites-available/stock-api /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

#### 3. Déploiement du Code

```bash
cd /var/www
git clone <repository-url> stock-api
cd stock-api
composer install --optimize-autoloader --no-dev
cp .env.example .env
php artisan key:generate
php artisan migrate --force
php artisan l5-swagger:generate
```

#### 4. Permissions

```bash
sudo chown -R www-data:www-data /var/www/stock-api
sudo chmod -R 755 /var/www/stock-api
sudo chmod -R 775 /var/www/stock-api/storage
sudo chmod -R 775 /var/www/stock-api/bootstrap/cache
```

#### 5. Optimisation Production

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
composer dump-autoload --optimize
```

#### 6. Configuration de la Queue

Installer Supervisor :

```bash
sudo apt install supervisor
```

Fichier `/etc/supervisor/conf.d/stock-api-worker.conf` :

```ini
[program:stock-api-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/stock-api/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/stock-api/storage/logs/worker.log
stopwaitsecs=3600
```

Démarrer :

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start stock-api-worker:*
```

---

## Bonnes Pratiques

### 1. Sécurité

- Toujours valider les entrées utilisateur
- Utiliser les Form Requests de Laravel
- Limiter les rate limits sur les routes d'authentification
- Ne jamais exposer les clés API dans le code
- Utiliser HTTPS en production
- Activer CORS uniquement pour les domaines autorisés

### 2. Performance

- Utiliser l'eager loading pour éviter le N+1 :
  ```php
  Product::with(['category', 'supplier'])->get();
  ```

- Mettre en cache les données fréquemment utilisées :
  ```php
  Cache::remember('products', 3600, function () {
      return Product::all();
  });
  ```

- Utiliser les queues pour les tâches lourdes :
  ```php
  GenerateReportJob::dispatch($report);
  ```

### 3. Code Quality

- Respecter les standards PSR-12
- Utiliser Laravel Pint pour formater le code :
  ```bash
  ./vendor/bin/pint
  ```

- Écrire des tests pour les fonctionnalités critiques
- Documenter toutes les APIs avec Swagger

---

## Support et Contribution

Pour toute question ou suggestion :

- **Email** : support@example.com
- **Documentation** : http://localhost:8000/api/documentation
- **Repository** : https://github.com/your-repo/stock-api

---

## Changelog

### Version 1.0.0 (2025-11-08)

- Initialisation du projet
- Mise en place de l'architecture multi-tenant
- Création de toutes les migrations
- Implémentation des APIs de base
- Documentation Swagger complète
- Tests unitaires et d'intégration
