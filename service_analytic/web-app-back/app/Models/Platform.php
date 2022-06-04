<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Platform
 * Платформы для интеграции со сторонними сервисами
 *
 * @package App\Models
 */
class Platform extends Model
{
    protected $fillable = ['title', 'description', 'api_url', 'settings'];

    protected $hidden = ['created_at', 'updated_at'];

    protected $casts = ['settings' => 'json'];

    /********************* Relations section ***********************/

    /**
     * Get accounts
     *
     * @return HasMany
     */
    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class, 'platform_id', 'id');
    }
}
