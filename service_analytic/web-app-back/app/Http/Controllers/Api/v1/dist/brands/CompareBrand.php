<?php

namespace App\Http\Controllers\Api\v1\dist\brands;

use App\Models\BrandBlackList;
use Exception;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 *
 */
class CompareBrand
{
    /**
     * @var
     */
    private $request;
    /**
     * @var
     */
    private $search;

    /**
     * @return mixed
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return mixed
     */
    public function getSearch()
    {
        return $this->search;
    }

    /**
     * @param $request
     * @return $this
     * @throws Exception
     */
    public function setRequest($request): CompareBrand
    {

        if(!$request->id)
            throw new Exception('Не был передан бренд');

        $this->request = $request;
        return $this;
    }

    /**
     * @return $this
     */
    private function getBrand(): CompareBrand
    {
        $this->search =  BrandBlackList::query()->where(['id' => $this->request->id])->first()->brand;

        return $this;
    }

    /**
     * @return Builder
     */
    private function getBrandsOz(): Builder
    {
        if($this->search)
            return DB::connection('va')->table('oz_feature_options as oz')->select(['value as name'])->where('value', 'like', '%'.$this->search.'%');
       return DB::connection('va')->table('oz_feature_options as oz')->select(['value as name']);
    }

    /**
     * @return Builder
     */
    private function getBrandsWb(): Builder
    {
        if($this->search)
            return DB::connection('va')->table('wb_directory_items')->select(['title as name'])->where(['wb_directory_id' => 1])->where('title', 'like', '%'.$this->search.'%');
        return DB::connection('va')->table('wb_directory_items')->select(['title as name'])->where(['wb_directory_id' => 1]);
    }

    /**
     * @param $q1
     * @param $q2
     * @return mixed
     */
    private function mergeBrands($q1, $q2){
        $q2->union($q1);
        return  $q2->first();;
    }

    /**
     * @throws Exception
     */
    public function compare(){
        return $this->getBrand()->mergeBrands($this->getBrandsOz(), $this->getBrandsWb());
    }
}
