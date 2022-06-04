<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasPermissions;

class Role extends Model
{
    use HasFactory, HasPermissions;

    protected $hidden = ['created_at', 'updated_at'];

    protected $fillable = ['id', 'name', 'description', 'guard_name', 'table_model_id'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function rolePermissions()
    {
        return $this->hasManyThrough(
            Permission::class,
            RoleHasPermission::class,
            'role_id',
            'id',
            'id',
            'permission_id'
        );
    }
}
