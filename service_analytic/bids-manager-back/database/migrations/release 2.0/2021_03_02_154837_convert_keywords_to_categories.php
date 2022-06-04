<?php

use App\Models\CampaignKeyword;
use App\Models\Keyword;
use App\Models\KeywordPopularity;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ConvertKeywordsToCategories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('keywords', function (Blueprint $table) {
            $table->dropIndex('keyword_name');
            $table->index('name', 'keyword_name');
        });

        $this->convertKeywords();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }

    /**
     * Конвертация ключевиков
     */
    protected function convertKeywords()
    {
        var_dump(date('Y-m-d H:i:s').': Start keywords converting');

        $campaignKeywords = CampaignKeyword::query()->with('campaignGood.good')->get();

        $campaignKeywords->each(function($campaignKeyword) {
            $this->convertKeyword($campaignKeyword);
        });

        var_dump(date('Y-m-d H:i:s').': Finish keywords converting');
    }

    /**
     * Конвертация ключевика
     *
     * @param CampaignKeyword $campaignKeyword
     */
    protected function convertKeyword($campaignKeyword)
    {
        if( !$campaignKeyword->campaignGood ) {
            echo $campaignKeyword->id;
        }
        $category = $campaignKeyword->campaignGood->good->category;
        $topCategory = $category ? $category->topCategory() : null;
        $topCategoryId = $topCategory ? $topCategory->id : null;

        $keyword = Keyword::find($campaignKeyword->keyword_id);
        if( !$keyword->category_id )
        {
            $keyword->category_id = $topCategoryId;
            $keyword->save();
        }
        elseif( $keyword->category_id != $topCategoryId )
        {
            $newKeyword = new Keyword();
            $newKeyword->name = $keyword->name;
            $newKeyword->category_id = $topCategoryId;
            $newKeyword->save();

            $campaignKeyword->keyword_id = $newKeyword->id;
            $campaignKeyword->save();

            $select = KeywordPopularity::query()
                                       ->where('keyword_id', $keyword->id)
                                       ->select(['created_at', 'updated_at'])
                                       ->selectRaw($newKeyword->id.' as keyword_id')
                                       ->addSelect('date', 'popularity');

            DB::table('keyword_popularities')
              ->insertUsing(['created_at', 'updated_at', 'keyword_id', 'date', 'popularity'], $select);
        }
    }
}
