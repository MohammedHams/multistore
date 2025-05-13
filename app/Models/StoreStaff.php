<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreStaff extends Model
{
    use HasFactory;
    
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
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'permissions' => 'array',
    ];
    
    /**
     * Get the user data.
     *
     * @return array|null
     */
    public function getUserData(): ?array
    {
        if (!$this->user) {
            return null;
        }
        
        return [
            'id' => $this->user->id,
            'name' => $this->user->name,
            'email' => $this->user->email
        ];
    }
    
    /**
     * Get the user that the staff belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the store that the staff belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
    
    /**
     * Check if the staff is assigned to the specified store.
     *
     * @param int $storeId
     * @return bool
     */
    public function isAssignedToStore(int $storeId): bool
    {
        return $this->store_id == $storeId;
    }
    
    /**
     * Check if the staff has permission for the specified action.
     *
     * @param string $action
     * @return bool
     */
    public function hasPermission(string $action): bool
    {
        if (empty($this->permissions)) {
            return false;
        }
        
        // Map controller methods to permission keys
        $permissionMap = [
            'index' => 'view',
            'show' => 'view',
            'create' => 'create',
            'store' => 'create',
            'edit' => 'edit',
            'update' => 'edit',
            'destroy' => 'delete',
            'updateStock' => 'edit',
            'updateStatus' => 'edit',
            'updatePaymentStatus' => 'edit',
        ];
        
        // Get the permission key for the action
        $permissionKey = $permissionMap[$action] ?? $action;
        
        // Check if the staff has the permission
        return in_array($permissionKey, $this->permissions);
    }
    
    /**
     * Available permission types
     */
    const PERMISSION_MANAGE_PRODUCTS = 'manage_products';
    const PERMISSION_MANAGE_ORDERS = 'manage_orders';
    const PERMISSION_MANAGE_CUSTOMERS = 'manage_customers';
    const PERMISSION_MANAGE_SETTINGS = 'manage_settings';
    const PERMISSION_VIEW_REPORTS = 'view_reports';
    
    /**
     * Get all available permissions.
     *
     * @return array
     */
    public static function getAvailablePermissions(): array
    {
        return [
            self::PERMISSION_MANAGE_PRODUCTS,
            self::PERMISSION_MANAGE_ORDERS,
            self::PERMISSION_MANAGE_CUSTOMERS,
            self::PERMISSION_MANAGE_SETTINGS,
            self::PERMISSION_VIEW_REPORTS,
        ];
    }
    
    /**
     * Check if the staff has a specific permission.
     *
     * @param string $permission
     * @return bool
     */
    public function checkStaffPermission(string $permission): bool
    {
        return in_array($permission, $this->permissions ?? []);
    }
}
