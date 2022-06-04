<?php

namespace App\Models;

use DateTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasRoles;

/**
 * Права на доступ к функционалу
 * @property int id
 * @property string description
 * @property DateTime created_at
 * @property DateTime updated_at
 * @property DateTime deleted_at
 */
class Permission extends Model
{
    use HasFactory, HasRoles;

    protected $hidden = ['created_at',  'updated_at'];

    protected $fillable = ['id', 'name', 'guard_name'];
}
