<?php
namespace App\Http\Controllers\Api\v1\dist\brands;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;


/**
 *
 */
class MergeAllBrands
{

    /**
     * @var Request
     */
    private Request $request;

    /**
     * @param $request
     * @return $this
     */
    public function setRequest($request): MergeAllBrands
    {
       $this->request = $request;
       return $this;
    }

    /**
     * @return mixed
     */
    public function getRequest()
    {
        return $this->request;
    }


    /**
     * @return \Illuminate\Database\Query\Builder
     */
    private function getBrandsOz(): \Illuminate\Database\Query\Builder
    {
        if($this->request->search)
            return DB::connection('va')->table('oz_feature_options as oz')->select(['value as name'])->where('value', 'like', '%'.$this->request->search.'%');
        return DB::connection('va')->table('oz_feature_options as oz')->select(['value as name']);
    }

    /**
     * @return \Illuminate\Database\Query\Builder
     */
    private function getBrandsWb(): \Illuminate\Database\Query\Builder
   {
       if($this->request->search)
           return DB::connection('va')->table('wb_directory_items')->select(['title as name'])->where(['wb_directory_id' => 1])->where('title', 'like', '%'.$this->request->search.'%');
        return DB::connection('va')->table('wb_directory_items')->select(['title as name'])->where(['wb_directory_id' => 1]);
    }

    /**
     * @param $q1
     * @param $q2
     * @return mixed
     */
    private function mergeBrands($q1, $q2){
        $q2->union($q1);
        return $q2->limit(10)->get()->toArray();
    }

    /**
     * @return mixed
     */
    public function get(){
        return $this->mergeBrands($this->getBrandsOz(), $this->getBrandsWb());
    }
}
