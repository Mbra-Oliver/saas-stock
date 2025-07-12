// app/Services/WarehouseService.php
<?php

namespace App\Services;

use App\Models\Warehouse;
use App\Models\Company;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;

class WarehouseService
{
    /**
     * Get warehouses with filters
     */
    public function getWarehouses(array $filters = []): Collection
    {
        $query = Warehouse::with('company');

        if (isset($filters['company_id'])) {
            $query->byCompany($filters['company_id']);
        }

        if (isset($filters['type'])) {
            $query->byType($filters['type']);
        }

        if (isset($filters['active'])) {
            $query->where('active', $filters['active']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Create a new warehouse
     */
    public function createWarehouse(array $data): Warehouse
    {
        // Vérifier que la company existe
        $company = Company::findOrFail($data['company_id']);

        // Générer un code unique si pas fourni
        if (!isset($data['code'])) {
            $data['code'] = $this->generateWarehouseCode($company);
        }

        return Warehouse::create($data);
    }

    /**
     * Get a specific warehouse
     */
    public function getWarehouse(int $id): Warehouse
    {
        return Warehouse::with('company')->findOrFail($id);
    }

    /**
     * Update a warehouse
     */
    public function updateWarehouse(int $id, array $data): Warehouse
    {
        $warehouse = Warehouse::findOrFail($id);

        // Si le code est modifié, vérifier l'unicité
        if (isset($data['code']) && $data['code'] !== $warehouse->code) {
            $existingWarehouse = Warehouse::where('code', $data['code'])
                ->where('id', '!=', $id)
                ->first();
            if ($existingWarehouse) {
                throw new \Exception('Warehouse code already exists');
            }
        }

        $warehouse->update($data);
        return $warehouse->fresh('company');
    }

    /**
     * Delete a warehouse
     */
    public function deleteWarehouse(int $id): bool
    {
        $warehouse = Warehouse::findOrFail($id);

        // Vérifier s'il y a des dépendances (produits en stock, etc.)
        // TODO: Ajouter les vérifications nécessaires

        return $warehouse->delete();
    }

    /**
     * Get warehouses by company
     */
    public function getWarehousesByCompany(int $companyId): Collection
    {
        return Warehouse::byCompany($companyId)
            ->active()
            ->orderBy('name')
            ->get();
    }

    /**
     * Toggle warehouse status
     */
    public function toggleWarehouseStatus(int $id): Warehouse
    {
        $warehouse = Warehouse::findOrFail($id);
        $warehouse->update(['active' => !$warehouse->active]);
        return $warehouse->fresh('company');
    }

    /**
     * Generate unique warehouse code
     */
    private function generateWarehouseCode(Company $company): string
    {
        $companyCode = Str::upper(Str::substr($company->name, 0, 3));
        $timestamp = now()->format('ymd');
        $counter = Warehouse::where('company_id', $company->id)
            ->whereDate('created_at', today())
            ->count() + 1;

        return "{$companyCode}-WH-{$timestamp}-" . str_pad($counter, 3, '0', STR_PAD_LEFT);
    }
}
