<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
//use Illuminate\Database\Eloquent\SoftDeletes;

class WbDirectoryItem extends Model
{
//    use HasFactory, SoftDeletes;
    use HasFactory;

    public $fillable = [
        'wb_directory_id', 'title', 'translation', 'popularity', 'has_in_ozon', 'data',
    ];

    public $hidden = ['created_at', 'updated_at', 'deleted_at', 'pivot'];

    public $casts = [
        'data' => 'object',
    ];

    /**
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function wildberries() {
        return $this->hasOne(Option::class, 'title', 'value');
    }
}
