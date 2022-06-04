<?php

use App\Models\CampaignProduct;
use App\Models\CampaignKeyword;
use App\Models\CampaignStopWord;
use App\Models\Group;
use App\Models\Status;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Seeder;

class StatusDeletedSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Group::query()
            ->whereHas('campaign', function(Builder $query) {
                $query->whereNotNull('ozon_id');
            })
            ->update(['status_id' => Status::DELETED]);

        CampaignProduct::query()
            ->whereHas('campaign', function(Builder $query) {
                $query->whereNotNull('ozon_id');
            })
            ->update(['status_id' => Status::DELETED]);

        CampaignKeyword::query()
            ->whereHas('campaignGood', function(Builder $query) {
                $query->whereHas('campaign', function(Builder $query) {
                    $query->whereNotNull('ozon_id');
                });
            })
            ->update(['status_id' => Status::DELETED]);

        CampaignStopWord::query()
            ->whereHas('campaignGood', function(Builder $query) {
                $query->whereHas('campaign', function(Builder $query) {
                    $query->whereNotNull('ozon_id');
                });
            })
            ->update(['status_id' => Status::DELETED]);
    }
}
