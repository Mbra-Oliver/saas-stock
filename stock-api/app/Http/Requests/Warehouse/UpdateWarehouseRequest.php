<?php

namespace App\Http\Requests\Warehouse;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWarehouseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('edit warehouses');
    }

    public function rules(): array
    {
        $warehouseId = $this->route('warehouse')->id ?? $this->route('warehouse');

        return [
            'name' => 'sometimes|required|string|max:255',
            'code' => 'sometimes|required|string|max:50|unique:warehouses,code,' . $warehouseId,
            'manager_id' => 'nullable|exists:users,id',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'status' => 'nullable|in:active,inactive',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Le nom de l\'entrepôt est requis',
            'code.required' => 'Le code est requis',
            'code.unique' => 'Ce code existe déjà',
        ];
    }
}
