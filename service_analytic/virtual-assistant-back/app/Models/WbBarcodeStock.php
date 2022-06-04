<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WbBarcodeStock extends Model
{
    use HasFactory;

    public $fillable = [
        'barcode', 'check_date', 'quantity', 'warehouse_name', 'warehouse_id',
    ];

    /**
     * primaryKey
     *
     * @var integer
     * @access protected
     */
    protected $primaryKey = null;
    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    public $timestamps = false;
}
