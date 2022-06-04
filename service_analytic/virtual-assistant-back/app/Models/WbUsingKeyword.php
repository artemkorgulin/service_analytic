<?php

namespace App\Models;

use App\Services\ParserRabbitService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class WbUsingKeyword extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'wb_product_id',
        'name',
        'popularity',
    ];

    /**
     * Товар
     *
     * @return BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(WbProduct::class, 'wb_product_id');
    }

    public function save(array $options = [])
    {
        $wbProduct = WbProduct::where('id', $this->wb_product_id)->firstOrFail();

        $parserRabbitService = new ParserRabbitService();
        $data = [
            'wb_product_id' => $this->wb_product_id,
            'name' => $this->name,
            'sku' => $wbProduct->nmid,
            'user_id' => $wbProduct->user_id,
        ];
        $parserRabbitService->sendMessage(json_encode($data));

        return parent::save($options);
    }
}
