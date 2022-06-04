<?php

namespace App\Http\Controllers\Api\v1\dist\blackList;

use App\Models\BrandBindRole;
use App\Models\BrandBlackList;
use App\Models\Permission;
use Database\Seeders\dist\black_list\iBindRole;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use \Exception;
use \stdClass;

class BlackListPermission
{

    /**
     * @var stdClass
     */
    private stdClass $request;



    /**
     * @var BrandBlackList|null
     */
    private ?BrandBlackList $blackList;

    /**
     * @param mixed $request
     * @throws Exception
     */
    public function setRequest($request): BlackListPermission
    {
        if(empty($request->obj))
            throw new Exception('Не получен id списка');

        $this->request = new stdClass();
        foreach ($request->obj as $key => $value)
        {
            $this->request->$key = $value;
        }
        return $this;
    }

    /**
     * @return stdClass
     */
    public function getRequest(): stdClass
    {
        return $this->request;
    }

    /**
     * @return BrandBlackList
     */
    public function getBlackList(): BrandBlackList
    {
        return $this->blackList;
    }


    private function changeList(){

        if($this->blackList = BrandBlackList::query()->where(['id' => $this->request->id])->first()){
            /** @var  BrandBlackList $this->blackList */
            $this->blackList->partner = $this->request->partner;
            $this->blackList->brand = $this->request->brand;
            $this->blackList->manager = $this->request->manager;
            $this->blackList->status = $this->request->status;
            $this->blackList->wb = $this->request->wb;
            $this->blackList->ozon = $this->request->ozon;
            $this->blackList->pattern = $this->request->pattern;
            $this->blackList->save();
        } else {
            $this->blackList = new BrandBlackList();
            $this->blackList->partner = $this->request->partner;
            $this->blackList->brand = $this->request->brand;
            $this->blackList->manager = $this->request->manager;
            $this->blackList->status = $this->request->status;
            $this->blackList->wb = $this->request->wb;
            $this->blackList->ozon = $this->request->ozon;
            $this->blackList->pattern = $this->request->pattern;
            $this->blackList->save();
        }

    }


    /**
     * @return Builder|Model|object
     * @throws Exception
     */
    private function getPermissionOzon(): int
    {
        $perm = Permission::query()->where(['name' => iBindRole::PERM_OZON])->first();
        if(empty($perm))
            throw new Exception('Ну установлены права для черного списка, выполните запуск php artisan db:seed BlackListBindRole');
        return $perm->id;
    }

    /**
     * @throws Exception
     */
    private function changePermissionOzon(){
        if($role = BrandBindRole::query()->where(['black_list_id' => $this->request->id, 'permission_id' =>  $this->getPermissionOzon()])->first()){
            if($this->request->ozon == 0){
                $role->delete();
            }
        } else {
            if($this->request->ozon == 1){
                $new_role = new BrandBindRole;
                $new_role->permission_id = $this->getPermissionOzon();
                $new_role->black_list_id = $this->request->id;
                $new_role->save();
            }
        }
    }


    /**
     * @return Builder|Model|object
     * @throws Exception
     */
    private function getPermissionWb(): int
    {
        $perm = Permission::query()->where(['name' => iBindRole::PERM_WB])->first();
        if(empty($perm))
            throw new Exception('Ну установлены права для черного списка, выполните запуск php artisan db:seed BlackListBindRole');
        /** @var  Permission $perm */
        return $perm->id;
    }

    /**
     * @throws Exception
     */
    private function changePermissionWb()
    {
        if ($role = BrandBindRole::query()->where(['black_list_id' => $this->request->id, 'permission_id' => $this->getPermissionWb()])->first()) {
            if ($this->request->wb == 0) {
                $role->delete();
            }
        } else {
            if ($this->request->wb == 1) {
                $new_role = new BrandBindRole;
                $new_role->permission_id = $this->getPermissionWb();
                $new_role->black_list_id = $this->request->id;
                $new_role->save();

            }
        }
    }

    /**
     * @throws Exception
     */
    public function change()
    {
        $this->changeList();
        $this->changePermissionOzon();
        $this->changePermissionWb();
    }
}
