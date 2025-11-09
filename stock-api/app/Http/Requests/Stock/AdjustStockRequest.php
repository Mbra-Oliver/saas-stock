<?php

namespace App\Http\Requests\Stock;

use Illuminate\Foundation\Http\FormRequest;

class AdjustStockRequest extends FormRequest
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
            'new_quantity' => 'required|integer|min:0',
            'reference' => 'nullable|string|max:255',
            'notes' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required' => 'Le produit est requis',
            'product_id.exists' => 'Le produit sélectionné n\'existe pas',
            'warehouse_id.required' => 'L\'entrepôt est requis',
            'warehouse_id.exists' => 'L\'entrepôt sélectionné n\'existe pas',
            'new_quantity.required' => 'La nouvelle quantité est requise',
            'new_quantity.min' => 'La quantité doit être au moins 0',
            'notes.required' => 'Une note explicative est requise pour un ajustement',
        ];
    }
}
