<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\ProductDashboard
 *
 * @property int $id
 * @property string $dashboard_type Тип дашборда
 * @property array $good_segment Данные сегмента good для дашборда
 * @property array $normal_segment Данные сегмента normal для дашборда
 * @property array $bad_segment Данные сегмента bad для дашборда
 * @property int $marketplace_platform_id Связь с таблицей webapp.platform
 * @property int $user_id Связь с таблицей webapp.user
 * @property int $account_id Связь с таблицей webapp.account
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class ProductDashboard extends Model
{
    use HasFactory;

    protected $table = 'product_dashboard';

    protected $fillable = [
        'dashboard_type',
        'good_segment',
        'normal_segment',
        'bad_segment',
        'marketplace_platform_id',
        'user_id',
        'account_id',
    ];

    protected $casts = [
        'good_segment' => 'array',
        'normal_segment' => 'array',
        'bad_segment' => 'array',
    ];

}
