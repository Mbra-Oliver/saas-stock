<?php

namespace App\Http\Requests\Warehouse;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Traits\ApiResponse;
use Illuminate\Validation\Rule;

class UpdateWarehouseRequest extends FormRequest
{
    use ApiResponse;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $warehouseId = $this->route('warehouse');

        return [
            'name' => 'sometimes|string|max:255',
            'code' => [
                'sometimes',
                'string',
                'max:50',
                Rule::unique('warehouses', 'code')->ignore($warehouseId)
            ],
            'description' => 'nullable|string|max:1000',
            'address' => 'sometimes|string|max:500',
            'city' => 'sometimes|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'sometimes|string|max:2|in:CI,FR,SN,ML,BF',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'capacity' => 'nullable|numeric|min:0|max:999999.99',
            'type' => 'sometimes|in:main,secondary,temporary',
            'active' => 'sometimes|boolean',
            'company_id' => 'sometimes|exists:companies,id',
        ];
    }

    public function messages(): array
    {
        return [
            'name.max' => 'Le nom ne peut pas dépasser 255 caractères.',
            'code.unique' => 'Ce code d\'entrepôt existe déjà.',
            'email.email' => 'L\'email doit être valide.',
            'capacity.numeric' => 'La capacité doit être un nombre.',
            'capacity.min' => 'La capacité doit être positive.',
            'type.in' => 'Le type d\'entrepôt doit être: main, secondary ou temporary.',
            'company_id.exists' => 'L\'entreprise sélectionnée n\'existe pas.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            $this->validationError($validator->errors()->toArray())
        );
    }
}
