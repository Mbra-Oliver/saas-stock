<?php

namespace App\Http\Requests\Stock;

use Illuminate\Foundation\Http\FormRequest;

class StockInRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage stock');
    }

    public function rules(): array
    {
        return [
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'quantity' => 'required|integer|min:1',
            'cost_price' => 'nullable|numeric|min:0',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'order_id' => 'nullable|exists:orders,id',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required' => 'Le produit est requis',
            'product_id.exists' => 'Le produit sélectionné n\'existe pas',
            'warehouse_id.required' => 'L\'entrepôt est requis',
            'warehouse_id.exists' => 'L\'entrepôt sélectionné n\'existe pas',
            'quantity.required' => 'La quantité est requise',
            'quantity.min' => 'La quantité doit être au moins 1',
        ];
    }
}
