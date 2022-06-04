<?php

namespace App\Http\Controllers\Api\V1;

use App\Constants\Platform;
use App\Models\CampaignProduct;
use App\Models\CampaignStatus;
use App\Http\Controllers\Controller;
use App\Services\DatabaseService;
use App\Services\UserService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;

/**
 * Class ApiController
 *
 * @package App\Http\Controllers\Api
 */
class ApiController extends Controller
{
    /**
     * @var MessageBag $errors
     */
    protected $errors;

    /**
     * Create a new ApiController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->errors = new MessageBag;
    }

    /**
     * Запрос на участие товаров в РК
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function checkProductsCampaigns(Request $request)
    {
        $request->validate([
            'clients'   => 'required|array',
            'clients.*' => 'required|array',
            'clients.*.perfomance_client_id' => 'required|string',
            'clients.*.skus' => 'required|array',
            'clients.*.skus.*' => 'required|string',
        ]);

        $clients = $request->get('clients');

        $result = [];

        foreach ($clients as $client) {
            $client = (object)$client;
            $perfomanceClientId = $client->perfomance_client_id;
            $account = DatabaseService::getWabTableQuery('accounts')
                ->where('is_active', true)
                ->where('platform_client_id', '=', $perfomanceClientId)
                ->first();
            $skus = $client->skus;

            if (!$account) {
                $this->errors->add($perfomanceClientId, __('api.account_found_error', ['perfomance_client_id' => $perfomanceClientId]));
                continue;
            }

            $productCampaigns = CampaignProduct::query()
                ->leftJoin('campaign', 'campaign_id', '=', 'campaign.id')
                ->whereIn('sku', $skus)
                ->where('campaign.account_id', $account->id)
                ->where('campaign.code', CampaignStatus::ACTIVE)
                ->where(function (Builder $query) {
                    $query->where('start_date', '<=', Carbon::now())
                        ->orWhere('start_date', '=', '0000-00-00');
                })
                ->where(function (Builder $query) {
                    $query->where('end_date', '>=', Carbon::now())
                        ->orWhere('end_date', '=', '0000-00-00');
                })
                ->select('sku')
                ->selectRaw('1 as has_campaigns')
                ->pluck('has_campaigns', 'campaign_products.product_id');

            $result[$perfomanceClientId] = array_replace(
                array_fill_keys($skus, 0),
                $productCampaigns->toArray()
            );
        }

        return response()->json([
            'success' => $this->errors->count() == 0,
            'data'    => $result,
            'errors'  => $this->errors->getMessages()
        ]);
    }
}
