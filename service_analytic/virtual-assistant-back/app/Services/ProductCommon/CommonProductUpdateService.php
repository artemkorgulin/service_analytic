<?php

namespace App\Services\ProductCommon;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class CommonProductUpdateService
{
    /** Instance of Illuminate\Database\Eloquent\Model
     *
     * @var Object
     */
    private object $model;

    const CLASS_SUPPORTED_LIST = [
        '\App\Models\OzProduct',
        '\App\Models\WbProduct',
    ];

    /**
     * @param array $ids
     * @param bool $status
     * @return int
     * @throws \Exception
     */
    public function updateProductsBlocked(array $ids, bool $status)
    {
        if (! $this->model) {
            throw new \Exception('You must init model.');
        }

        $countUpdateItems = DB::table($this->model->getTable())
            ->whereIn('id', $ids)
            ->where('is_block', '!=', $status)
            ->update(['is_block' => $status]);

        return $countUpdateItems;
    }

    /**
     * @param  Object  $model
     * @return void
     * @throws \Exception
     */
    public function initModel(object $model): void
    {
        if (($model instanceof Model) == false) {
            throw new \Exception('Error object instance, need model instance.');
        }

        if (in_array($model::class, self::CLASS_SUPPORTED_LIST)) {
            throw new \Exception('Wrong model object, see CLASS_SUPPORTED_LIST constant.');
        }

        $this->model = $model;
    }
}
