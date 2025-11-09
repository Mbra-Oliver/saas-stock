<?php

namespace App\Service;

use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use Exception;

class StockService
{
    /**
     * Get all stocks for a company with filters
     */
    public function getAllStocks($companyId, $perPage = 15, $filters = [])
    {
        $query = Stock::with(['product', 'warehouse'])
            ->whereHas('product', function ($q) use ($companyId) {
                $q->where('company_id', $companyId);
            });

        // Apply filters
        if (!empty($filters['warehouse_id'])) {
            $query->where('warehouse_id', $filters['warehouse_id']);
        }

        if (!empty($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        if (!empty($filters['low_stock'])) {
            $query->whereColumn('quantity', '<=', 'alert_quantity');
        }

        if (!empty($filters['search'])) {
            $query->whereHas('product', function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('sku', 'like', '%' . $filters['search'] . '%');
            });
        }

        return $query->orderBy('updated_at', 'desc')->paginate($perPage);
    }

    /**
     * Stock In - Increase stock quantity
     */
    public function stockIn(array $data, $companyId)
    {
        return DB::transaction(function () use ($data, $companyId) {
            // Verify product belongs to company
            $product = Product::where('id', $data['product_id'])
                ->where('company_id', $companyId)
                ->firstOrFail();

            // Get or create stock record
            $stock = Stock::firstOrCreate(
                [
                    'product_id' => $data['product_id'],
                    'warehouse_id' => $data['warehouse_id']
                ],
                [
                    'quantity' => 0,
                    'reserved_quantity' => 0,
                    'alert_quantity' => $product->alert_quantity ?? 10,
                    'last_restock_date' => now()
                ]
            );

            // Update stock quantity
            $oldQuantity = $stock->quantity;
            $stock->quantity += $data['quantity'];
            $stock->last_restock_date = now();
            $stock->save();

            // Create stock movement record
            $movement = StockMovement::create([
                'product_id' => $data['product_id'],
                'warehouse_id' => $data['warehouse_id'],
                'type' => 'in',
                'quantity' => $data['quantity'],
                'previous_quantity' => $oldQuantity,
                'new_quantity' => $stock->quantity,
                'reference' => $data['reference'] ?? null,
                'order_id' => $data['order_id'] ?? null,
                'supplier_id' => $data['supplier_id'] ?? null,
                'cost_price' => $data['cost_price'] ?? $product->purchase_price,
                'notes' => $data['notes'] ?? null,
                'created_by' => auth()->id()
            ]);

            return [
                'stock' => $stock->load(['product', 'warehouse']),
                'movement' => $movement
            ];
        });
    }

    /**
     * Stock Out - Decrease stock quantity
     */
    public function stockOut(array $data, $companyId)
    {
        return DB::transaction(function () use ($data, $companyId) {
            // Verify product belongs to company
            $product = Product::where('id', $data['product_id'])
                ->where('company_id', $companyId)
                ->firstOrFail();

            // Get stock record
            $stock = Stock::where('product_id', $data['product_id'])
                ->where('warehouse_id', $data['warehouse_id'])
                ->firstOrFail();

            // Check if enough stock available
            $availableQuantity = $stock->quantity - $stock->reserved_quantity;
            if ($availableQuantity < $data['quantity']) {
                throw new Exception('Stock insuffisant. Disponible: ' . $availableQuantity);
            }

            // Update stock quantity
            $oldQuantity = $stock->quantity;
            $stock->quantity -= $data['quantity'];
            $stock->save();

            // Create stock movement record
            $movement = StockMovement::create([
                'product_id' => $data['product_id'],
                'warehouse_id' => $data['warehouse_id'],
                'type' => 'out',
                'quantity' => $data['quantity'],
                'previous_quantity' => $oldQuantity,
                'new_quantity' => $stock->quantity,
                'reference' => $data['reference'] ?? null,
                'order_id' => $data['order_id'] ?? null,
                'customer_id' => $data['customer_id'] ?? null,
                'cost_price' => $data['cost_price'] ?? $product->purchase_price,
                'notes' => $data['notes'] ?? null,
                'created_by' => auth()->id()
            ]);

            return [
                'stock' => $stock->load(['product', 'warehouse']),
                'movement' => $movement
            ];
        });
    }

    /**
     * Transfer stock between warehouses
     */
    public function transferStock(array $data, $companyId)
    {
        return DB::transaction(function () use ($data, $companyId) {
            // Verify product belongs to company
            $product = Product::where('id', $data['product_id'])
                ->where('company_id', $companyId)
                ->firstOrFail();

            // Get source stock
            $sourceStock = Stock::where('product_id', $data['product_id'])
                ->where('warehouse_id', $data['from_warehouse_id'])
                ->firstOrFail();

            // Check if enough stock available
            $availableQuantity = $sourceStock->quantity - $sourceStock->reserved_quantity;
            if ($availableQuantity < $data['quantity']) {
                throw new Exception('Stock insuffisant. Disponible: ' . $availableQuantity);
            }

            // Update source stock
            $sourceOldQuantity = $sourceStock->quantity;
            $sourceStock->quantity -= $data['quantity'];
            $sourceStock->save();

            // Get or create destination stock
            $destStock = Stock::firstOrCreate(
                [
                    'product_id' => $data['product_id'],
                    'warehouse_id' => $data['to_warehouse_id']
                ],
                [
                    'quantity' => 0,
                    'reserved_quantity' => 0,
                    'alert_quantity' => $product->alert_quantity ?? 10,
                    'last_restock_date' => now()
                ]
            );

            // Update destination stock
            $destOldQuantity = $destStock->quantity;
            $destStock->quantity += $data['quantity'];
            $destStock->last_restock_date = now();
            $destStock->save();

            // Create stock movement records
            $outMovement = StockMovement::create([
                'product_id' => $data['product_id'],
                'warehouse_id' => $data['from_warehouse_id'],
                'type' => 'transfer_out',
                'quantity' => $data['quantity'],
                'previous_quantity' => $sourceOldQuantity,
                'new_quantity' => $sourceStock->quantity,
                'reference' => $data['reference'] ?? 'TRF-' . time(),
                'notes' => $data['notes'] ?? null,
                'created_by' => auth()->id()
            ]);

            $inMovement = StockMovement::create([
                'product_id' => $data['product_id'],
                'warehouse_id' => $data['to_warehouse_id'],
                'type' => 'transfer_in',
                'quantity' => $data['quantity'],
                'previous_quantity' => $destOldQuantity,
                'new_quantity' => $destStock->quantity,
                'reference' => $data['reference'] ?? 'TRF-' . time(),
                'notes' => $data['notes'] ?? null,
                'created_by' => auth()->id()
            ]);

            return [
                'source_stock' => $sourceStock->load(['product', 'warehouse']),
                'destination_stock' => $destStock->load(['product', 'warehouse']),
                'movements' => [$outMovement, $inMovement]
            ];
        });
    }

    /**
     * Adjust stock quantity
     */
    public function adjustStock(array $data, $companyId)
    {
        return DB::transaction(function () use ($data, $companyId) {
            // Verify product belongs to company
            $product = Product::where('id', $data['product_id'])
                ->where('company_id', $companyId)
                ->firstOrFail();

            // Get stock record
            $stock = Stock::where('product_id', $data['product_id'])
                ->where('warehouse_id', $data['warehouse_id'])
                ->firstOrFail();

            // Update stock quantity
            $oldQuantity = $stock->quantity;
            $stock->quantity = $data['new_quantity'];
            $stock->save();

            // Create stock movement record
            $movement = StockMovement::create([
                'product_id' => $data['product_id'],
                'warehouse_id' => $data['warehouse_id'],
                'type' => 'adjustment',
                'quantity' => abs($data['new_quantity'] - $oldQuantity),
                'previous_quantity' => $oldQuantity,
                'new_quantity' => $stock->quantity,
                'reference' => $data['reference'] ?? 'ADJ-' . time(),
                'notes' => $data['notes'] ?? null,
                'created_by' => auth()->id()
            ]);

            return [
                'stock' => $stock->load(['product', 'warehouse']),
                'movement' => $movement
            ];
        });
    }

    /**
     * Get stock movements history
     */
    public function getStockMovements($companyId, $perPage = 15, $filters = [])
    {
        $query = StockMovement::with(['product', 'warehouse', 'user', 'order'])
            ->whereHas('product', function ($q) use ($companyId) {
                $q->where('company_id', $companyId);
            });

        // Apply filters
        if (!empty($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        if (!empty($filters['warehouse_id'])) {
            $query->where('warehouse_id', $filters['warehouse_id']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Get low stock products across all warehouses
     */
    public function getLowStockProducts($companyId)
    {
        return Stock::with(['product', 'warehouse'])
            ->whereHas('product', function ($q) use ($companyId) {
                $q->where('company_id', $companyId);
            })
            ->whereColumn('quantity', '<=', 'alert_quantity')
            ->orderBy('quantity', 'asc')
            ->get();
    }

    /**
     * Get stock details for a product
     */
    public function getProductStockDetails($productId, $companyId)
    {
        $product = Product::where('id', $productId)
            ->where('company_id', $companyId)
            ->firstOrFail();

        $stocks = Stock::with('warehouse')
            ->where('product_id', $productId)
            ->get();

        $totalQuantity = $stocks->sum('quantity');
        $totalReserved = $stocks->sum('reserved_quantity');
        $totalAvailable = $totalQuantity - $totalReserved;

        return [
            'product' => $product,
            'stocks' => $stocks,
            'summary' => [
                'total_quantity' => $totalQuantity,
                'total_reserved' => $totalReserved,
                'total_available' => $totalAvailable,
                'warehouses_count' => $stocks->count()
            ]
        ];
    }
}
