<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('edit orders');
    }

    public function rules(): array
    {
        return [
            'warehouse_id' => 'sometimes|required|exists:warehouses,id',
            'customer_name' => 'nullable|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'customer_phone' => 'nullable|string|max:50',
            'customer_address' => 'nullable|string',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',

            // Order items
            'items' => 'sometimes|array|min:1',
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
            'warehouse_id.exists' => 'L\'entrepôt sélectionné n\'existe pas',
            'items.*.product_id.required' => 'Le produit est requis pour chaque article',
            'items.*.quantity.required' => 'La quantité est requise pour chaque article',
            'items.*.unit_price.required' => 'Le prix unitaire est requis pour chaque article',
        ];
    }
}
