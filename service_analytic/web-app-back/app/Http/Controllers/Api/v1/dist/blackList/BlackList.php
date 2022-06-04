<?php

namespace App\Http\Controllers\Api\v1\dist\blackList;

use App\Models\BrandBlackList;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Позиция из черного списка брендов
 */
class BlackList
{

    /**
     * @var
     */
    private $request;

    /**
     * @return mixed
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param $request
     * @return $this
     */
    public function setRequest($request): BlackList
    {
        $this->request = $request;
        return $this;
    }

    /**
     * @return Builder|Model|object|null
     */
    private function getBlackList()
    {
        return BrandBlackList::query()->where(['id' => $this->request->id])->first();
    }

    /**
     * @return Builder|Model|object|null
     */
    public function get(){
        return $this->getBlackList();
    }

}
