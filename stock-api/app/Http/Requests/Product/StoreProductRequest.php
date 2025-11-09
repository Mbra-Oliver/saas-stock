<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create products');
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:255|unique:products,sku',
            'barcode' => 'nullable|string|max:255|unique:products,barcode',
            'category_id' => 'nullable|exists:categories,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'description' => 'nullable|string',
            'type' => 'nullable|in:simple,variable,service',
            'unit' => 'nullable|string|max:50',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'wholesale_price' => 'nullable|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'alert_quantity' => 'nullable|integer|min:0',
            'minimum_quantity' => 'nullable|integer|min:1',
            'maximum_quantity' => 'nullable|integer|min:1',
            'expiry_date' => 'nullable|date',
            'status' => 'nullable|in:active,inactive,out_of_stock',
            'track_stock' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Le nom du produit est requis',
            'sku.unique' => 'Ce SKU existe déjà',
            'barcode.unique' => 'Ce code-barres existe déjà',
            'purchase_price.required' => 'Le prix d\'achat est requis',
            'selling_price.required' => 'Le prix de vente est requis',
            'category_id.exists' => 'La catégorie sélectionnée n\'existe pas',
            'supplier_id.exists' => 'Le fournisseur sélectionné n\'existe pas',
        ];
    }
}
