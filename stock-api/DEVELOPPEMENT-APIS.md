# Guide de Développement Complet des APIs

## Statut Actuel du Projet

### ✅ Complété

1. **Dépendances Installées**
   - Laravel Sanctum (authentification)
   - Spatie Laravel Permission (gestion des rôles)
   - L5-Swagger (documentation API)
   - Laravel Excel (import/export)
   - DomPDF (génération PDF)

2. **Base de Données**
   - ✅ 15 migrations créées et exécutées avec succès
   - ✅ Structure multi-tenant complète
   - ✅ Support multi-entrepôt
   - ✅ Toutes les tables relationnelles configurées

3. **Documentation**
   - ✅ FORMATION.md créé avec guide complet d'utilisation

###  🔄 En Cours

4. **Modèles Laravel** (Models)
   - Modèles créés mais nécessitent configuration des relations

### ⏳ À Faire

5. Services
6. Controllers
7. Form Requests
8. Policies
9. Routes API
10. Jobs & Events
11. Annotations Swagger

---

## Étapes Restantes Détaillées

### ÉTAPE 1 : Configuration des Modèles

Chaque modèle doit être configuré avec :
- Fillable attributes
- Relations (hasMany, belongsTo, etc.)
- Casts
- Scopes
- Trait HasFactory, SoftDeletes si applicable

#### Liste des modèles à configurer :

1. **User.php** - Ajouter relations avec Company, créé par, etc.
2. **Company.php** - Relations avec Users, Warehouses, Products, etc.
3. **Warehouse.php** - Relations avec Company, Products (via Stock), etc.
4. **Category.php** - Auto-relation (parent/children), Products
5. **Supplier.php** - Relations avec Company, Products
6. **Product.php** - Relations avec Category, Supplier, Stocks, etc.
7. **Stock.php** - Relations avec Product, Warehouse
8. **StockMovement.php** - Relations avec Product, Warehouse, User (created_by)
9. **Order.php** - Relations avec OrderItems, Warehouse, User (created_by)
10. **OrderItem.php** - Relations avec Order, Product
11. **Inventory.php** - Relations avec InventoryItems, Warehouse
12. **InventoryItem.php** - Relations avec Inventory, Product
13. **Setting.php** - Relation avec Company
14. **Report.php** - Relation avec Company, User (generated_by)

### ÉTAPE 2 : Mettre à Jour User Model

Le modèle User doit inclure Sanctum pour l'authentification et Spatie Permission pour les rôles.

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, HasApiTokens, HasRoles;

    protected $fillable = [
        'company_id',
        'name',
        'email',
        'password',
        'phone',
        'status',
        'avatar',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Relations
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function createdStockMovements()
    {
        return $this->hasMany(StockMovement::class, 'created_by');
    }

    public function createdOrders()
    {
        return $this->hasMany(Order::class, 'created_by');
    }

    public function createdInventories()
    {
        return $this->hasMany(Inventory::class, 'created_by');
    }

    public function generatedReports()
    {
        return $this->hasMany(Report::class, 'generated_by');
    }

    public function managedWarehouses()
    {
        return $this->hasMany(Warehouse::class, 'manager_id');
    }
}
```

### ÉTAPE 3 : Configuration Complète de Tous les Modèles

Je vais créer un fichier de référence pour chaque modèle.

---

## Commandes Artisan pour Générer les Composants

### 1. Controllers

```bash
# Controllers API
php artisan make:controller Api/CompanyController --api
php artisan make:controller Api/WarehouseController --api
php artisan make:controller Api/CategoryController --api
php artisan make:controller Api/SupplierController --api
php artisan make:controller Api/ProductController --api
php artisan make:controller Api/StockController --api
php artisan make:controller Api/OrderController --api
php artisan make:controller Api/InventoryController --api
php artisan make:controller Api/ReportController --api
php artisan make:controller Api/DashboardController
```

### 2. Form Requests

```bash
# Auth Requests
php artisan make:request Auth/RegisterRequest
php artisan make:request Auth/LoginRequest

# Company Requests
php artisan make:request Company/StoreCompanyRequest
php artisan make:request Company/UpdateCompanyRequest

# Warehouse Requests
php artisan make:request Warehouse/StoreWarehouseRequest
php artisan make:request Warehouse/UpdateWarehouseRequest

# Category Requests
php artisan make:request Category/StoreCategoryRequest
php artisan make:request Category/UpdateCategoryRequest

# Supplier Requests
php artisan make:request Supplier/StoreSupplierRequest
php artisan make:request Supplier/UpdateSupplierRequest

# Product Requests
php artisan make:request Product/StoreProductRequest
php artisan make:request Product/UpdateProductRequest

# Stock Requests
php artisan make:request Stock/StockInRequest
php artisan make:request Stock/StockOutRequest
php artisan make:request Stock/StockTransferRequest
php artisan make:request Stock/StockAdjustmentRequest

# Order Requests
php artisan make:request Order/StoreOrderRequest
php artisan make:request Order/UpdateOrderRequest

# Inventory Requests
php artisan make:request Inventory/StoreInventoryRequest
php artisan make:request Inventory/UpdateInventoryItemsRequest
```

### 3. Services

Les services doivent être créés manuellement dans `app/Service/` :

- `CompanyService.php`
- `WarehouseService.php`
- `CategoryService.php`
- `SupplierService.php`
- `ProductService.php`
- `StockService.php`
- `OrderService.php`
- `InventoryService.php`
- `ReportService.php`

### 4. Policies

```bash
php artisan make:policy CompanyPolicy --model=Company
php artisan make:policy WarehousePolicy --model=Warehouse
php artisan make:policy CategoryPolicy --model=Category
php artisan make:policy SupplierPolicy --model=Supplier
php artisan make:policy ProductPolicy --model=Product
php artisan make:policy StockPolicy --model=Stock
php artisan make:policy OrderPolicy --model=Order
php artisan make:policy InventoryPolicy --model=Inventory
php artisan make:policy ReportPolicy --model=Report
```

### 5. Jobs

```bash
php artisan make:job ProcessStockMovement
php artisan make:job GenerateReportJob
php artisan make:job SendStockAlertJob
php artisan make:job ExportProductsJob
php artisan make:job ImportProductsJob
```

### 6. Events

```bash
php artisan make:event StockUpdated
php artisan make:event OrderCreated
php artisan make:event OrderCompleted
php artisan make:event InventoryCompleted
php artisan make:event LowStockAlert
```

### 7. Listeners

```bash
php artisan make:listener SendStockAlertNotification --event=LowStockAlert
php artisan make:listener UpdateStockAfterOrder --event=OrderCompleted
php artisan make:listener LogStockMovement --event=StockUpdated
```

---

## Configuration des Routes API

Fichier `routes/api.php` :

```php
<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\WarehouseController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\StockController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\DashboardController;
use Illuminate\Support\Facades\Route;

// Public routes (sans authentification)
Route::prefix('v1')->group(function () {
    // Authentication
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);
});

// Protected routes (avec authentification Sanctum)
Route::prefix('v1')->middleware(['auth:sanctum'])->group(function () {

    // Auth user
    Route::get('me', [AuthController::class, 'me']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::put('profile', [AuthController::class, 'updateProfile']);
    Route::put('password', [AuthController::class, 'changePassword']);

    // Dashboard
    Route::get('dashboard', [DashboardController::class, 'index']);
    Route::get('dashboard/stats', [DashboardController::class, 'stats']);

    // Companies
    Route::apiResource('companies', CompanyController::class);

    // Warehouses
    Route::apiResource('warehouses', WarehouseController::class);
    Route::get('warehouses/{warehouse}/products', [WarehouseController::class, 'products']);
    Route::get('warehouses/{warehouse}/stocks', [WarehouseController::class, 'stocks']);

    // Categories
    Route::apiResource('categories', CategoryController::class);
    Route::get('categories/{category}/products', [CategoryController::class, 'products']);

    // Suppliers
    Route::apiResource('suppliers', SupplierController::class);
    Route::get('suppliers/{supplier}/products', [SupplierController::class, 'products']);

    // Products
    Route::apiResource('products', ProductController::class);
    Route::get('products/search/{query}', [ProductController::class, 'search']);
    Route::get('products/{product}/stock', [ProductController::class, 'stock']);
    Route::post('products/{product}/image', [ProductController::class, 'uploadImage']);
    Route::post('products/import', [ProductController::class, 'import']);
    Route::get('products/export', [ProductController::class, 'export']);

    // Stock Management
    Route::prefix('stocks')->group(function () {
        Route::get('/', [StockController::class, 'index']);
        Route::post('in', [StockController::class, 'stockIn']);
        Route::post('out', [StockController::class, 'stockOut']);
        Route::post('transfer', [StockController::class, 'transfer']);
        Route::post('adjustment', [StockController::class, 'adjustment']);
        Route::get('movements', [StockController::class, 'movements']);
        Route::get('alerts', [StockController::class, 'alerts']);
    });

    // Orders
    Route::apiResource('orders', OrderController::class);
    Route::post('orders/{order}/confirm', [OrderController::class, 'confirm']);
    Route::post('orders/{order}/cancel', [OrderController::class, 'cancel']);
    Route::get('orders/{order}/invoice', [OrderController::class, 'generateInvoice']);

    // Inventories
    Route::apiResource('inventories', InventoryController::class);
    Route::put('inventories/{inventory}/items', [InventoryController::class, 'updateItems']);
    Route::post('inventories/{inventory}/complete', [InventoryController::class, 'complete']);
    Route::post('inventories/{inventory}/adjust', [InventoryController::class, 'adjustStock']);

    // Reports
    Route::prefix('reports')->group(function () {
        Route::get('sales', [ReportController::class, 'sales']);
        Route::get('stock', [ReportController::class, 'stock']);
        Route::get('purchases', [ReportController::class, 'purchases']);
        Route::post('generate', [ReportController::class, 'generate']);
        Route::get('{report}/download', [ReportController::class, 'download']);
    });
});
```

---

## Configuration de Swagger

### 1. Publier la Configuration

```bash
php artisan vendor:publish --provider "L5Swagger\L5SwaggerServiceProvider"
```

### 2. Configuration dans `config/l5-swagger.php`

Modifier le fichier pour définir :

```php
'defaults' => [
    'routes' => [
        'api' => 'api/documentation',
    ],
    'paths' => [
        'docs' => storage_path('api-docs'),
        'views' => base_path('resources/views/vendor/l5-swagger'),
        'base' => env('L5_SWAGGER_BASE_PATH', null),
        'swagger_ui_assets_path' => env('L5_SWAGGER_UI_ASSETS_PATH', 'vendor/swagger-api/swagger-ui/dist/'),
        'excludes' => [],
    ],
],
```

### 3. Annotations de Base dans Controller

Exemple pour `AuthController.php` :

```php
/**
 * @OA\Info(
 *     title="Stock Management API",
 *     version="1.0.0",
 *     description="API de gestion de stock multi-vendor et multi-entrepôt",
 *     @OA\Contact(
 *         email="support@example.com"
 *     )
 * )
 *
 * @OA\Server(
 *     url="http://localhost:8000/api/v1",
 *     description="Serveur de développement"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/register",
     *     tags={"Authentication"},
     *     summary="Inscription d'un nouvel utilisateur",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password","company_name"},
     *             @OA\Property(property="name", type="string", example="Jean Dupont"),
     *             @OA\Property(property="email", type="string", format="email", example="jean@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="password123"),
     *             @OA\Property(property="phone", type="string", example="+237123456789"),
     *             @OA\Property(property="company_name", type="string", example="Mon Entreprise")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Utilisateur créé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Utilisateur créé avec succès"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user", type="object"),
     *                 @OA\Property(property="token", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=422, description="Erreur de validation")
     * )
     */
    public function register(RegisterRequest $request)
    {
        // Implementation
    }
}
```

### 4. Générer la Documentation

```bash
php artisan l5-swagger:generate
```

Documentation accessible sur : `http://localhost:8000/api/documentation`

---

## Seeders pour Données de Test

### 1. Créer les Seeders

```bash
php artisan make:seeder RoleAndPermissionSeeder
php artisan make:seeder CompanySeeder
php artisan make:seeder UserSeeder
php artisan make:seeder WarehouseSeeder
php artisan make:seeder CategorySeeder
php artisan make:seeder SupplierSeeder
php artisan make:seeder ProductSeeder
```

### 2. RoleAndPermissionSeeder

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Products
            'view products',
            'create products',
            'edit products',
            'delete products',

            // Stock
            'view stock',
            'manage stock',
            'adjust stock',

            // Orders
            'view orders',
            'create orders',
            'edit orders',
            'delete orders',
            'confirm orders',

            // Reports
            'view reports',
            'generate reports',

            // Settings
            'manage settings',
            'manage users',
            'manage roles',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles
        $admin = Role::create(['name' => 'admin']);
        $admin->givePermissionTo(Permission::all());

        $gestionnaire = Role::create(['name' => 'gestionnaire']);
        $gestionnaire->givePermissionTo([
            'view products', 'create products', 'edit products',
            'view stock', 'manage stock',
            'view orders', 'create orders', 'edit orders', 'confirm orders',
            'view reports', 'generate reports',
        ]);

        $caissier = Role::create(['name' => 'caissier']);
        $caissier->givePermissionTo([
            'view products',
            'view stock',
            'view orders', 'create orders',
        ]);

        $auditeur = Role::create(['name' => 'auditeur']);
        $auditeur->givePermissionTo([
            'view products',
            'view stock',
            'view orders',
            'view reports',
        ]);
    }
}
```

### 3. Exécuter les Seeders

```bash
php artisan db:seed
```

---

## Prochaines Étapes

1. ✅ Terminer la configuration de tous les modèles
2. ✅ Créer tous les Controllers avec la logique métier
3. ✅ Créer tous les Form Requests pour validation
4. ✅ Créer tous les Services
5. ✅ Configurer toutes les routes
6. ✅ Ajouter les annotations Swagger à tous les endpoints
7. ✅ Créer les Policies pour les autorisations
8. ✅ Créer les Jobs et Events
9. ✅ Écrire les tests
10. ✅ Générer la documentation Swagger finale

---

## Commandes Utiles

### Développement

```bash
# Lancer le serveur
php artisan serve

# Lancer la queue
php artisan queue:work

# Lancer les tests
php artisan test

# Nettoyer le cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Formater le code
./vendor/bin/pint
```

### Production

```bash
# Optimiser l'application
php artisan config:cache
php artisan route:cache
php artisan view:cache
composer dump-autoload --optimize
```

---

## Notes Importantes

1. **Multi-tenant** : Toutes les requêtes doivent filtrer par `company_id` de l'utilisateur connecté
2. **Validation** : Utiliser des Form Requests pour toute validation
3. **Autorisation** : Vérifier les permissions avec les Policies
4. **Sécurité** : Ne jamais exposer d'informations sensibles dans les réponses API
5. **Performance** : Utiliser l'eager loading pour éviter le N+1 problem
6. **Tests** : Écrire des tests pour toutes les fonctionnalités critiques

---

Fin du guide de développement.
