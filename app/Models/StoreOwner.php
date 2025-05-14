<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class StoreOwner extends Authenticatable
{
    use HasFactory, Notifiable, TwoFactorAuthenticatable;
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'store_owners';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'store_id',
        'user_id',
        'permissions',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'permissions' => 'array',
    ];

    /**
     * Get the user that owns the store owner.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the store that the store owner owns.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
    
    /**
     * Check if the store owner owns the specified store.
     *
     * @param int $storeId
     * @return bool
     */
    public function ownsStore(int $storeId): bool
    {
        return $this->store_id == $storeId;
    }
    
    /**
     * Check if the store owner has the given permission.
     *
     * @param string $permission
     * @return bool
     */
    public function hasPermission(string $permission): bool
    {
        // Store owners have these permissions by default
        $defaultPermissions = [
            'view-store', 'edit-store', 
            'manage-products', 
            'manage-orders', 
            'manage-staff'
        ];
        
        // Ensure permissions is an array
        $permissions = $this->permissions;
        if (is_string($permissions)) {
            $permissions = json_decode($permissions, true) ?? [];
        } elseif (empty($permissions)) {
            $permissions = [];
        }
        
        // Merge default permissions with any custom permissions
        $allPermissions = array_merge($defaultPermissions, $permissions);
        
        // Check if the store owner has the permission
        return in_array($permission, $allPermissions);
    }
    
    /**
     * Get the login username.
     *
     * @return string
     */
    public function getAuthIdentifierName()
    {
        return 'id';
    }
    
    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->user->password;
    }
    
    /**
     * Get the email address for the user.
     *
     * @return string
     */
    public function getEmailAttribute()
    {
        return $this->user->email;
    }
    
    /**
     * Get the name for the user.
     *
     * @return string
     */
    public function getNameAttribute()
    {
        return $this->user->name;
    }
}
