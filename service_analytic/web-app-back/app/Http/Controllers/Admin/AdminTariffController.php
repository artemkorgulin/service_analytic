<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OldTariff;

/**
 * Class TariffController
 * Контроллер для управления тарифами в панели администратора
 * @package App\Http\Controllers\Api\v1
 */
class AdminTariffController extends Controller
{
    /**
     * Список актуальных тарифов
     * @return mixed
     */
    public function index()
    {
        return response()->api_success(OldTariff::all()->toArray(), 200);
    }
}
