<?php

use App\Models\CampaignProduct;
use App\Models\CampaignKeyword;
use App\Models\CampaignStatus;
use App\Models\CampaignStopWord;
use App\Models\Keyword;
use App\Helpers\GoodHelper;
use App\Services\Loaders\OzonCampaignProductsLoader;
use App\Services\Loaders\OzonCampaignsLoader;
use App\Services\Loaders\OzonCategoriesLoader;
use App\Models\StopWord;
use Illuminate\Database\Migrations\Migration;

class ConvertData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->setCampaignStatuses();
        $this->setGoodStatuses();

        (new OzonCampaignsLoader)->load();
        (new OzonCategoriesLoader)->load();

        $this->convertGoods();
        $this->convertKeywords();
        $this->convertStopWords();

        (new OzonCampaignProductsLoader())->load();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {}

    /**
     * Конвертация статусов кампаний
     */
    protected function setCampaignStatuses()
    {
        CampaignStatus::create(['code' => 'RUNNING', 'ozon_code' => 'CAMPAIGN_STATE_RUNNING', 'name' => 'Активна']);
        CampaignStatus::create(['code' => 'INACTIVE', 'ozon_code' => 'CAMPAIGN_STATE_INACTIVE', 'name' => 'Неактивна']);
        CampaignStatus::create(['code' => 'PLANNED', 'ozon_code' => 'CAMPAIGN_STATE_PLANNED', 'name' => 'Запланирована']);
        CampaignStatus::create(['code' => 'STOPPED', 'ozon_code' => 'CAMPAIGN_STATE_STOPPED', 'name' => 'Приостановлена']);
        CampaignStatus::create(['code' => 'DRAFT', 'ozon_code' => '', 'name' => 'Черновик']);
        CampaignStatus::create(['code' => 'ARCHIVED', 'ozon_code' => 'CAMPAIGN_STATE_ARCHIVED', 'name' => 'Архив']);
    }

    /**
     * Конвертация статусов товаров
     */
    protected function setGoodStatuses()
    {
    }

    /**
     * Конвертация товаров
     */
    protected function convertGoods()
    {
        var_dump(date('Y-m-d H:i:s').': Start goods converting');
        do {
            $campaignGoods = CampaignProduct::query()->whereNull('good_id')->limit(1000)->get();
            var_dump(count($campaignGoods));
            $campaignGoods->each(function($campaignGood) {
                $this->convertGood($campaignGood);
            });
            sleep(60);
        }
        while( count($campaignGoods) > 0 );
        var_dump(date('Y-m-d H:i:s').': Finish goods converting');
    }

    /**
     * Конвертация товара
     *
     * @param  CampaignProduct  $campaignGood
     *
     * @return bool
     */
    protected function convertGood($campaignGood)
    {
        $good = GoodHelper::getGoodBySku($campaignGood->campaign->account, $campaignGood->sku);
        if( !$good ) return false;

        $campaignGood->good_id = $good->id;
        return $campaignGood->save();
    }

    /**
     * Конвертация ключевиков
     */
    protected function convertKeywords()
    {
        var_dump(date('Y-m-d H:i:s').': Start keywords converting');

        $campaignKeywords = CampaignKeyword::query()
                                           ->whereNull('keyword_id')
                                           ->orWhereNull('group_id')
                                           ->with('campaignGood')
                                           ->get();
        var_dump(count($campaignKeywords));

        $campaignKeywords->each(function($campaignKeyword) {
            $this->convertKeyword($campaignKeyword);
        });

        var_dump(date('Y-m-d H:i:s').': Finish keywords converting');
    }

    /**
     * Конвертация ключевика
     *
     * @param CampaignKeyword $campaignKeyword
     *
     * @return bool
     */
    protected function convertKeyword($campaignKeyword)
    {
        $keyword = Keyword::query()->where('name', $campaignKeyword->name)->first();
        if( !$keyword ) {
            $keyword = new Keyword();
            $keyword->name = $campaignKeyword->name;
            $keyword->save();
        }

        $campaignKeyword->keyword_id = $keyword->id;
        $campaignKeyword->group_id = $campaignKeyword->campaignGood->group_id;
        return $campaignKeyword->save();
    }

    /**
     * Конвертация минус-слов
     */
    protected function convertStopWords()
    {
        var_dump(date('Y-m-d H:i:s').': Start stop words converting');

        $campaignStopWords = CampaignStopWord::query()
                                             ->whereNull('stop_word_id')
                                             ->orWhereNull('group_id')
                                             ->with('campaignGood')
                                             ->get();
        var_dump(count($campaignStopWords));

        $campaignStopWords->each(function($campaignStopWord) {
            $this->convertStopWord($campaignStopWord);
        });

        var_dump(date('Y-m-d H:i:s').': Finish stop words converting');
    }

    /**
     * Конвертация минус-слова
     *
     * @param CampaignStopWord $campaignStopWord
     *
     * @return bool
     */
    protected function convertStopWord($campaignStopWord)
    {
        // Ищем минус-слово
        $stopWord = StopWord::query()->where('name', $campaignStopWord->name)->first();

        if( !$stopWord ) {
            $stopWord = new StopWord();
            $stopWord->name = $campaignStopWord->name;
            $stopWord->save();
        }

        $campaignStopWord->stop_word_id = $stopWord->id;
        $campaignStopWord->group_id = $campaignStopWord->campaignGood->group_id;
        return $campaignStopWord->save();
    }
}
