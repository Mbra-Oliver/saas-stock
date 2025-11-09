<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create orders');
    }

    public function rules(): array
    {
        return [
            'warehouse_id' => 'required|exists:warehouses,id',
            'type' => 'required|in:sale,purchase,return',
            'order_number' => 'nullable|string|max:255|unique:orders,order_number',
            'order_date' => 'nullable|date',

            // Customer info (for sales)
            'customer_name' => 'nullable|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'customer_phone' => 'nullable|string|max:50',
            'customer_address' => 'nullable|string',

            // Supplier info (for purchases)
            'supplier_id' => 'nullable|exists:suppliers,id',

            'reference' => 'nullable|string|max:255',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'shipping_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',

            // Order items
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
            'items.*.discount_amount' => 'nullable|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'warehouse_id.required' => 'L\'entrepôt est requis',
            'type.required' => 'Le type de commande est requis',
            'type.in' => 'Le type doit être: sale, purchase ou return',
            'items.required' => 'Au moins un article est requis',
            'items.*.product_id.required' => 'Le produit est requis pour chaque article',
            'items.*.quantity.required' => 'La quantité est requise pour chaque article',
            'items.*.unit_price.required' => 'Le prix unitaire est requis pour chaque article',
        ];
    }
}
