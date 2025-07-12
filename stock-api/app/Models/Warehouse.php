<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Warehouse extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'address',
        'city',
        'postal_code',
        'country',
        'phone',
        'email',
        'capacity',
        'type',
        'active',
        'company_id'
    ];

    protected $casts = [
        'active' => 'boolean',
        'capacity' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    public function scopeByCompany(Builder $query, int $companyId): Builder
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    // Accessors
    public function getFullAddressAttribute(): string
    {
        return "{$this->address}, {$this->city}" .
            ($this->postal_code ? ", {$this->postal_code}" : '') .
            ", {$this->country}";
    }
}
