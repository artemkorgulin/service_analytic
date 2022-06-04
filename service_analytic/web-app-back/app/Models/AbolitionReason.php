<?php

namespace App\Models;

use DateTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Причина отмены платежа
 * @property int id
 * @property string party инициатор отмены платежа
 * @property string reason причина отмены платежа
 * @property DateTime created_at
 * @property DateTime updated_at
 */
class AbolitionReason extends Model
{
    use HasFactory;
}
