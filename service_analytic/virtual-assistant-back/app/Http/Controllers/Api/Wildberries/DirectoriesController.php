<?php

namespace App\Http\Controllers\Api\Wildberries;

use App\Helpers\Controller\CommonControllerHelper;
use App\Models\CollateWbCharacteristics;
use App\Models\RequiredCollectionWbCharacteristics;
use App\Models\WbCategory;
use App\Models\WbDirectory;
use App\Models\WbFeature;
use App\Services\Wildberries\WbSearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Class DirectoriesController
 * Хранит категории продуктов Wildberries
 */
class DirectoriesController extends CommonController
{

    protected function getParams(Request $request): CommonControllerHelper
    {
        $params = parent::getParams($request);
        if (app()->runningInConsole() === false) {
            foreach ($request->accounts as $account) {
                if ($account['platform_title'] === $params->myPlatformTitle && $account['pivot']['is_selected'] == 1) {
                    $params->account = $account;
                }
            }
        }

        return $params;
    }

    /**
     * Получение всех категорий продуктов Wildberries
     * Параметры:
     * search ищет категории продуктов Wildberries
     *
     */
    public function index(Request $request)
    {
        return response()->json(WbDirectory::select()->orderBy('title')->paginate()->setPath(''));
    }

    /**
     * Получение всех категорий продуктов Wildberries
     * Параметры:
     * search ищет категории продуктов Wildberries
     * @param Request $request
     * @param $slug
     * @return JsonResponse
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function show(Request $request, $slug): JsonResponse
    {
        if (!$slug) {
            abort(404);
        }
        $perPage = (int)$request->get('per_page') ?? 25;

        $directory = WbDirectory::firstWhere('slug', 'LIKE', '%' . $slug);
        if (!$directory) {
            abort(404);
        }

        $items = [];
        $search = $request->get('search');

        switch ($slug) {
            case 'tnved':
                if ($request->get('object')) {
                    $items = WbCategory::firstWhere('name', $request->get('object'))
                        ->directoryItems()
                        ->select('title AS id', 'title', DB::raw('0 as popularity'))
                        ->paginate($perPage)->setPath('');
                }

//                if ($search && strlen($search) > 3) {
//                    $items = $directory->items()
//                        ->where('wb_directory_items.title', 'LIKE', $search . '%')
//                        ->orderBy('wb_directory_items.title', 'ASC')
//                        ->paginate(999)->setPath('')->appends(['search' => $search]);
//                } else {
//                    $items = $directory->items()->orderBy('wb_directory_items.title', 'ASC')->paginate()->setPath('');
//                }
                break;

            case 'preload': // Получение предзагруженных полей

            case 'ext':
                $data = WbSearchService::searchCharacteristics(
                    $request->get('type') ?? '',
                    $request->get('category') ?? '',
                    $search ?? '',
                    $directory->id,
                    false,
                    $request->get('per_page') ?? '',
                );
                $items = $data['items'] ?? [];
                $useOnlyDictionaryValues = $data['useOnlyDictionaryValues'] ?? [];
                break;

            default:
                if ($search && strlen($search) > 0) {
                    $items = $directory->items()
                        ->where('wb_directory_items.title', 'LIKE', '%' . $search . '%')
                        ->orderBy('wb_directory_items.popularity', 'DESC')
                        ->orderBy('wb_directory_items.has_in_ozon', 'DESC')
                        ->orderBy('wb_directory_items.title', 'ASC')
                        ->paginate($perPage)->setPath('')->appends(['search' => $search]);
                } else {
                    $items = $directory->items()
                        ->orderBy('wb_directory_items.popularity', 'DESC')
                        ->orderBy('wb_directory_items.has_in_ozon', 'DESC')
                        ->orderBy('wb_directory_items.title', 'ASC')->paginate($perPage)->setPath('');
                }
        }
        if (!empty($useOnlyDictionaryValues)) {
            return response()->json(['items' => $items, 'useOnlyDictionaryValues' => $useOnlyDictionaryValues]);
        }
        return response()->json(['items' => $items]);
    }
}
