<?php

namespace App\Models;

use App\Services\UserService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class OzTemporaryProduct
 * Временный продукт
 *
 * @property integer $id
 * @property string $sku_fbo
 * @property string $sku_fbs
 * @property integer $quantity_fbo
 * @property integer $quantity_fbs
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $name
 * @property-read string $url
 * @method static \Illuminate\Database\Eloquent\Builder|OzTemporaryProduct newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OzTemporaryProduct newQuery()
 * @method static \Illuminate\Database\Query\Builder|OzTemporaryProduct onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|OzTemporaryProduct query()
 * @method static \Illuminate\Database\Eloquent\Builder|OzTemporaryProduct whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OzTemporaryProduct whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OzTemporaryProduct whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OzTemporaryProduct whereSku($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OzTemporaryProduct whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|OzTemporaryProduct withTrashed()
 * @method static \Illuminate\Database\Query\Builder|OzTemporaryProduct withoutTrashed()
 * @mixin \Eloquent
 */
class OzTemporaryProduct extends Model
{
    use softDeletes;

    protected $table = 'oz_temporary_products';

    protected $fillable = [
        'sku_fbo', 'sku_fbs', 'user_id', 'account_id', 'account_client_id', 'title', 'brand', 'barcode', 'offer_id',
        'category_id',
        'category', 'image', 'images', 'descriptions', 'vat', 'price', 'min_ozon_price', 'buybox_price',
        'premium_price', 'recommended_price', 'old_price', 'count_reviews', 'rating', 'optimization', 'data',
        'external_id', 'quantity_fbo', 'quantity_fbs'
    ];

    protected $casts = [
        'images' => 'object',
        'descriptions' => 'object',
        'data' => 'object',
    ];

    protected $appends = ['url'];

    // ------------------ Scopes section ----------------------- //

    /**
     * Get products from current user
     *
     * @param $query
     * @return mixed
     */
    public function scopeCurrentUser($query): mixed
    {
        return $query->where('user_id', '=', UserService::getUserId());
    }


    /**
     * Get products from current user
     *
     * @param $query
     * @return mixed
     */
    public function scopeCurrentAccount($query): mixed
    {
        return $query->where('account_id', '=', UserService::getCurrentAccountOzonId());
    }

    /**
     * Search by SKU
     *
     * @param $query
     * @param $searchString
     * @return mixed
     */
    public function scopeSearchBySkuFbo($query, $searchString): mixed
    {
        return $query->where('sku_fbo', 'LIKE', "%".$searchString."%");
    }


    // ------------------ Getters section ---------------------- //

    /**
     * Link to ozon product page
     *
     * @return string|null
     */
    public function getUrlAttribute(): ?string
    {
        return !empty($this->url_id) ? 'https://www.ozon.ru/context/detail/id/'.$this->url_id : null;
    }
}
