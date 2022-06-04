<?php

namespace App\Console\Commands;

use App\Models\WbCategory;
use App\Services\Wildberries\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class WbProductCategories extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wb:get-product-categories';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Получение типов товаров Wildberries и описание полей (обязательные и т.д.)';

    /**
     * Link type
     *
     * @var string
     */
    protected string $link = 'https://content-suppliers.wildberries.ru/ns/characteristics-configurator-api/content-configurator/api/v1/config/get/object/all?top=100000&lang=ru';

    protected string $linkProduct = 'https://content-suppliers.wildberries.ru/ns/characteristics-configurator-api/content-configurator/api/v1/config/get/object/translated?lang=ru&name=';


    protected string $wildberriesApiToken;

    protected string $wildberriesClientId;


    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->wildberriesApiToken = config('env.wildberries_api_token');
        $this->wildberriesClientId = config('env.wildberries_client_id');
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $respond = Http::get($this->link)->json();
        $types = $respond['data'];
        $WbClient = new Client($this->wildberriesClientId, $this->wildberriesApiToken);

        foreach ($types as $key => $type) {
            $type = trim($type);
            if ($type) {
                try {
                    $url = $this->linkProduct . $type;
                    $respondForType = Http::get($url)->json();
                    $this->info("Обновляю тип товара: $type");
                    $response = $WbClient->getObjectList(['pattern' => $type, 'lang' => 'ru']);
                    if (isset($respondForType['data']) && isset($response->data->{$type}->parent)) {
                        WbCategory::updateOrCreate(['name' => $type], [
                            'name' => $type,
                            'parent' => $response->data->{$type}->parent ?? null,
                            'data' => $respondForType['data']]);
                    }
                } catch (\Exception $exception) {
                    report($exception);
                    $this->error($exception->getMessage());
                }
            }
        }
    }

    /**
     *
     */
    private function getParentFromWildberries() {

    }
}
