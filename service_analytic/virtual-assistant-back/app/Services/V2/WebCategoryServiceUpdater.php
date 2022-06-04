<?php

namespace App\Services\V2;

use App\Models\OzProduct;
use App\Models\TriggerChangePhotos;
use App\Models\TriggerChangeReviews;
use App\Models\WebCategory;
use App\Models\WebCategoryHistory;
use AnalyticPlatform\LaravelHelpers\Helpers\ExceptionHandlerHelper;
use Exception;
use Illuminate\Support\Facades\Log;

/**
 * Class WebCategoryServiceUpdater
 * Сервис для выгрузки результатов парсинга с ftp
 * @package App\Services\V2
 */
class WebCategoryServiceUpdater
{
    /**
     * Метод для обновления топ-36 в веб категориях
     */
    public function updateTop36(): void
    {
        $dates = [];
        $result = [];

        $ftp = new FtpService();
        foreach ($ftp->getTop36Files() as $file_path) {
            try {
                $parse_result = $ftp->parseTop36File($file_path);
                if ($parse_result) {
                    $date = array_keys($parse_result)[0];
                    $dates[] = $date;
                    $result[$date] = $parse_result[$date];
                }
            } catch (\Exception $exception) {
                report($exception);
            }
        }

        usort($dates, function ($date1, $date2) {
            return $date1 <=> $date2;
        });

        foreach ($dates as $date) {
            $parse_result = $result[$date];
            foreach ($parse_result as $web_category_id => $web_category_data) {
                try {
                    /** @var WebCategoryHistory $previous_history @var WebCategory $web_category */
                    $web_category = WebCategory::query()->where('external_id', $web_category_id)->first();
                    if (!$web_category) {
                        continue;
                    }

                    $previous_history = WebCategoryHistory::where('web_category_id', $web_category->id)->get()->last();

                    $history = new WebCategoryHistory();
                    $history->web_category_id = $web_category->id;
                    $history->min_price = $web_category_data['min_price'] ?? 0;
                    $history->max_price = $web_category_data['max_price'] ?? 0;
                    $history->min_reviews = $web_category_data['min_reviews'] ?? 0;
                    $history->max_reviews = $web_category_data['max_reviews'] ?? 0;
                    $history->min_rating = str_replace(',', '.', $web_category_data['min_rating']) ?? 0;
                    $history->min_photos = $web_category_data['min_photo'] ?? 0;
                    $history->average_price = $web_category_data['average_price'] ?? 0;
                    $history->created_at = $web_category_data['created_at'];
                    $history->save();

                    if (!$previous_history || $previous_history->min_photos != $history->min_photos) {
                        $triggers = [];
                        foreach ($web_category->products->pluck('id') as $product_id) {
                            $triggers = [
                                'web_category_id' => $web_category->id,
                                'min_photos' => $history->min_photos,
                                'product_id' => $product_id,
                                'created_at' => NOW(),
                                'updated_at' => NOW(),
                            ];
                        }

                        TriggerChangePhotos::insert($triggers);
                    }

                    if (!$previous_history || $previous_history->min_reviews != $history->min_reviews) {
                        $triggers = [];
                        foreach ($web_category->products->pluck('id') as $product_id) {
                            $triggers = [
                                'web_category_id' => $web_category->id,
                                'min_reviews' => $history->min_reviews,
                                'product_id' => $product_id,
                                'created_at' => NOW(),
                                'updated_at' => NOW(),
                            ];
                        }

                        TriggerChangeReviews::insert($triggers);
                    }
                } catch (\Exception $exception) {
                    report($exception);
                }

            }
        }
    }

    /**
     * Метод для обновления топ-36 в веб категориях
     */
    public function updateSku(): void
    {
        $ftp = new FtpService();

        foreach ($ftp->getSkuFiles() as $file_path) {

            print "Обрабатываю файл {$file_path}\n";

            $parsed_data = $ftp->parseSkuFile($file_path);
            if (!$parsed_data) {
                continue;
            }
            foreach ($parsed_data as $web_category_id => $data) {
                /** @var WebCategory $webCategory */
                $webCategory = WebCategory::updateOrCreate(
                    [
                        'external_id' => $web_category_id,
                    ],
                    [
                        'full_name' => $data['full_name'],
                        'name' => $data['name'],
                    ]
                );

                foreach ($data['products'] as $dataProduct) {
                    /**
                     * @var OzProduct $product
                     */
                    $explode = explode('/', $dataProduct['url']);
                    if ($explode) {
                        $product = OzProduct::where('sku_fbo', $explode[count($explode) - 1])->first();
                        if ($product) {
                            $product->web_category_id = $webCategory->id;
                            $product->rating = str_replace(',', '.', $dataProduct['rating']);
                            $product->count_reviews = (int)$dataProduct['reviews'];
                            $product->save();
                        }
                    }
                }
            }

            try {
                $ftp->sendTop36RequestFile();
            } catch (Exception $exception) {
                report($exception);
                ExceptionHandlerHelper::logFail($exception);
            }
        }
    }
}
