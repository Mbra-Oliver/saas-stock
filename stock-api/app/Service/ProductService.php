<?php

namespace App\Service;

use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductService
{
    /**
     * Get all products for the authenticated user's company
     */
    public function getAllProducts($companyId, $perPage = 15, $filters = [])
    {
        $query = Product::where('company_id', $companyId)
            ->with(['category', 'supplier', 'stocks.warehouse']);

        // Apply filters
        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('sku', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('barcode', 'like', '%' . $filters['search'] . '%');
            });
        }

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (!empty($filters['supplier_id'])) {
            $query->where('supplier_id', $filters['supplier_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['low_stock']) && $filters['low_stock']) {
            $query->lowStock();
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Create a new product
     */
    public function createProduct(array $data, $companyId)
    {
        // Generate SKU if not provided
        if (empty($data['sku'])) {
            $data['sku'] = $this->generateSKU();
        }

        $data['company_id'] = $companyId;

        $product = Product::create($data);

        return $product->load(['category', 'supplier']);
    }

    /**
     * Update a product
     */
    public function updateProduct(Product $product, array $data)
    {
        $product->update($data);

        return $product->fresh(['category', 'supplier']);
    }

    /**
     * Delete a product
     */
    public function deleteProduct(Product $product)
    {
        // Check if product has stock movements
        if ($product->stockMovements()->exists()) {
            throw new \Exception('Impossible de supprimer ce produit car il a des mouvements de stock');
        }

        // Delete associated stocks
        $product->stocks()->delete();

        // Delete product
        $product->delete();

        return true;
    }

    /**
     * Get product details with stock information
     */
    public function getProductDetails(Product $product)
    {
        return $product->load([
            'category',
            'supplier',
            'stocks.warehouse',
            'stockMovements' => function ($query) {
                $query->latest()->limit(10);
            }
        ]);
    }

    /**
     * Upload product image
     */
    public function uploadImage(Product $product, $image)
    {
        // Delete old image if exists
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        // Store new image
        $path = $image->store('products', 'public');

        // Update product
        $product->update(['image' => $path]);

        return $product->fresh();
    }

    /**
     * Generate unique SKU
     */
    private function generateSKU()
    {
        do {
            $sku = 'PRD-' . strtoupper(Str::random(8));
        } while (Product::where('sku', $sku)->exists());

        return $sku;
    }

    /**
     * Search products
     */
    public function searchProducts($companyId, $query)
    {
        return Product::where('company_id', $companyId)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', '%' . $query . '%')
                    ->orWhere('sku', 'like', '%' . $query . '%')
                    ->orWhere('barcode', 'like', '%' . $query . '%');
            })
            ->with(['category', 'supplier'])
            ->limit(20)
            ->get();
    }

    /**
     * Get low stock products
     */
    public function getLowStockProducts($companyId)
    {
        return Product::where('company_id', $companyId)
            ->lowStock()
            ->with(['category', 'supplier', 'stocks.warehouse'])
            ->get();
    }

    /**
     * Import products from array
     */
    public function importProducts(array $productsData, $companyId)
    {
        $imported = 0;
        $errors = [];

        foreach ($productsData as $index => $data) {
            try {
                // Check if SKU already exists
                if (Product::where('sku', $data['sku'])->where('company_id', $companyId)->exists()) {
                    $errors[] = "Ligne " . ($index + 1) . ": SKU {$data['sku']} existe déjà";
                    continue;
                }

                $data['company_id'] = $companyId;
                Product::create($data);
                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Ligne " . ($index + 1) . ": " . $e->getMessage();
            }
        }

        return [
            'imported' => $imported,
            'errors' => $errors,
            'total' => count($productsData)
        ];
    }
}
