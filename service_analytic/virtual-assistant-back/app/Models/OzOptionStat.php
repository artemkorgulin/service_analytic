<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class OzOptionStat extends Model
{
    use HasFactory;

    public $fillable = [
        'name', 'filename', 'comment', 'created_at', 'updated_at',
    ];
    protected $table = 'oz_option_stats';

    /**
     * Update data in in summariest
     */
    public function createSummary()
    {
        // Delete all old summaries
        $this->purifyOldSummary();
        // And create new
        foreach ($this->items()->select(['key_request', DB::raw('SUM(popularity) AS summary_popularity')])->
        groupBy('key_request')->get() as $item) {
            OzOptionStatSummary::updateOrCreate(
                ['key_request' => $item->key_request],
                ['summary_popularity' => $item->summary_popularity ?? 0]
            );
        }
    }

    /**
     * Relation for summaries
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function summaries(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(OzOptionStatSummary::class, 'option_stat_id');
    }

    /**
     * Relation for items
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function items(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(OzOptionStatItem::class, 'option_stat_id');
    }

    /**
     * Purify old summaries
     * @return void
     */
    public function purifyOldSummary()
    {
        DB::delete(<<<SQL
            DELETE oz_option_stat_summaries1
            FROM oz_option_stat_summaries AS oz_option_stat_summaries1,
                 oz_option_stat_summaries AS oz_option_stat_summaries2
            WHERE oz_option_stat_summaries1.id < oz_option_stat_summaries2.id
                AND oz_option_stat_summaries1.key_request = oz_option_stat_summaries2.key_request
        SQL
        );
    }


}
