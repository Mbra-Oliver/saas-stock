<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, HasApiTokens, HasRoles;

    protected $fillable = [
        'company_id',
        'name',
        'email',
        'password',
        'phone',
        'status',
        'avatar',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Relations
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function createdStockMovements()
    {
        return $this->hasMany(StockMovement::class, 'created_by');
    }

    public function createdOrders()
    {
        return $this->hasMany(Order::class, 'created_by');
    }

    public function createdInventories()
    {
        return $this->hasMany(Inventory::class, 'created_by');
    }

    public function completedInventories()
    {
        return $this->hasMany(Inventory::class, 'completed_by');
    }

    public function generatedReports()
    {
        return $this->hasMany(Report::class, 'generated_by');
    }

    public function managedWarehouses()
    {
        return $this->hasMany(Warehouse::class, 'manager_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeOfCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }
}
