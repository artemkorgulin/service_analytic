<?php

namespace App\Http\Controllers\Api;

use App\Models\OzProduct;
use App\Services\UserService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class FileController extends Controller
{


    /**
     * Просто тестовый метод потом можно удалить
     * @return bool|void
     */
    public function test() {
        $userTestProductsCount = OzProduct::where(['user_id' => UserService::getUserId()])->count();
        if ($userTestProductsCount < 3) {
            return true;
        }
    }
}
