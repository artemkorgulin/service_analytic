<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Class StopWord
 *
 * @package App\Models
 *
 * @property string $name
 */
class StopWord extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
    ];

    /**
     * Товары в РК
     *
     * @return BelongsToMany
     */
    public function campaignProducts()
    {
        return $this->belongsToMany(CampaignProduct::class);
    }

    /**
     * Группы
     *
     * @return BelongsToMany
     */
    public function groups()
    {
        return $this->belongsToMany(Group::class);
    }

    public static function saveStopWord(array $request)
    {
        if (!array_key_exists('stop_word_name', $request)) {
            return false;
        }

        $stopWord = self::where('name', '=', $request['stop_word_name'])->first();

        if (!empty($stopWord)) {
            return $stopWord;
        }

        $stopWord = new StopWord();
        $stopWord->name = $request['stop_word_name'];
        $stopWord->save();

        return $stopWord;
    }
}
