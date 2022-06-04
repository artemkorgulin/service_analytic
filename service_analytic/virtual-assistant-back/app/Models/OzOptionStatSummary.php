<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class OzOptionStatSummary extends Model
{
    use HasFactory;

    protected $table = 'oz_option_stat_summaries';

    public $fillable = [
        'key_request', 'summary_popularity', 'created_at', 'updated_at',
    ];

    /**
     * Update popularity option in all marketplaces
     * @return void
     */
    public static function updateAllOptionPopularity()
    {
        self::updateOzonOptionPopularity();
        self::updateWildberriesOptionPopularity();
    }

    /**
     * Update Ozon product option popularity warning popularity
     * @return void
     */
    public static function updateOzonOptionPopularity()
    {
        DB::update(<<<SQL
            UPDATE oz_feature_options
            INNER JOIN oz_option_stat_summaries
                ON oz_option_stat_summaries.key_request = oz_feature_options.value
            SET oz_feature_options.popularity = oz_option_stat_summaries.summary_popularity
            WHERE oz_option_stat_summaries.summary_popularity>0;
        SQL
        );
    }

    /**
     * Update Wildberries product option popularity warning popularity
     * @return void
     */
    public static function updateWildberriesOptionPopularity()
    {
        DB::update(<<<SQL
            UPDATE wb_directory_items
            INNER JOIN oz_option_stat_summaries
                ON oz_option_stat_summaries.key_request = wb_directory_items.title
            SET wb_directory_items.popularity = oz_option_stat_summaries.summary_popularity,
                wb_directory_items.has_in_ozon = 1
            WHERE oz_option_stat_summaries.summary_popularity>0;
        SQL
        );
    }
}
