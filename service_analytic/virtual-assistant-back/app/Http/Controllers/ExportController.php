<?php

namespace App\Http\Controllers;

use App\Constants\Errors\ProductsErrors;
use App\Exports\NegativeKeywordsExport;
use App\Exports\ProductCharacteristicsExport;
use App\Exports\RootQueriesExport;
use App\Exports\SearchQueriesExport;
use App\Models\OzProduct;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    /**
     * @param Request $request
     */
    public function exportRootQueries(Request $request)
    {
        Excel::download(new RootQueriesExport, $this->getFileName('корневые запросы'));
    }

    public function exportSearchQueries(Request $request)
    {
        Excel::download(new SearchQueriesExport, $this->getFileName('поисковые запросы'));
    }

    public function exportNegativeKeywords(Request $request)
    {
        Excel::download(new NegativeKeywordsExport, $this->getFileName('минус-слова'));
    }

    /**
     * @param $exportType
     * @param Carbon $date
     * @return string
     */
    public function getFileName($exportType, Carbon $date = null)
    {
        $date = $date ?? Carbon::now();

        return "Выгрузка {$exportType} {$date->format('d.m.Y H:i')}.xls";
    }

    /**
     * Экспорт характеристик товара
     *
     * @param Request $request
     * @param int $id
     */
    public function exportProductCharacteristics(Request $request, int $id)
    {
        $request->validate([
            'type' => [
                'required',
                'string',
                'in:' . ProductCharacteristicsExport::TYPE_ALL . ',' . ProductCharacteristicsExport::TYPE_FILLED
            ]
        ]);
        $product = Product::findWithCurrentUser($id);

        if (!$product) {
            return response()->api_fail(
                'Товар не найден',
                [],
                404,
                ProductsErrors::NOT_FOUND
            );
        }

        return Excel::download(new ProductCharacteristicsExport($product, $request->type),
            $this->getFileName("характеристик товара $product->name"));
    }
}
