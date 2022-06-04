<?php

namespace App\Models;

use Carbon\Traits\Date;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


/**
 * Class ProductPositionHistoryGraph
 * Большинство полей дублирует поля ProductPositionHistory
 * Нужна только для хранения позиций по все подкатегориям, в отличие
 * от ProductPositionHistory в которой хранится только позиция в самой глубокой категории
 *
 * @property integer $id
 * @property integer $product_id
 * @property integer $position
 * @property string $category
 * @property string $date
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\OzProduct $product
 * @method static \Illuminate\Database\Eloquent\Builder|ProductPositionHistoryGraph newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductPositionHistoryGraph newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductPositionHistoryGraph query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductPositionHistoryGraph whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductPositionHistoryGraph whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductPositionHistoryGraph whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductPositionHistoryGraph whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductPositionHistoryGraph wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductPositionHistoryGraph whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductPositionHistoryGraph whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ProductPositionHistoryGraph extends Model
{
    protected $table = 'oz_product_positions_history_graph';

    /**
     * Товар
     *
     * @return BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(OzProduct::class, 'product_id');
    }
}
