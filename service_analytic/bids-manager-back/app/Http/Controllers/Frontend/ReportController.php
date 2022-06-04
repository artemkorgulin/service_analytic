<?php

namespace App\Http\Controllers\Frontend;

use App\Exports\AnalyticsExport;
use App\Exports\CampaignExport;
use App\Exports\KeywordsExport;
use App\Exports\StrategyCpoExport;
use App\Exports\StrategyShowsExport;
use App\Http\Controllers\Controller;
use App\Models\StrategyType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    /**
     * Получить отчет по аналитике
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getAnalyticsReport(Request $request)
    {
        $request->merge([
            'all' => true,
            'needReformat' => false,
        ]);

        $res = (new AnalyticController)->getAnalyticsList($request)->getData();

        if( $res->success )
        {
            $analyticsData = (array) $res->data->campaigns;

            $ext = \Maatwebsite\Excel\Excel::XLSX;
            $filename = 'AnalyticsReport_'.$request->from.'_'.$request->to.
                        '_'.time().
                        '.'.strtolower($ext);
            $filepath = 'xls/'.date('Y-m-d').'/'.$filename;
            $res = Excel::store(new AnalyticsExport($analyticsData), $filepath, 'public', $ext);

            return response()->json(
                [
                    'success' => $res,
                    'data' => [
                        'xlsLink' => Storage::url($filepath),
                        'xlsName' => $filename,
                    ],
                    'errors' => [],
                ]
            );
        }

        return response()->json($res);
    }

    /**
     * Получить отчет по кампаниям
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getCampaignsStatisticReport(Request $request)
    {
        $request->merge([
            'all' => true,
            'needReformat' => false,
        ]);

        $res = (new AnalyticController)->getCampaignsList($request)->getData();

        if( $res->success )
        {
            $campaignsData = (array) $res->data->campaigns;

            $ext = \Maatwebsite\Excel\Excel::XLSX;
            $filename = 'CampaignsReport_'.$request->from.'_'.$request->to.
                        '_'.time().
                        '.'.strtolower($ext);
            $filepath = 'xls/'.date('Y-m-d').'/'.$filename;
            $res = Excel::store(new CampaignExport($campaignsData), $filepath, 'public', $ext);

            return response()->json(
                [
                    'success' => $res,
                    'data' => [
                        'xlsLink' => Storage::url($filepath),
                        'xlsName' => $filename,
                    ],
                    'errors' => [],
                ]
            );
        }

        return response()->json($res);
    }

    /**
     * Получить отчет по товарам
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getKeywordsStatisticReport(Request $request)
    {
        $request->merge([
            'all' => true,
            'needReformat' => false,
        ]);

        $res = (new AnalyticController)->getKeywordsList($request)->getData();

        if( $res->success )
        {
            $keywordsData = (array)$res->data->keywords;

            $ext = \Maatwebsite\Excel\Excel::XLSX;
            $filename = 'KeywordsReport_' . $request->from . '_' . $request->to .
                '_' . implode(',', $request->products ?? ['all']) .
                '_' . time() .
                '.' . strtolower($ext);
            $filepath = 'xls/' . date('Y-m-d') . '/' . $filename;
            $res = Excel::store(new KeywordsExport($keywordsData), $filepath, 'public', $ext);

            return response()->json(
                [
                    'success' => $res,
                    'data' => [
                        'xlsLink' => Storage::url($filepath),
                        'xlsName' => $filename,
                    ],
                    'errors' => [],
                ]
            );
        }

        return response()->json($res);
    }

    /***************************/
    /* Управление ставками 2.0 */
    /***************************/

    /**
     * Получить Excel файл с историей по стратегии РК
     * @param Request $request
     * @return JsonResponse
     */
    public function getStrategyCampaignHistoryXls(Request $request)
    {
        $request->merge([
            'all' => true,
            'needReformat' => false,
        ]);

        $res = (new StrategyController)->getStrategyCampaignHistory($request)->getData();

        if( $res->success )
        {
            $history = $res->data->history ?? [];

            $ext = \Maatwebsite\Excel\Excel::XLSX;
            $filename = 'StrategyReport_'.$request->strategyId.'_'.time().'.'.strtolower($ext);
            $filepath = 'xls/'.date('Y-m-d').'/'.$filename;

            switch( $res->data->strategyTypeId ) {
                case StrategyType::OPTIMAL_SHOWS:
                    $exportClass = StrategyShowsExport::class;
                    break;
                case StrategyType::OPTIMIZE_CPO:
                    $exportClass = StrategyCpoExport::class;
                    break;
            }

            if (isset($exportClass)) {
                $res = Excel::store(new $exportClass($history), $filepath, 'public', $ext);

                return response()->json(
                    [
                        'success' => $res,
                        'data' => [
                            'xlsLink' => Storage::url($filepath),
                            'xlsName' => $filename,
                        ],
                        'errors' => [],
                    ]
                );
            }
        }

        return response()->json($res);
    }
}
