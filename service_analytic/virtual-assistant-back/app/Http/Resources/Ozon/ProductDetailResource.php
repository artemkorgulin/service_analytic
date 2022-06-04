<?php

namespace App\Http\Resources\Ozon;

use App\DTO\Ozon\ProductDetailDTO;
use App\Services\V2\OptimisationHistoryService;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductDetailResource extends JsonResource
{
    public const REDEFINED_PARAMS_PRODUCT = [
        8292 => ['is_required' => 0],
        10289 => ['is_required' => 0],
    ];

    /**
     * @param array $characteristics
     * @return array
     */
    private function redefinedParamsProduct(array $characteristics): array
    {
        $characteristics = array_column($characteristics, null, 'id');
        foreach (self::REDEFINED_PARAMS_PRODUCT as $id => $params) {
            if (!array_key_exists($id, $characteristics)) {
                continue;
            }

            foreach ($params as $param => $value) {
                $characteristics[$id][$param] = $value;
            }
        }

        return $characteristics;
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        /** @var $this ProductDetailDTO */
        $product = $this->product;
        $analyticsData = optional($this->analyticsData)->first();
        $escrowService = $this->escrowService;

        $webCategoryHistory = $product->webCategory
            ? $product->webCategory->history()->latest()->first()
            : null;
        $webCategoryHistoryCreatedAt = $webCategoryHistory->created_at ?? null;

        $history = $product->changeHistory->sortByDesc('created_at')->first();
        $priceError = ($history && $history->priceChanges->count())
            ? $history->priceChanges->sortByDesc('created_at')->first()->errors
            : null;

        $characteristics = (object)$this->redefinedParamsProduct($product->characteristics);
        $recommendedCharacteristics = $this->redefinedParamsProduct(optional($product->category)->characteristics);

        return [
            'id' => $product->id,
            'external_id' => $product->external_id,
            'name' => $product->actual_name,
            'sku' => [
                'fbo' => $product->sku_fbo,
                'fbs' => $product->sku_fbs,
            ],
            'status_id' => $product->status_id,
            'category_id' => $product->category_id,
            'barcode' => $product->barcode,
            'bread_crumbs' => $product->category->breadCrumbs ?? '',
            'web_category_name' => $product->webCategory && $product->webCategory->name
                ? $product->webCategory->name
                : 'Информация о категории товара недоступна',
            'rating' => doubleval(number_format($product->rating, 1, '.', '')),
            'reviews' => $product->count_reviews ?: 0,
            'photos' => $product->count_photos ?: 0,
            'photo_url' => $product->photo_url ?? $product->images[0] ?? '',
            'descriptions' => $product->descriptions,
            'images' => $product->images ?? ($product->photo_url ? [$product->photo_url] : []),
            'images3d' => $product->images360 ?? [],
            'images360' => $product->images360 ?? [],
            'youtubecodes' => $product->getYoutubecodeAttribute(),
            'url' => $product->url,
            'price' => (float)$product->price ?? 0.00,
            'old_price' => (float)$product->old_price ?? 0.00,
            'premium_price' => (float)$product->premium_price ?? 0.00,
            'buybox_price' => (float)$product->buybox_price ?? 0.00,
            'marketing_price' => (float)$product->marketing_price ?? 0.00,
            'discount' => (float)$product->discount,
            'vat' => (float)$product->vat,
            'volume_weight' => (float)$product->volume_weight,
            'min_ozon_price' => (float)$product->min_ozon_price ?? 0.00,
            'status' => $product->status->name,
            'updated' => $product->updated_at,
            'characteristics' => $characteristics,
            'error_characteristics' => $product->errorCharacteristics,
            'price_recommendation_higher' => $product->priceRecommendationHigher,
            'recomended_characteristics' => $recommendedCharacteristics,
            'triggers' => [
                'position' => $product->positionTrigger,
                'photos' => $product->minPhotosTrigger,
                'reviews' => $product->minReviewsTrigger,
                'removedFromSale' => $product->hasRemovedFromSaleTriggers,
            ],
            'dimension_unit' => $product->dimension_unit,
            'depth' => (float)$product->depth,
            'height' => (float)$product->height,
            'width' => (float)$product->width,
            'weight_unit' => $product->weight_unit,
            'weight' => (float)$product->weight,
            'positions' => $product->positions,
            'position' => $product->getHighestPosition(),
            'recomendations' => $product->recomendations,
            'card_updated' => $product->card_updated,
            'is_test' => $product->is_test,
            'characteristics_updated_at' => $product->characteristics_updated_at,
            'characteristics_updated' => $product->characteristics_updated,
            'position_updated' => $product->position_updated,
            'mpstat_updated_at' => $product->mpstat_updated_at,
            'web_category_parsed_at' => $webCategoryHistoryCreatedAt,
            'price_error' => $priceError,
            'show_success_alert' => $product->show_success_alert,
            'optimization' => $product->optimization ? intval($product->optimization) : 0,
            'content_optimization' => $product->content_optimization,
            'search_optimization' => $product->search_optimization,
            'visibility_optimization' => $product->visibility_optimization,
            'colorSample' => $product->color_image,
            'listGoodsAdds' => $this->listGoodsAdds,
            'metrics' => [
                'hits_view' => (int)$analyticsData->avg_hits_view,
                'conv_tocart_pdp' => (int)$analyticsData->avg_conv_tocart_pdp,
                'conv_tocart' => (int)$analyticsData->avg_conv_tocart,
                'revenue' => (int)$analyticsData->sum_revenue,
                'ordered_units' => (int)$analyticsData->sum_ordered_units,
                'position_category' => (int)$analyticsData->avg_position_category,
            ],
            'optimisationHistory' => OptimisationHistoryService::productHistory($product),
            'escrow' => [
                'remainLimit' => $escrowService->getRemainEscrowLimit(),
                'totalLimit' => $escrowService->getEscrowLimit(),
                'hashes' => $product->escrowHash()->get(),
                'certificates' => $product->certificate()->get(),
                'escrowPercent' => $escrowService->getEscrowPercentForOzon($product),
                'lastEscrowPercent' => $escrowService->getEscrowLastPercent(),
                'history' => $escrowService->productEscrowHistory($product),
            ],
            'created_at' => $product->created_at->format('Y-m-d H:i:s'),
            'quantity' => [
                'fbo' => $product->quantity_fbo,
                'fbs' => $product->quantity_fbs,
            ]
        ];
    }
}
