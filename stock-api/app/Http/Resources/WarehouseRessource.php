<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class WarehouseResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'description' => $this->description,
            'address' => $this->address,
            'city' => $this->city,
            'postal_code' => $this->postal_code,
            'country' => $this->country,
            'full_address' => $this->full_address,
            'phone' => $this->phone,
            'email' => $this->email,
            'capacity' => $this->capacity,
            'type' => $this->type,
            'type_label' => $this->getTypeLabel(),
            'active' => $this->active,
            'status' => $this->active ? 'Actif' : 'Inactif',
            'company' => [
                'id' => $this->company->id,
                'name' => $this->company->name,
                'city' => $this->company->city,
            ],
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }

    private function getTypeLabel(): string
    {
        return match ($this->type) {
            'main' => 'Principal',
            'secondary' => 'Secondaire',
            'temporary' => 'Temporaire',
            default => 'Inconnu'
        };
    }
}
