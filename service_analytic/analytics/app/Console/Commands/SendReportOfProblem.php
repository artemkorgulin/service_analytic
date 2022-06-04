<?php

namespace App\Console\Commands;

use App\Contracts\Repositories\V1\Action\HistoryRepositoryInterface;
use App\Repositories\V1\Action\OzHistoryProductRepository;
use App\Repositories\V1\Webapp\UserRepository;
use Carbon\Carbon;
use App\Repositories\V1\Action\WbHistoryProductRepository;
use App\Services\CallsForActionService;
use AnalyticPlatform\LaravelHelpers\Jobs\UsersNotification;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class SendReportOfProblem extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:problem';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Отправить отчет о проблеме';

    private CallsForActionService $callsForActionService;
    private string $date;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(CallsForActionService $callsForActionService)
    {
        $this->callsForActionService = $callsForActionService;
        $this->date = (Carbon::now())->subDay()->format('d-m-Y');
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $historyRepository = new WbHistoryProductRepository;
        $wbAccountList = $historyRepository->getWbAccountList();
        $this->info('WB');
        foreach ($wbAccountList as $account) {
            $products = $historyRepository->getProductsForWbAccount($account);
            $this->sendTroublesForAccount($historyRepository, $products, $account);
        }

        $historyRepository = new ozHistoryProductRepository;
        $ozAccountList = $historyRepository->getOzAccountList();
        $this->info('OZ');
        foreach ($ozAccountList as $account) {
            $products = $historyRepository->getProductsForOzAccount($account);
            $this->sendTroublesForAccount($historyRepository, $products, $account);
        }

        return 0;
    }

    /**
     * @param  HistoryRepositoryInterface  $historyRepository
     * @param  Collection  $products
     *
     * @param  int  $account
     */
    public function sendTroublesForAccount(
        HistoryRepositoryInterface $historyRepository,
        Collection $products,
        int $account
    ): void {
        $userId = $products->pluck('user_id')->first();

        try {
            $userEmail = (new UserRepository())->getUserById($userId)->email;
        } catch (\Exception $exception) {
            report($exception);
            return;
        }

        $this->info('accountId '.$account.' userId '.$userId.' email '.$userEmail);

        $data
            = $this->callsForActionService->getData($historyRepository,
            $products);
        $weightData = $this->callsForActionService->getWeightData($data);

        // Продукт с наибольшими проблемами на аккаунте
        $troublesProductForAccount
            = $this->callsForActionService->getTroublesProduct($weightData);

        //TODO  email for testing
        $userEmail = 's.zarubin@sellerexpert.ru';
        $url = env('FRONT_APP_URL').'/action-call?mp_id='.$account
            .'&product_id='.$troublesProductForAccount['id'];

        UsersNotification::dispatchSync(
            'card_product.product_problem',
            [['id' => $userId, 'lang' => 'ru', 'email' => $userEmail]],
            [
                'date'    => $this->date,
                'message' => 'Ссообщение о сигналах к действию', 'url' => $url
            ]
        );
    }
}
