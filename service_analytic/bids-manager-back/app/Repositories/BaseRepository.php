<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Class BaseRepository
 *
 * @package App\Repositories
 */
abstract class BaseRepository
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * BaseRepository constructor.
     */
    public function __construct()
    {
        $this->model = new ($this->getModelClass());
    }

    /**
     * @return mixed
     */
    abstract protected function getModelClass();

    /**
     * @return Model|\Illuminate\Foundation\Application|mixed
     */
    protected function startConditions()
    {
        return clone $this->model;
    }

    /**
     * @param $id
     * @return Model
     */
    public function getItem($id)
    {
        return $this->startConditions()->find($id);
    }

    /**
     * @return Collection
     */
    public function getList()
    {
        return $this->startConditions()
            ->select()
            ->get();
    }

    public static function getProductQuery(string $tableName)
    {
        return self::getVaQuery($tableName);
    }

    public static function getVaQuery(string $tableName)
    {
        return DB::connection('va')->table($tableName);
    }

    public static function getWabQuery(string $tableName)
    {
        return DB::connection('wab')->table($tableName);
    }
}
