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
            'import products',
            'export products',

            // Categories
            'view categories',
            'create categories',
            'edit categories',
            'delete categories',

            // Suppliers
            'view suppliers',
            'create suppliers',
            'edit suppliers',
            'delete suppliers',

            // Warehouses
            'view warehouses',
            'create warehouses',
            'edit warehouses',
            'delete warehouses',

            // Stock
            'view stock',
            'stock in',
            'stock out',
            'transfer stock',
            'adjust stock',
            'view stock movements',

            // Orders
            'view orders',
            'create orders',
            'edit orders',
            'delete orders',
            'confirm orders',
            'cancel orders',
            'generate invoice',

            // Inventories
            'view inventories',
            'create inventories',
            'complete inventories',
            'adjust inventory',

            // Reports
            'view reports',
            'generate reports',
            'export reports',

            // Settings
            'manage settings',
            'manage users',
            'manage roles',
            'manage company',

            // Dashboard
            'view dashboard',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions

        // ADMIN - All permissions
        $admin = Role::create(['name' => 'admin']);
        $admin->givePermissionTo(Permission::all());

        // GESTIONNAIRE - Manage products, stock, orders, reports
        $gestionnaire = Role::create(['name' => 'gestionnaire']);
        $gestionnaire->givePermissionTo([
            'view products', 'create products', 'edit products', 'delete products', 'import products', 'export products',
            'view categories', 'create categories', 'edit categories', 'delete categories',
            'view suppliers', 'create suppliers', 'edit suppliers', 'delete suppliers',
            'view warehouses', 'create warehouses', 'edit warehouses',
            'view stock', 'stock in', 'stock out', 'transfer stock', 'adjust stock', 'view stock movements',
            'view orders', 'create orders', 'edit orders', 'delete orders', 'confirm orders', 'cancel orders', 'generate invoice',
            'view inventories', 'create inventories', 'complete inventories', 'adjust inventory',
            'view reports', 'generate reports', 'export reports',
            'view dashboard',
        ]);

        // CAISSIER - Create orders, view stock
        $caissier = Role::create(['name' => 'caissier']);
        $caissier->givePermissionTo([
            'view products',
            'view categories',
            'view stock',
            'view orders', 'create orders', 'generate invoice',
            'view dashboard',
        ]);

        // AUDITEUR - Read only
        $auditeur = Role::create(['name' => 'auditeur']);
        $auditeur->givePermissionTo([
            'view products',
            'view categories',
            'view suppliers',
            'view warehouses',
            'view stock', 'view stock movements',
            'view orders',
            'view inventories',
            'view reports',
            'view dashboard',
        ]);

        $this->command->info('Roles and permissions seeded successfully!');
    }
}
