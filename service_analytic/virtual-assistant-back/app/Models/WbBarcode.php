<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WbBarcode extends Model
{
    use HasFactory;

    protected $primaryKey = 'barcode';

    public $fillable = [
        'barcode', 'subject', 'brand', 'name', 'size', 'barcodes', 'article', 'used', 'quantity', 'created_at',
        'updated_at',
    ];

    protected $casts = [
        'barcodes' => 'array',
    ];

    public $incrementing = false;

    protected $keyType = 'bigInteger';

    /**
     * Return unused barcodes
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUnused($query)
    {
        return $query->where('used', false);
    }
}
