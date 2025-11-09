<?php

namespace App\Service;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Stock;
use Illuminate\Support\Facades\DB;
use Exception;

class OrderService
{
    /**
     * Get all orders for a company with filters
     */
    public function getAllOrders($companyId, $perPage = 15, $filters = [])
    {
        $query = Order::with(['items.product', 'user', 'warehouse'])
            ->where('company_id', $companyId);

        // Apply filters
        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['payment_status'])) {
            $query->where('payment_status', $filters['payment_status']);
        }

        if (!empty($filters['warehouse_id'])) {
            $query->where('warehouse_id', $filters['warehouse_id']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('order_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('order_date', '<=', $filters['date_to']);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('order_number', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('customer_name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('reference', 'like', '%' . $filters['search'] . '%');
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Create a new order
     */
    public function createOrder(array $data, $companyId)
    {
        return DB::transaction(function () use ($data, $companyId) {
            // Generate order number if not provided
            if (empty($data['order_number'])) {
                $data['order_number'] = $this->generateOrderNumber($data['type']);
            }

            // Calculate totals from items
            $subtotal = 0;
            foreach ($data['items'] as $item) {
                $subtotal += $item['quantity'] * $item['unit_price'];
            }

            $taxAmount = $subtotal * ($data['tax_rate'] ?? 0) / 100;
            $discount = $data['discount_amount'] ?? 0;
            $shipping = $data['shipping_cost'] ?? 0;
            $total = $subtotal + $taxAmount - $discount + $shipping;

            // Create order
            $order = Order::create([
                'company_id' => $companyId,
                'warehouse_id' => $data['warehouse_id'],
                'order_number' => $data['order_number'],
                'type' => $data['type'],
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'order_date' => $data['order_date'] ?? now(),
                'customer_name' => $data['customer_name'] ?? null,
                'customer_email' => $data['customer_email'] ?? null,
                'customer_phone' => $data['customer_phone'] ?? null,
                'customer_address' => $data['customer_address'] ?? null,
                'supplier_id' => $data['supplier_id'] ?? null,
                'reference' => $data['reference'] ?? null,
                'subtotal' => $subtotal,
                'tax_rate' => $data['tax_rate'] ?? 0,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discount,
                'shipping_cost' => $shipping,
                'total' => $total,
                'notes' => $data['notes'] ?? null,
                'created_by' => auth()->id()
            ]);

            // Create order items
            foreach ($data['items'] as $itemData) {
                $product = Product::findOrFail($itemData['product_id']);

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $itemData['product_id'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'tax_rate' => $itemData['tax_rate'] ?? 0,
                    'discount_amount' => $itemData['discount_amount'] ?? 0,
                    'subtotal' => $itemData['quantity'] * $itemData['unit_price']
                ]);
            }

            return $order->load(['items.product', 'warehouse']);
        });
    }

    /**
     * Update an order
     */
    public function updateOrder(Order $order, array $data)
    {
        return DB::transaction(function () use ($order, $data) {
            // Only allow updates if order is still pending
            if ($order->status !== 'pending') {
                throw new Exception('Impossible de modifier une commande confirmée');
            }

            // Update order
            $order->update([
                'warehouse_id' => $data['warehouse_id'] ?? $order->warehouse_id,
                'customer_name' => $data['customer_name'] ?? $order->customer_name,
                'customer_email' => $data['customer_email'] ?? $order->customer_email,
                'customer_phone' => $data['customer_phone'] ?? $order->customer_phone,
                'customer_address' => $data['customer_address'] ?? $order->customer_address,
                'supplier_id' => $data['supplier_id'] ?? $order->supplier_id,
                'reference' => $data['reference'] ?? $order->reference,
                'notes' => $data['notes'] ?? $order->notes,
            ]);

            // Update items if provided
            if (!empty($data['items'])) {
                // Delete old items
                $order->items()->delete();

                // Calculate new totals
                $subtotal = 0;
                foreach ($data['items'] as $itemData) {
                    $product = Product::findOrFail($itemData['product_id']);

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $itemData['product_id'],
                        'quantity' => $itemData['quantity'],
                        'unit_price' => $itemData['unit_price'],
                        'tax_rate' => $itemData['tax_rate'] ?? 0,
                        'discount_amount' => $itemData['discount_amount'] ?? 0,
                        'subtotal' => $itemData['quantity'] * $itemData['unit_price']
                    ]);

                    $subtotal += $itemData['quantity'] * $itemData['unit_price'];
                }

                $taxAmount = $subtotal * ($order->tax_rate) / 100;
                $total = $subtotal + $taxAmount - $order->discount_amount + $order->shipping_cost;

                $order->update([
                    'subtotal' => $subtotal,
                    'tax_amount' => $taxAmount,
                    'total' => $total
                ]);
            }

            return $order->fresh(['items.product', 'warehouse']);
        });
    }

    /**
     * Confirm an order
     */
    public function confirmOrder(Order $order)
    {
        return DB::transaction(function () use ($order) {
            if ($order->status !== 'pending') {
                throw new Exception('Cette commande est déjà confirmée');
            }

            // Update order status
            $order->update([
                'status' => 'confirmed',
                'confirmed_at' => now(),
                'confirmed_by' => auth()->id()
            ]);

            // For sales orders, reduce stock
            if ($order->type === 'sale') {
                foreach ($order->items as $item) {
                    $stock = Stock::where('product_id', $item->product_id)
                        ->where('warehouse_id', $order->warehouse_id)
                        ->first();

                    if (!$stock || $stock->quantity < $item->quantity) {
                        throw new Exception("Stock insuffisant pour le produit: {$item->product->name}");
                    }

                    $stock->decrement('quantity', $item->quantity);
                }
            }

            // For purchase orders, increase stock
            if ($order->type === 'purchase') {
                foreach ($order->items as $item) {
                    $stock = Stock::firstOrCreate(
                        [
                            'product_id' => $item->product_id,
                            'warehouse_id' => $order->warehouse_id
                        ],
                        [
                            'quantity' => 0,
                            'reserved_quantity' => 0,
                            'alert_quantity' => $item->product->alert_quantity ?? 10
                        ]
                    );

                    $stock->increment('quantity', $item->quantity);
                    $stock->update(['last_restock_date' => now()]);
                }
            }

            return $order->fresh(['items.product', 'warehouse']);
        });
    }

    /**
     * Cancel an order
     */
    public function cancelOrder(Order $order)
    {
        return DB::transaction(function () use ($order) {
            if ($order->status === 'cancelled') {
                throw new Exception('Cette commande est déjà annulée');
            }

            if ($order->status === 'confirmed') {
                // If order was confirmed, reverse stock changes
                if ($order->type === 'sale') {
                    foreach ($order->items as $item) {
                        $stock = Stock::where('product_id', $item->product_id)
                            ->where('warehouse_id', $order->warehouse_id)
                            ->first();

                        if ($stock) {
                            $stock->increment('quantity', $item->quantity);
                        }
                    }
                }

                if ($order->type === 'purchase') {
                    foreach ($order->items as $item) {
                        $stock = Stock::where('product_id', $item->product_id)
                            ->where('warehouse_id', $order->warehouse_id)
                            ->first();

                        if ($stock) {
                            $stock->decrement('quantity', $item->quantity);
                        }
                    }
                }
            }

            $order->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancelled_by' => auth()->id()
            ]);

            return $order->fresh(['items.product', 'warehouse']);
        });
    }

    /**
     * Update payment status
     */
    public function updatePaymentStatus(Order $order, string $status)
    {
        $order->update([
            'payment_status' => $status,
            'paid_at' => $status === 'paid' ? now() : null
        ]);

        return $order->fresh();
    }

    /**
     * Get order details
     */
    public function getOrderDetails(Order $order)
    {
        return $order->load(['items.product', 'warehouse', 'user', 'supplier']);
    }

    /**
     * Generate unique order number
     */
    private function generateOrderNumber($type)
    {
        $prefix = match($type) {
            'sale' => 'SO',
            'purchase' => 'PO',
            'return' => 'RO',
            default => 'ORD'
        };

        $lastOrder = Order::where('type', $type)
            ->where('order_number', 'like', $prefix . '-%')
            ->orderBy('created_at', 'desc')
            ->first();

        if ($lastOrder) {
            $lastNumber = (int) substr($lastOrder->order_number, strlen($prefix) + 1);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . '-' . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Get order statistics
     */
    public function getOrderStats($companyId, $filters = [])
    {
        $query = Order::where('company_id', $companyId);

        if (!empty($filters['date_from'])) {
            $query->whereDate('order_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('order_date', '<=', $filters['date_to']);
        }

        $stats = [
            'total_orders' => $query->count(),
            'pending_orders' => (clone $query)->where('status', 'pending')->count(),
            'confirmed_orders' => (clone $query)->where('status', 'confirmed')->count(),
            'cancelled_orders' => (clone $query)->where('status', 'cancelled')->count(),
            'total_sales' => (clone $query)->where('type', 'sale')->where('status', 'confirmed')->sum('total'),
            'total_purchases' => (clone $query)->where('type', 'purchase')->where('status', 'confirmed')->sum('total'),
            'paid_amount' => (clone $query)->where('payment_status', 'paid')->sum('total'),
            'unpaid_amount' => (clone $query)->where('payment_status', 'unpaid')->sum('total'),
        ];

        return $stats;
    }
}
