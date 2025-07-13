<?php

namespace App\Http\Requests\Warehouse;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Traits\ApiResponse;

class StoreWarehouseRequest extends FormRequest
{
    use ApiResponse;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'code' => 'sometimes|string|max:50|unique:warehouses,code',
            'description' => 'nullable|string|max:1000',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'sometimes|string|max:2|in:CI,FR,SN,ML,BF', // Pays d'Afrique de l'Ouest
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'capacity' => 'nullable|numeric|min:0|max:999999.99',
            'type' => 'sometimes|in:main,secondary,temporary',
            'active' => 'sometimes|boolean',

        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Le nom de l\'entrepôt est obligatoire.',
            'name.max' => 'Le nom ne peut pas dépasser 255 caractères.',
            'code.unique' => 'Ce code d\'entrepôt existe déjà.',
            'address.required' => 'L\'adresse est obligatoire.',
            'city.required' => 'La ville est obligatoire.',
            'email.email' => 'L\'email doit être valide.',
            'capacity.numeric' => 'La capacité doit être un nombre.',
            'capacity.min' => 'La capacité doit être positive.',
            'type.in' => 'Le type d\'entrepôt doit être: main, secondary ou temporary.',

        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            $this->validationError($validator->errors()->toArray())
        );
    }
}
