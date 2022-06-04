<?php

namespace App\Models;

use DateTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Права для тарифа
 * @property int id
 * @property int tariff_id
 * @property int permission_id
 * @property DateTime created_at
 * @property DateTime updated_at
 */
class TariffPermission extends Model
{
    use HasFactory;
}
