<?php

namespace App\Console\Commands;

use App\Classes\Helper;
use App\Models\WbNomenclature;
use App\Models\WbProduct;
use App\Services\Wildberries\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class GetWildberriesProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wb:get-products
    {--user_id=: Получение продуктов из Wildberries для определенного пользователя }
    {--account_id=: Получение продуктов из Wildberries для определенной учетной записи }
    ';

    /**
     * Url для получения информации по пользователям
     *
     * @var string
     */
    protected $usersUrl = '/get-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Получение всех продуктов по Wildberries';

    protected $exportStep = 500;


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
        $token = config('auth.web_app_token');

        $url = env('WEB_APP_API_URL', '') . '/inner/vp-accounts/' . env('WB_PLATFORM_ID');

        try {
            $accounts = Http::withHeaders([
                'Authorization-Web-App' => $token,
                'Accept' => 'application/json; charset=utf-8'
            ])->get($url)->json();
        } catch (\Exception $exception) {
            report($exception);
            $this->error($exception->getMessage());
        }

        if (!isset($accounts) || !$accounts) {
            $this->error('Не смогли получить аккаунты Wildberries завершаю!');
            return;
        }

        if (isset($accounts['error'])) {
            $this->error(json_encode($accounts['error']));
            return;
        }

        foreach ($accounts as $account) {
            $client = new Client($account['platform_client_id'], $account['platform_api_key']);

            $this->info('Start get and sync information about Wildberries nomenclatures');
            $nomenclatures = collect($client->getInfo());

            $bar = $this->output->createProgressBar(count($nomenclatures));
            $bar->start();

            foreach ($nomenclatures->chunk($this->exportStep) as $nomenclaturesPart) {
                $records = [];
                foreach ($nomenclaturesPart as $nomenclature) {

                    if (!in_array($nomenclature->nmId, array_column($records, 'nm_id'))) {

                        $records[] = [
                            'user_id' => $account['user_id'] ?? null,
                            'account_id' => $account['id'],
                            'nm_id' => $nomenclature->nmId,
                            'price' => $nomenclature->price,
                            'discount' => $nomenclature->discount,
                            'promocode' => $nomenclature->promoCode,
                        ];
                    }

                    $bar->advance();
                }
                WbNomenclature::upsert($records, ['account_id', 'nm_id'], ['user_id', 'price', 'discount', 'promocode']);
            }


            $bar->finish();
            $this->info("\n\n");

            $this->info('Начните получать и синхронизировать информацию о картах Wildberries');
            $page = 0;
            $cards = collect($client->getCardList($this->exportStep, $page * $this->exportStep));

            do {
                $records = [];
                foreach ($cards as $card) {
                    if (isset($card['id'])) {
                        if (!in_array($card['id'], array_column($records, 'card_id'))) {
                            $records[] = [
                                'account_id' => $account['id'],
                                'user_id' => $account['user_id'] ?? null,
                                'card_id' => $card['id'],
                                'imt_id' => $card['imtId'],
                                'card_user_id' => $card['userId'],
                                'supplier_id' => $card['supplierId'],
                                'imt_supplier_id' => $card['imtSupplierId'],
                                'title' => Helper::wbCardGetTitle($card),
                                'object' => $card['object'],
                                'parent' => $card['parent'],
                                'country_production' => $card['countryProduction'],
                                'supplier_vendor_code' => $card['supplierVendorCode'],
                                'data' => json_encode($card),
                            ];
                        }
                    }
                }

                try {
                    WbProduct::upsert($records,
                        ['account_id', 'card_id', 'imt_id'],
                        ['user_id', 'card_user_id', 'supplier_id', 'imt_supplier_id', 'object', 'parent',
                            'country_production', 'supplier_vendor_code', 'data'
                        ]);
                } catch (\Exception $exception) {
                    report($exception);
                    $this->error($exception->getMessage());
                }

                $page++;
                $this->info("Sync page {$page}");
                try {
                    $cards = collect($client->getCardList($this->exportStep, $page * $this->exportStep));
                } catch (\Exception $exception) {
                    report($exception);
                    $this->error($exception->getMessage());
                    $cards = coollect([]);
                }
            } while (count($cards) > 0);

            $this->info("\n\n");

            $this->info("Соединяю карточки товара и номенклатуры Wildberries");

            $countCards = WbProduct::where('account_id', $account['id'])->count();

            $bar = $this->output->createProgressBar($countCards);

            $bar->start();

            WbProduct::where('account_id', $account['id'])->chunk($this->exportStep, function ($cards) use (&$bar, $account) {
                foreach ($cards as $card) {
                    $nomenclatureNmIds = Helper::wbCardGetNmIds($card->data);
                    $nomenclatureIds = WbNomenclature::where('account_id', $account['id'])
                        ->whereIn('nm_id', $nomenclatureNmIds)
                        ->pluck('id')->toArray();
                    $card->nomenclatures()->sync($nomenclatureIds);
                    $bar->advance();
                }
            });

            $bar->finish();

            $this->info("\n\n");
        }

        return 0;
    }
}
