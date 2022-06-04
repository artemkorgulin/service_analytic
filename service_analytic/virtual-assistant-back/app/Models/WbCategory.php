<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WbCategory extends Model
{
    use HasFactory;

    public $fillable = [
        'name', 'parent', 'data', 'comment',
    ];
    public $casts = [
        'data' => 'object',
    ];

    protected $hidden = ['pivot'];

    protected $table = 'wb_categories';


    /**
     * Отношение с таблицей wb_directory_items
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function directoryItems()
    {
        return $this->belongsToMany(WbDirectoryItem::class, 'wb_category_directory_items', 'wb_category_id', 'wb_directory_item_id');
    }
}
