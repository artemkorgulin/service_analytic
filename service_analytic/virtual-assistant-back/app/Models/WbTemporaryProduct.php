<?php

namespace App\Models;

use App\Services\UserService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WbTemporaryProduct extends Model
{
    use HasFactory, SoftDeletes;

    public $fillable = [
        'user_id', 'account_id', 'account_client_id', 'card_id', 'imt_id', 'card_user_id', 'supplier_id',
        'imt_supplier_id', 'title', 'brand', 'barcodes', 'nmid', 'sku', 'image', 'price', 'object', 'parent',
        'country_production', 'supplier_vendor_code', 'data', 'nomenclatures', 'url', 'quantity',
        'created_at', 'updated_at'
    ];

    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    protected $casts = [
        'data' => 'object',
        'nomenclatures' => 'object',
        'barcodes' => 'object',
    ];

    /**
     * Get products from current account
     *
     * @param $query
     * @return mixed
     */
    public function scopeCurrentAccount($query)
    {
        return $query->where('account_id', '=', UserService::getCurrentAccountWildberriesId());
    }

    /**
     * Get products from current user
     * get products from current user
     *
     * @param $query
     * @return mixed
     */
    public function scopeCurrentUser($query)
    {
        return $query->where('user_id', '=', UserService::getUserId());
    }

    /**
     * Quantity in stocks (summary)
     * quantity in stocks (summary)
     * @return int
     */
    public function getQuantity(): int
    {
        return WbBarcodeStock::whereIn('barcode', $this->barcodes)->sum('quantity') ?? 0;
    }
}
