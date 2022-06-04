<?php

namespace App\Console\Commands;

use App\Models\OzCategory;
use Illuminate\Console\Command;
use App\Models\OzProduct;
use App\Repositories\RootQueryRepository;
use Illuminate\Support\Facades\DB;
use App\Models\PlatfomSemantic;

class OzonSemantic extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ozon:semantic {id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $timeStart = microtime(true);
        $this->info('Семв=антическое ядро');

        $productId = $this->argument('id') ?? null;
        if ($productId)
            $sqlAdd = ' and  op.id = '.$productId;
        else
            $sqlAdd = '';

        $results  = DB::select("
            select product_id, category_id, category_name,  oz_products_features_value, oz_features_name
            from (
                select
                        oc.id as 'category_id', op.id as 'product_id',  oc.name as 'category_name'
                        ,opf.value as 'oz_products_features_value' ,oz_features.name as 'oz_features_name'
                from oz_categories as oc
                     join  oz_products op on op.category_id = oc.id
                     join oz_products_features opf on op.id = opf.product_id
                     join oz_features  on opf.feature_id = oz_features.id
                where  op.id is not null  " . $sqlAdd .
                " and (oz_features .name  like 'Тип' OR  oz_features .name like 'Бренд'  )
            )    as tbl where 1 = 1
        ");

        foreach ($results as $result){
            $res =  DB::table('oz_option_stat_items')
                ->where('search_response', 'like', "$result->oz_products_features_value%")
                ->get()->toArray();
            if (is_array($res)){
                foreach ($res as $data ){
                    $plSemantic = new PlatfomSemantic();
                    $plSemantic->product_id = $result->product_id;
                    $plSemantic->category_id = $result->category_id;
                    $plSemantic->platform = 'Ozon';
                    $plSemantic->key_request = $data->key_request;
                    $plSemantic->search_responce = $data->search_response;
                    $plSemantic->name_ch = $result->oz_features_name;
                    $plSemantic->popularity = $data->popularity;
                    $plSemantic->conversion = $data->conversion;
                    $plSemantic->save();
                }
            }else{
                $plSemantic = new PlatfomSemantic();
                $plSemantic->product_id = $result->product_id;
                $plSemantic->category_id = $result->category_id;
                $plSemantic->platform = 'Ozon';
                $plSemantic->key_request = $res->key_request;
                $plSemantic->search_responce = $res->search_response;
                $plSemantic->name_ch = $result->oz_features_name;
                $plSemantic->popularity = $res->popularity;
                $plSemantic->conversion = $res->conversion;
                $plSemantic->save();
            }
        }

        $timeEnd = microtime(true);
        $time = $timeEnd - $timeStart;
        $this->info('Семантическое ядро сформировано ' . $time );

        return  0;
    }
}
