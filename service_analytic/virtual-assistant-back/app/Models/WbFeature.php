<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WbFeature extends Model
{
    use HasFactory, SoftDeletes;

    const EXCLUDE_WB_FEATURES = ['Код ролика на YouTube', 'Аннотация', 'Изображения', '3D-изображение', 'Название', 'Наименование', 'Ключевые слова'];

    public $fillable = [
       'directory_id',
       'title',
    ];

    protected $hidden = [
        'deleted_at', 'created_at', 'updated_at'
    ];

    /**
     * Опции по характеристик
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function items() {
        return $this->belongsToMany(WbDirectoryItem::class, 'wb_feature_directory_items',
            'feature_id', 'item_id');
    }



}
