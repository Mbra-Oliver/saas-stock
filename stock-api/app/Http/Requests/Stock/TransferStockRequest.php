<?php

namespace App\Http\Requests\Stock;

use Illuminate\Foundation\Http\FormRequest;

class TransferStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage stock');
    }

    public function rules(): array
    {
        return [
            'product_id' => 'required|exists:products,id',
            'from_warehouse_id' => 'required|exists:warehouses,id',
            'to_warehouse_id' => 'required|exists:warehouses,id|different:from_warehouse_id',
            'quantity' => 'required|integer|min:1',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required' => 'Le produit est requis',
            'product_id.exists' => 'Le produit sélectionné n\'existe pas',
            'from_warehouse_id.required' => 'L\'entrepôt source est requis',
            'from_warehouse_id.exists' => 'L\'entrepôt source n\'existe pas',
            'to_warehouse_id.required' => 'L\'entrepôt destination est requis',
            'to_warehouse_id.exists' => 'L\'entrepôt destination n\'existe pas',
            'to_warehouse_id.different' => 'L\'entrepôt destination doit être différent de l\'entrepôt source',
            'quantity.required' => 'La quantité est requise',
            'quantity.min' => 'La quantité doit être au moins 1',
        ];
    }
}
