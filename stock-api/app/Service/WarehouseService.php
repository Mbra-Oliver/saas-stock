<?php

namespace App\Service;

use App\Models\Warehouse;

class WarehouseService
{
    public function getAllWarehouses($companyId, $perPage = 15, $filters = [])
    {
        $query = Warehouse::with('manager')
            ->where('company_id', $companyId);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('code', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('city', 'like', '%' . $filters['search'] . '%');
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function createWarehouse(array $data, $companyId)
    {
        $data['company_id'] = $companyId;
        return Warehouse::create($data);
    }

    public function updateWarehouse(Warehouse $warehouse, array $data)
    {
        $warehouse->update($data);
        return $warehouse->fresh('manager');
    }

    public function deleteWarehouse(Warehouse $warehouse)
    {
        // Check if warehouse has stock
        if ($warehouse->stocks()->exists()) {
            throw new \Exception('Impossible de supprimer un entrepôt ayant des stocks');
        }

        $warehouse->delete();
    }

    public function getWarehouseDetails(Warehouse $warehouse)
    {
        return $warehouse->load(['manager', 'stocks.product']);
    }
}
