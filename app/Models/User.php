<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;
use App\Models\StoreOwner;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
    
    /**
     * Get the stores that the user is an employee of.
     */
    public function stores(): BelongsToMany
    {
        return $this->belongsToMany(Store::class, 'store_employees')
                    ->withPivot('role')
                    ->withTimestamps();
    }
    
    /**
     * Check if the user is an employee of the given store.
     *
     * @param Store $store
     * @return bool
     */
    public function isEmployeeOf(Store $store): bool
    {
        return $this->stores()->where('store_id', $store->id)->exists();
    }
    
    /**
     * Check if the user has a specific role in the given store.
     *
     * @param Store $store
     * @param string $role
     * @return bool
     */
    public function hasStoreRole(Store $store, string $role): bool
    {
        $storeEmployee = $this->stores()
                              ->where('store_id', $store->id)
                              ->first();
        
        if (!$storeEmployee) {
            return false;
        }
        
        return $storeEmployee->pivot->role === $role ||
               $storeEmployee->pivot->role === StoreEmployee::ROLE_ADMIN ||
               $storeEmployee->pivot->role === StoreEmployee::ROLE_MANAGER;
    }
    
    /**
     * Get the store owners associated with the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function storeOwners(): HasMany
    {
        return $this->hasMany(StoreOwner::class, 'user_id');
    }
}
