<?php

namespace App\Service;

use App\Models\Supplier;

class SupplierService
{
    public function getAllSuppliers($companyId, $perPage = 15, $filters = [])
    {
        $query = Supplier::where('company_id', $companyId);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('email', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('phone', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('company', 'like', '%' . $filters['search'] . '%');
            });
        }

        return $query->orderBy('name', 'asc')->paginate($perPage);
    }

    public function createSupplier(array $data, $companyId)
    {
        $data['company_id'] = $companyId;
        return Supplier::create($data);
    }

    public function updateSupplier(Supplier $supplier, array $data)
    {
        $supplier->update($data);
        return $supplier->fresh();
    }

    public function deleteSupplier(Supplier $supplier)
    {
        // Check if supplier has products
        if ($supplier->products()->exists()) {
            throw new \Exception('Impossible de supprimer un fournisseur ayant des produits associés');
        }

        $supplier->delete();
    }

    public function getSupplierDetails(Supplier $supplier)
    {
        return $supplier->load('products');
    }
}
